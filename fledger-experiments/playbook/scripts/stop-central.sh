#!/bin/bash

echo "[stopping services]"
systemctl stop flsignal
systemctl stop flrealm

echo "[removing old logs]"
rm -f /var/log/flsignal
rm -f /var/log/flrealm
