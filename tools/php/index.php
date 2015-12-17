<?php
include_once 'include.inc.php';

try {
    $path = PATH_POST_HTML;
    HiBlog::ins()->setTplFile(PATH_HTML_TPL.'/index_tpl.html');
    $files = HiBlog::ins()->index($path);
    var_export($files);
} catch (Exception $ex) {
    exit('Fail: '.$ex->getMessage()."\n");  
}

