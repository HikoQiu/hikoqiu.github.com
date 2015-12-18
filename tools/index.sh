#!/bin/sh

# Path conf
. ./tools/conf

# generate new post
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_index.log"
$PHP $PATH_TOOL/php/index.php >> $logfile

echo "--index"
cat $logfile
