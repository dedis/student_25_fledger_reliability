#!/bin/bash

# This should be ran on the XDC. Run setup-xdc.sh at the root of the repo to run this from your machine.
mkdir -p ~/.local/bin
mkdir -p ~/.local/share

ln -s ~/experiments/xdc/exp ~/.local/bin
ln -s ~/experiments/xdc/node ~/.local/bin
ln -s ~/experiments/xdc/nload-profile ~/.local/share
ln -s ~/experiments/xdc/nonload-profile ~/.local/share
