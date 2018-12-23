<?php
include_once 'include.inc.php';

try {
    HiBlog::ins()->setTplFile(PATH_HTML_TPL.'/index_tpl.html');
    $files = HiBlog::ins()->index(PATH_POST_HTML);
    var_export($files);
} catch (Exception $ex) {
    exit('Fail: '.$ex->getMessage()."\n");  
}

