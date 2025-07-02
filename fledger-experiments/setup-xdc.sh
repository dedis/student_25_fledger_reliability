#!/bin/bash

echo "[rsync files]"
make

ssh fledger bash -c "experiments/xdc/setup.sh"
