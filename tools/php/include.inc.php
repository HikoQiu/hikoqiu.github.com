<?php
// China time zone
ini_set('date.timezone','Asia/Shanghai');

include 'HiBlog.php';
include 'pinyin/Pinyin.php';
include 'markdown/Markdown.inc.php';
include 'markdown/HyperDown.php';

// parse PATH vars from conf
$conf = file_get_contents('./tools/conf');
$conf = explode("\n", $conf);
foreach($conf as $item) {
    $item = explode('=', trim($item));
    if(count($item) != 2) {
        continue;
    }
    
    // 将tools中的conf中的所有大写变量设置成全局变量
    define($item[0], trim($item[1], "'\""));
}
