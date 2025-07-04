#!/bin/bash

### CENTRAL: flsignal
### N0-0: create pages
### N0-1: create realm
###
### ALL OTHER INSTANCES: fetch page

ENV_FOLDER="env.systemd"
FLAT="false"
if test -n "$1"; then
  ENV_FOLDER="$1"
  FLAT="true"
fi

mkdir -p "$ENV_FOLDER"

NODE_AMOUNT=$(grep -e "NODE_AMOUNT=" env | sed -e 's/NODE_AMOUNT=//')
INSTANCES_PER_NODE=$(grep -e "INSTANCES_PER_NODE=" env | sed -e 's/INSTANCES_PER_NODE=//')

LOOP_DELAY=1000
CENTRAL_HOST=10.0.128.128

MALICIOUS_PERCENT=20
MALICIOUS_AMOUNT=$((NODE_AMOUNT * INSTANCES_PER_NODE * MALICIOUS_PERCENT / 100))

# Either 48 pages of 10k chars, or 110 pages of 5k chars
FILLER_AMOUNT=105
TARGET_AMOUNT=5
PAGE_SIZE=5000 # size of both filler pages and target page

ENABLE_LOCAL_BLACKLISTS="--with-local-blacklists" # empty or "--with-local-blacklists"
#ENABLE_LOCAL_BLACKLISTS=""
ENABLE_SYNC="--enable-sync"   # empty or "--enable-sync"
BOOTWAIT_MAX=5000             # randomize instance start time so that they don't all start together
CONNECTION_DELAY=20000        # time before filler pages are created
PAGES_PROPAGATION_DELAY=10000 # time before target page is created
TIMEOUT_MS=300000             # timeout for each instance
PROPAGATION_TIMEOUT_MS=600000 # propagation timeout for each instance

REALM_SIZE=100000
REALM_FLO_SIZE=100000

EXPERIMENT_ID=$(grep -e "EXPERIMENT_ID=" env | sed -e 's/EXPERIMENT_ID=//')
echo "id@fledger.yohan.ch: [$EXPERIMENT_ID]"

for i in $(seq 0 $((NODE_AMOUNT - 1))); do
  node="n${i}"
  if test "$FLAT" = "false"; then
    mkdir -p "$ENV_FOLDER/$node"
  fi

  for j in $(seq 0 $((INSTANCES_PER_NODE - 1))); do
    instance="fledger-n$i-$j"
    if test "$i" -le 9; then
      instance="fledger-n0$i-$j"
    fi
    current_instance=$((i * INSTANCES_PER_NODE + j))
    cmd="--bootwait-max $BOOTWAIT_MAX simulation dht-fetch-pages --timeout-ms $TIMEOUT_MS --experiment-id $EXPERIMENT_ID $ENABLE_SYNC $ENABLE_LOCAL_BLACKLISTS --propagation-timeout-ms $PROPAGATION_TIMEOUT_MS"
    if test $current_instance -le $MALICIOUS_AMOUNT; then
      cmd="--evil-noforward $cmd"
    fi

    envfile="$ENV_FOLDER/$node/$instance"
    if test "$FLAT" = "true"; then
      envfile="$ENV_FOLDER/$instance"
    fi
    {
      echo "CENTRAL_HOST=$CENTRAL_HOST"
      echo "NODE_NAME=$instance"
      echo "NODE_CMD=$cmd"
      echo "RUST_BACKTRACE=full"
      echo "WAIT=true"
    } >"$envfile"
  done
done

create_page_cmd="--loop-delay $LOOP_DELAY simulation dht-create-pages --filler-amount $FILLER_AMOUNT --target-amount $TARGET_AMOUNT --page-size $PAGE_SIZE --connection-delay $CONNECTION_DELAY --pages-propagation-delay $PAGES_PROPAGATION_DELAY --experiment-id $EXPERIMENT_ID"
create_realm_cmd="realm create simulation $REALM_SIZE $REALM_FLO_SIZE --cond-pass"

if test "$MALICIOUS_PERCENT" = "100"; then
  create_page_cmd="--evil-noforward $create_page_cmd"
  create_realm_cmd="--evil-noforward $create_realm_cmd"
fi

# override fledger-n00-0
# it will create the tag
instance="fledger-n00-0"
envfile="$ENV_FOLDER/n0/$instance"
if test "$FLAT" = "true"; then
  envfile="$ENV_FOLDER/$instance"
fi
{
  echo "CENTRAL_HOST=$CENTRAL_HOST"
  echo "NODE_NAME=$instance"
  echo "NODE_CMD=$create_page_cmd"
  echo "WAIT=false"
} >"$envfile"

# override fledger-n00-1
# it will create the realm
instance="fledger-n00-1"
envfile="$ENV_FOLDER/n0/$instance"
if test "$FLAT" = "true"; then
  envfile="$ENV_FOLDER/$instance"
fi
{
  echo "CENTRAL_HOST=$CENTRAL_HOST"
  echo "NODE_NAME=$instance"
  # echo "NODE_CMD=realm create simulation 19000 5734" # 10 flos @ 1911 B => 19111 B /// 1 flo @ 1911 B (times 3 + 1) => 5734 B
  # flo sizes are kind of broken - experimentally 10 filler pages of size 512 B each go to around 17kB
  echo "NODE_CMD=$create_realm_cmd"
  echo "WAIT=false"
} >"$envfile"

{
  echo "FLSIGNAL=true"
  echo "FLREALM=false"
} >"$ENV_FOLDER/central"
