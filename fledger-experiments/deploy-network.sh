#!/bin/bash

set -euo pipefail

function get_env {
  name="$1"
  env_file="./env"
  if test; then
    env_file="$2"
  fi
  value=$(grep -e "$name=" "$env_file" | sed -e "s/$name=//")
  echo "$value"
}

sphere_project=$(get_env SPHERE_PROJECT)
sphere_username=$(get_env SPHERE_USERNAME)
sphere_repo=$(get_env SPHERE_REPO)

network="$1"
path="./$1"
model_file="$path/model.py"

net=$(basename "$network")
realize_as="$net.$sphere_project.$sphere_username"
materialize_as="$realize_as"

currentnet=$(cat attached-net || echo "")

token=$(mrg whoami -t)
remote="https://$token:@git.sphere-testbed.net/$sphere_username/$sphere_project"

function check_arguments {
  if ! test -d "$sphere_repo"; then
    echo "FATAL: repo not found at [$sphere_repo]."
    echo "You must clone the experiments repo (ex https://git.sphere-testbed.net/abehsser/fledger)."
    exit 1
  fi

  if ! test -f "$model_file"; then
    echo "FATAL: Model file [$model_file] not found."
    exit 1
  fi

  if test -z "$token"; then
    echo "FATAL: Mergetb token not found, make sure you're logged in."
    exit 1
  else
    echo "Found token: $token"
  fi
}

function check_already_deployed {
  if test -n "$currentnet" || test "$currentnet" = "$net"; then
    echo "INFO: Network [$net] is already attached."
    echo "NOTE: If the network is not actually attached, please empty the file \`attached-net\`"
    echo "not deploying network..."
    exit 1
  fi
}

function compile_model {
  mrg compile "$model_file" -q || exit 1
}

function push_model {
  cp "$model_file" "$sphere_repo"

  (
    cd "$sphere_repo" || exit 1

    git add model.py >/dev/null
    git commit -m "commit from deploy tool" >/dev/null || true
    git push "$remote" >/dev/null || exit 1

    rev=$(git rev-parse HEAD)
    echo "$rev created."
  )

}

function check_realization_exists {
  echo "Checking if $realize_as exists..."
  res=$(mrg list realizations | grep "$realize_as")
  test -n "$res"
}

function prompt_should_relinquish {
  read -r -p "Relinquish (y/n)? " choice

  case "$choice" in
  y | Y)
    true
    ;;
  *)
    false
    ;;
  esac
}

function prompt_should_reattach {
  read -r -p "Reattach (y/n)? " choice

  case "$choice" in
  y | Y)
    true
    ;;
  *)
    false
    ;;
  esac
}

function relinquish {
  echo "RELINQUISHING $realize_as"
  mrg relinquish "$realize_as"
  sleep 2
}

function realize {
  echo "Realize model as $realize_as..."
  (
    cd "$sphere_repo" || exit 1
    revision=$(git rev-parse HEAD)
    mrg realize "$realize_as" revision "$revision" --disable-progress
  )
}

function materialize {
  echo "    Materialize model as $materialize_as..."
  mrg mat "$materialize_as" --sync --disable-progress || exit 1
}

function check_materialization {
  success=$(mrg show mat "$realize_as" -S | grep Success)

  if test -z "$success"; then
    mrg show mat "$materialize_as"
    echo "FATAL: materialization failed."
    exit 1
  else
    echo "SUCCESS: materialization created"
  fi
}

function xdc_detach {
  mrg xdc detach "$sphere_project.$sphere_username" || exit 1
}

function xdc_attach {
  mrg xdc attach "$sphere_project.$sphere_username" "$realize_as" >/dev/null || {
    echo "FATAL: could not attach realization"
    exit 1
  }
  echo "$net" >./attached-net
}

function main {
  check_already_deployed

  echo "Deploying network [$net]..."
  if check_realization_exists; then
    echo "Realization exists, we must either REATTACH or RELINQUISH it to continue."

    if prompt_should_reattach; then
      xdc_detach
      xdc_attach
      exit 0
    else
      if prompt_should_relinquish; then
        relinquish
      else
        echo "aborting..."
        exit 0
      fi
    fi
  fi

  echo "[MODEL]"
  compile_model
  push_model

  echo "[REALIZATION]"
  realize

  echo "[MATERIALIZATION]"
  materialize

  echo "[XDC ATTACH]"
  xdc_detach
  xdc_attach

  echo "Network deployed and attached."
}

main
