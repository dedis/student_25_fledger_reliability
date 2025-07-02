#!/bin/bash

instances=$(ls ~/env.systemd)

if test -z "$instances"; then
  echo "WARNING: no instances!"
  exit
fi

echo "[generating services]"

for instance in $instances; do
  echo "$instance"
  service="$instance.service"
  path="/etc/systemd/system/$service"

  envfile="$HOME/env.systemd/$instance"
  logfile="/var/log/$instance"
  nodecmd=$(grep -e "NODE_CMD=" "$envfile" | sed -e 's/NODE_CMD=//')

  sed -e "s%ENVFILE%$envfile%" "$HOME/fledger.service" |
    sed -e "s%NODECMD%$nodecmd%" |
    sed -e "s%LOGFILE%$logfile%" >"$path" || exit 1
done

echo "...done"

echo "[reloading daemon]"
systemctl daemon-reload || exit 1
echo "...done"

echo "[starting instances]"
for instance in $instances; do
  echo "$instance"

  systemctl restart "$instance.service" || exit 1
done

echo "...done"
