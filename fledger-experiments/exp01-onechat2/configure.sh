#!/bin/bash

message="$(openssl rand -hex 16)"

mkdir -p env.systemd/n0
mkdir -p env.systemd/n1

{
  echo "CENTRAL_HOST=10.0.128.128"
  echo "NODE_NAME=fledger-n0-0"
  echo "NODE_CMD=simulation chat --send-msg '$message'"
  echo "WAIT=false"
} >"env.systemd/n0/fledger-n0-0"

{
  echo "CENTRAL_HOST=10.0.128.128"
  echo "NODE_NAME=fledger-n1-0"
  echo "NODE_CMD=simulation --print-new-messages chat --recv-msg '$message'"
  echo "WAIT=true"
} >"env.systemd/n1/fledger-n1-0"

{
  echo "FLSIGNAL=true"
  echo "FLREALM=false"
} >"env.systemd/central"
