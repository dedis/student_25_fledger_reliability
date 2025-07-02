#!/bin/bash

mkdir -p env.systemd

amount=10
pernode=5

for i in $(seq 0 $((amount - 1))); do
  node="n${i}"
  mkdir -p "env.systemd/$node"

  centralhost="10.0.0.128"

  for j in $(seq 0 $((pernode - 1))); do
    instance="fledger-$node-$j"
    envfile="env.systemd/$node/$instance"
    {
      echo "CENTRAL_HOST=$centralhost"
      echo "NODE_NAME=$instance"
      echo "NODE_CMD=--bootwait-max 15000 simulation dht-join-realm"
      echo "WAIT=true"
    } >"$envfile"
  done
done

{
  echo "FLSIGNAL=true"
  echo "FLREALM=true"
} >"env.systemd/central"
