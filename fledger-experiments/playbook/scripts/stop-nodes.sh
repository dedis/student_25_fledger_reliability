#!/bin/bash

instances=$(ls ~/env.systemd)

if test -z "$instances"; then
  echo "WARNING: no instances!"
  exit
fi

echo "[wiping instances]"

for instance in $instances; do
  service="$instance.service"

  echo "$instance"

  echo "    ...stopping service"
  systemctl stop "$service"

  echo "    ...removing node"
  rm -rf "/root/$instance"

  echo "    ...removing logs"
  rm -f "/var/log/$instance"
done

echo "[removing environments]"
rm -rf ~/env.systemd

echo "[DONE]"
