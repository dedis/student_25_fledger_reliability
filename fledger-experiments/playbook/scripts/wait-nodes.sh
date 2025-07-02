#!/bin/bash

DELAY=0.5

instances=$(grep ~/env.systemd -e '^WAIT=true$' -rl | xargs -n1 basename)

echo "Instances that we must wait for: $instances"

if test -z "$instances"; then
  echo "WARNING: no instances!"
  exit
fi

echo "[waiting for instances]"

for instance in $instances; do
  logfile="/var/log/$instance"

  echo "$instance"

  finished=""
  while test -z $finished; do
    if test -f "$logfile"; then
      # log file exists, check content
      if test -n "$(grep 'SIMULATION END' "$logfile")"; then
        finished=true
      fi
    fi

    sleep "$DELAY"
  done

  echo "    ...simulation over for this instance"
done

echo "...done"
