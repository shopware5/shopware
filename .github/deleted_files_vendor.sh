#!/usr/bin/env bash

function main() {
while getopts "ho:n:d:" OPTION
do
case $OPTION in
h)
usage
exit 1
;;
o)
original_dir=`realpath $OPTARG`/
;;
n)
new_dir=`realpath $OPTARG`/
;;
?)
usage
exit
;;
*)
usage
exit
;;
esac
done

if [[ -z "${original_dir:-}" ]]; then
echo "The original directory not set (-o)."
exit 1;
fi

if [[ -z "${new_dir:-}" ]]; then
echo "The new directory not set (-n)."
exit 1;
fi

if [ ! -d "$new_dir" ]; then
echo "The new directory $new_dir does not exist."
exit 1;
fi

if [ ! -d "$original_dir" ]; then
echo "The original directory ($original_dir) does not exist."
exit 1;
fi

diff_directories_find_deleted "${original_dir}" "${new_dir}"
}

function diff_directories_find_deleted() {
local originalDir=$1; shift
local newDir=$1; shift

local cutDir=$(dirname ${originalDir})

diff -rq "${originalDir}" "${newDir}" |
grep "^Only in ${originalDir}" |
sed -n 's/://p'  |
awk '{print $3"/"$4}' |
sed "s|${cutDir}||" |
sed "s|^/||" |
sed "s|//|/|"
}

function usage() {
cat << EOF
usage: $0 options

OPTIONS:
-h      Show this message
-o      Original directory
-n      New directory
EOF
}

main "$@"
