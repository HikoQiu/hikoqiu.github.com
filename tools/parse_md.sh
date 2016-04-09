#!/bin/sh

# Path conf
#. ./tools/conf

# Definitions
help=no
index=yes

# mardown file to parse
MD_FILENAME=
MD_ALL=no

for option
do
    case "$option" in
        -*=*) value=`echo "$option" | sed -e 's/[-_a-zA-Z0-9]*=//'` ;;
           *) value="" ;;
    esac

    case "$option" in
        --help)                        help=yes                   ;;

        --all)                       MD_ALL=yes                   ;;
        --file=*)                      MD_FILENAME="$value"         ;;
        --index=*)                     index="$value"         ;;
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

    echo "  --file          path to markdown file, if empty, parse the file in logs/ dir."
    echo "  --all           parse all md files"

    echo "  --index         yes/no - auto index index.html"
    
    echo

    exit 1
fi

# parse md file
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_debug.log"
rm $logfile
$PHP $PATH_TOOL/php/parse_md.php --file=$MD_FILENAME --all=$MD_ALL >> $logfile

cat $logfile

if [ $index = yes ]; then
    . $PATH_TOOL/index.sh
    exit 1
fi

