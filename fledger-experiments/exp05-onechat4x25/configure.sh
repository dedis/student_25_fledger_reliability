#!/bin/bash

mkdir -p env.systemd

amount=100
messages=()

for i in $(seq $amount); do
  message="$(openssl rand -hex 16)"
  messages+=("$message")
done

for i in $(seq 0 $((amount - 1))); do
  j=$((("$i" + 2) % amount))
  nodename="n${i}"

  mkdir -p "env.systemd/$nodename"
  envfile="env.systemd/${nodename}/fledger-${nodename}-0"

  send_msg="${messages[$j]}"
  recv_msg="${messages[$i]}"

  centralhost="10.0.128.128"

  {
    echo "CENTRAL_HOST=$centralhost"
    echo "NODE_NAME=fledger-${nodename}-0"
    echo "NODE_CMD=--bootwait-max 15000 simulation --print-new-messages chat --recv-msg '$recv_msg' --send-msg '$send_msg'"
    echo "WAIT=true"
  } >"$envfile"
done

{
  echo "FLSIGNAL=true"
  echo "FLREALM=false"
} >"env.systemd/central"
