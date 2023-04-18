#!/bin/sh
set -e

# check if the first argument passed in looks like a flag or is a file
if [ "${1#-}" != "$1" ] || [ -f "$1" ]; then
  set -- desniff "$@"
fi

set -- tini -- "$@"

exec "$@"
