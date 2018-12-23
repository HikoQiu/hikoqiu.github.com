<?php
include_once 'include.inc.php';

try{
    $params = HiBlog::parseParams();

    // 1.1 path of target file to parse
    $files = [];
    if($params['all'] == 'yes') {
        $files = HiBlog::ins()->dirFiles(PATH_POST_MD);
    }elseif(!empty($params['file'])) {
        if(!file_exists($params['file'])) {
            throw new Exception('[-] Error: 文件: '.$params['file'].' 不存在.');
        }

        $files = [$params['file']];
    }else {
        if(!file_exists(PATH_LOG.'/'.LATEST_FILE_NAME)) {
            throw new Exception(PATH_LOG.'/'.LATEST_FILE_NAME.' not exist.');
        }

        $files = [file_get_contents(PATH_LOG.'/'.LATEST_FILE_NAME)];
    }

    HiBlog::ins()->setPostHtmlDir(PATH_POST_HTML);
    foreach($files as $f) {
        $htmlPath = HiBlog::ins()->parsePostMd($f);
        echo "[".date('Y-m-d H:i:s')."]\n[+] 解析: {$f}\nHtml path: {$htmlPath}\n";
    }
} catch (Exception $ex) {
    exit('[-] Error: '.$ex->getMessage()."\n");
}