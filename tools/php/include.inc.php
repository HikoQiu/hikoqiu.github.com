<?php
// China time zone
ini_set('date.timezone','Asia/Shanghai');

include 'HiBlog.php';
include 'pinyin/Pinyin.php';
include 'markdown/Markdown.inc.php';

// parse PATH vars from conf
$conf = file_get_contents('./tools/conf');
$conf = explode("\n", $conf);
foreach($conf as $item) {
    $item = explode('=', trim($item));
    if(count($item) != 2) {
        continue;
    }
    
    // define key => val according to "conf" file
    define($item[0], trim($item[1], "'\""));
}