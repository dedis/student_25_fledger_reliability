#!/bin/bash

mkdir -p env.systemd

messages=()

for i in $(seq 10); do
  message="$(openssl rand -hex 16)"
  messages+=("$message")
done

for i in $(seq 0 9); do
  j=$((("$i" + 2) % 10))
  nodename="n${i}"

  mkdir -p "env.systemd/$nodename"
  envfile="env.systemd/${nodename}/fledger-${nodename}-0"

  send_msg="${messages[$j]}"
  recv_msg="${messages[$i]}"

  {
    echo "CENTRAL_HOST=10.0.128.128"
    echo "NODE_NAME=fledger-${nodename}-0"
    echo "NODE_CMD=simulation --print-new-messages chat --recv-msg '$recv_msg' --send-msg '$send_msg'"
    echo "WAIT=true"
  } >"$envfile"
done

{
  echo "FLSIGNAL=true"
  echo "FLREALM=false"
} >"env.systemd/central"
