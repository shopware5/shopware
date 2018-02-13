#! /bin/sh

set -eu

script_dir="$(cd -- "$(dirname "$0")" && pwd)"
root_dir="$script_dir"/..

set -x

rm -rf -- "$root_dir"/vendor/mpdf/mpdf/ttfonts
