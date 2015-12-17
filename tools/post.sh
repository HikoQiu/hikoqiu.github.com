#!/bin/sh

# Path conf
. ./tools/conf

# Definitions
help=no

# Post's conf
TITLE=
SUB_TITLE=
LANG=zh
AUTHOR=Hiko

# assign value to vars
for option
do
    case "$option" in
        -*=*) value=`echo "$option" | sed -e 's/[-_a-zA-Z0-9]*=//'` ;;
           *) value="" ;;
    esac

    case "$option" in
        --help)                          help=yes                   ;;

        --title=*)                      TITLE="$value"            ;;
        --stitle=*)                     SUB_TITLE="$value"         ;;
        --author=*)                     AUTHOR="$value"         ;;
        --lang=*)                       LANG="$value"           ;;
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

    echo "  --title         post's title"
    echo "  --stitle        post's sub title"
    echo "  --author        post's author"
    echo "  --lang          post's language version"
    echo

    exit 1
fi

# generate new post
date=`date '+%Y%m%d'`
logfile=$PATH_LOG/$date"_debug.log"
$PHP $PATH_TOOL/php/post.php --title=$TITLE --stitle=$SUB_TITLE --author=$AUTHOR --lang=$LANG >> $logfile

echo "--debug"
cat $logfile

# vim open the file
newfile=$(cat $PATH_LOG/$LATEST_FILE_NAME)

# default, u can use vim editor
# vim $newfile

# here, I use Mou (a markdown editor) to write my post.
/Applications/Mou.app/Contents/MacOS/Mou $newfile >> /tmp/null &