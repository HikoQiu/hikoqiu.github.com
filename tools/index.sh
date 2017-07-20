#!/bin/sh

# 遍历posts目录下所有的html文件

# generate new post
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_index.log"
if [ -e $logfile ]; then
    rm $logfile
fi

$PHP $PATH_TOOL/php/index.php >> $logfile

#cat $logfile
echo "[+] 索引完成!"