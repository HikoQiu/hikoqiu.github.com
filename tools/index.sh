#!/bin/sh

# Path conf
. ./tools/conf

# Definitions
help=no

# assign value to vars
for option
do
    case "$option" in
        -*=*) value=`echo "$option" | sed -e 's/[-_a-zA-Z0-9]*=//'` ;;
           *) value="" ;;
    esac

    case "$option" in
        --help)                          help=yes                   ;;        
        *)
            echo "$0: error: invalid option \"$option\""
            exit 1
        ;;
    esac

done

# show help info.
if [ $help = yes ]; then
    echo
    echo "  --help          Usage"
    echo

    echo "  index all posts to index.hmtl"
    
    echo

    exit 1
fi

# generate new post
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_index.log"
$PHP $PATH_TOOL/php/index.php >> $logfile

echo "--index"
cat $logfile
