use core::str;
use flarch::{
    data_storage::DataStorage,
    tasks::time::{timeout, Duration},
};
use thiserror::Error;
use tokio::sync::{mpsc::channel, watch};

use crate::router::{
    broker::BrokerRouter,
    messages::{NetworkWrapper, RouterIn, RouterOut},
};
use flarch::{
    broker::{Broker, BrokerError},
    nodeids::{NodeID, U256},
};

use super::{
    core::{Counters, WebProxyConfig, WebProxyStorage},
    messages::{Messages, WebProxyIn, WebProxyOut},
    response::Response,
};

pub(super) const MODULE_NAME: &str = "WebProxy";

#[derive(Debug, Error)]
pub enum WebProxyError {
    #[error("Didn't get answer from proxy node")]
    TimeoutProxyNode,
    #[error("BrokerError({0})")]
    BrokerError(#[from] BrokerError),
    #[error("No nodes available for proxying")]
    NoNodes,
    #[error("Timeout while waiting for response")]
    ResponseTimeout,
}

#[derive(Clone, Debug)]
pub struct WebProxy {
    /// Represents the underlying broker.
    pub web_proxy: Broker<WebProxyIn, WebProxyOut>,
    storage: watch::Receiver<WebProxyStorage>,
}

impl WebProxy {
    pub async fn start(
        ds: Box<dyn DataStorage + Send>,
        our_id: NodeID,
        router: BrokerRouter,
        config: WebProxyConfig,
    ) -> anyhow::Result<Self> {
        let mut web_proxy = Broker::new();
        let (messages, storage) = Messages::new(ds, config, our_id, web_proxy.clone());

        web_proxy.add_handler(Box::new(messages)).await?;
        web_proxy
            .add_translator_link(
                router,
                Box::new(Self::link_proxy_router),
                Box::new(Self::link_router_proxy),
            )
            .await?;

        Ok(Self { web_proxy, storage })
    }

    /// Sends a GET request to one of the remote proxies with the given URL.
    /// If the remote proxy doesn't answer within 5 seconds, a timeout error is
    /// returned.
    /// TODO: add GET headers and body, move timeout to configuration
    pub async fn get(&mut self, url: &str) -> anyhow::Result<Response> {
        log::debug!("Getting {url}");
        let our_rnd = U256::rnd();
        let (tx, rx) = channel(128);
        self.web_proxy
            .emit_msg_in(WebProxyIn::RequestGet(our_rnd, url.to_string(), tx))?;
        let (mut tap, id) = self.web_proxy.get_tap_out().await?;
        Ok(timeout(Duration::from_secs(5), async move {
            while let Some(msg) = tap.recv().await {
                if let WebProxyOut::ResponseGet(proxy, rnd, header) = msg {
                    if rnd == our_rnd {
                        self.web_proxy.remove_subsystem(id).await?;
                        return Ok(Response::new(proxy, header, rx));
                    }
                }
            }
            // TODO: is this really called sometime?
            self.web_proxy.remove_subsystem(id).await?;
            return Err(anyhow::anyhow!(WebProxyError::ResponseTimeout));
        })
        .await
        .map_err(|_| WebProxyError::ResponseTimeout)??)
    }

    pub fn get_counters(&mut self) -> Counters {
        self.storage.borrow().counters.clone()
    }

    fn link_router_proxy(msg: RouterOut) -> Option<WebProxyIn> {
        match msg {
            RouterOut::NodeInfosConnected(list) => Some(WebProxyIn::NodeInfoConnected(list)),
            RouterOut::NetworkWrapperFromNetwork(id, msg) => msg
                .unwrap_yaml(MODULE_NAME)
                .map(|msg| WebProxyIn::FromNetwork(id, msg)),
            _ => None,
        }
    }

    fn link_proxy_router(msg: WebProxyOut) -> Option<RouterIn> {
        if let WebProxyOut::ToNetwork(id, msg_node) = msg {
            Some(RouterIn::NetworkWrapperToNetwork(
                id,
                NetworkWrapper::wrap_yaml(MODULE_NAME, &msg_node).unwrap(),
            ))
        } else {
            None
        }
    }
}

#[cfg(test)]
mod tests {
    use flarch::{
        data_storage::DataStorageTemp,
        start_logging_filter_level,
        tasks::{spawn_local, wait_ms},
    };

    use crate::nodeconfig::NodeConfig;

    use super::*;

    #[tokio::test]
    async fn test_get() -> anyhow::Result<()> {
        start_logging_filter_level(vec![], log::LevelFilter::Info);
        let cl_ds = Box::new(DataStorageTemp::new());
        let cl_in = NodeConfig::new().info;
        let cl_id = cl_in.get_id();
        let mut cl_rnd = Broker::new();

        let wp_ds = Box::new(DataStorageTemp::new());
        let wp_in = NodeConfig::new().info;
        let wp_id = wp_in.get_id();
        let mut wp_rnd = Broker::new();

        let mut cl =
            WebProxy::start(cl_ds, cl_id, cl_rnd.clone(), WebProxyConfig::default()).await?;
        let (mut cl_tap, _) = cl_rnd.get_tap_in().await?;
        let _wp = WebProxy::start(wp_ds, wp_id, wp_rnd.clone(), WebProxyConfig::default()).await?;
        let (mut wp_tap, _) = wp_rnd.get_tap_in().await?;

        let list = vec![cl_in, wp_in];
        cl_rnd.emit_msg_out(RouterOut::NodeInfosConnected(list.clone()))?;
        wp_rnd.emit_msg_out(RouterOut::NodeInfosConnected(list))?;

        let (tx, mut rx) = channel(1);
        spawn_local(async move {
            let mut resp = cl.get("https://fledg.re").await.expect("fetching fledg.re");
            log::debug!("Got response struct with headers: {resp:?}");
            let content = resp.text().await.expect("getting text");
            log::debug!("Got text from content: {content:?}");
            tx.send(1).await.expect("sending done");
        });

        loop {
            if let Ok(_) = rx.try_recv() {
                log::debug!("Done");
                return Ok(());
            }

            if let Ok(RouterIn::NetworkWrapperToNetwork(dst, msg)) = cl_tap.try_recv() {
                log::debug!("Sending to WP: {msg:?}");
                wp_rnd
                    .emit_msg_out(RouterOut::NetworkWrapperFromNetwork(dst, msg))
                    .expect("sending to wp");
            }

            if let Ok(RouterIn::NetworkWrapperToNetwork(dst, msg)) = wp_tap.try_recv() {
                log::debug!("Sending to CL: {msg:?}");
                cl_rnd
                    .emit_msg_out(RouterOut::NetworkWrapperFromNetwork(dst, msg))
                    .expect("sending to wp");
            }

            log::debug!("Waiting");
            wait_ms(100).await;
        }
    }
}
