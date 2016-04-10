<?php
include_once 'include.inc.php';

try {
    $params = HiBlog::parseParams();
    $index = trim($params['index'], '#');
    if($index == '' || $index < 0) {
        throw new Exception('[-] Error: 文章编号必须大于0!');
    }
    $path = HiBlog::ins()->getMdPathByIndex($index);
    echo $path;
} catch (Exception $ex) {
    exit('Fail: '.$ex->getMessage()."\n");
}

