#!/bin/bash

DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LOG_DIR="$DIR/../logs"
#LOG_FILE="$LOG_DIR/error.log"
#mkdir $LOG_DIR
LOG_FILE="/dev/null"

ant $* 2>> $LOG_FILE

exit 0