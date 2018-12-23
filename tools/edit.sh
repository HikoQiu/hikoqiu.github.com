#!/bin/sh

date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_edit.log"
if [ -e $logfile ]; then
    rm $logfile
fi

ret=`$PHP $PATH_TOOL/php/md_path_by_index.php --index=$MD_INDEX`

# 判断是否找到对应的文章
flag=`echo "$ret" | grep -q "md" && echo 'true' || echo 'false'`
if [ "$flag" != "true" ]; then
    echo $ret
    exit -1
fi

# 编辑器打开md文件
echo $ret >> $logfile
cat $logfile

if [ ! -e $ret ];then
    echo "[-] Error: 文件不存在 $ret"
    exit -1
fi

$EDITOR $ret >> /tmp/null&
