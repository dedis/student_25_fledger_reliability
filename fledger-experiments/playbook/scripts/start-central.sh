#!/bin/bash

echo "[installing services]"
cp ~/flsignal.service /etc/systemd/system/ || exit 1
cp ~/flrealm.service /etc/systemd/system/ || exit 1

echo "[starting services]"
systemctl daemon-reload

source "$HOME/central"

if test "$FLSIGNAL" = "true"; then
  echo "flsignal ==> starting"
  systemctl restart flsignal
else
  echo "flsignal ==> is disabled"
fi

if test "$FLREALM" = "true"; then
  echo "flrealm ==> starting"
  systemctl restart flrealm
else
  echo "flrealm ==> is disabled"
fi
