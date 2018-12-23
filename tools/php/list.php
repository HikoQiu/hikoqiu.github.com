<?php
include_once 'include.inc.php';

try {
    $params = HiBlog::parseParams();
    $titles = HiBlog::ins()->listMds($params['num']);
    echo implode("\n", $titles);
} catch (Exception $ex) {
    exit('Fail: '.$ex->getMessage()."\n");
}

