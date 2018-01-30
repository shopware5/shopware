#! /bin/sh

set -eu

script_dir="$(cd -- "$(dirname "$0")" && pwd)"
# Execute the same actions as post-install-cmd:
exec "$script_dir"/composer-post-install-cmd.sh
