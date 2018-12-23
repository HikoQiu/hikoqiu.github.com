#!/bin/sh

# 遍历posts目录下所有的html文件

# Path conf
#. ./tools/conf

# generate new post
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_list.log"
if [ -e $logfile ]; then
    rm $logfile
fi
$PHP $PATH_TOOL/php/list.php --num=$MD_NUM >> $logfile

cat $logfile
