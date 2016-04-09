<?php
include_once 'include.inc.php';

try{
    $params = HiBlog::parseParams();

    // 1.1 path of target file to parse
    if($params['all'] == 'yes') {
        // @todo support parsing all mds
        throw new Exception('Function to parse all md are not supported now.');
    }
    
    $file = '';
    if(!empty($params['file'])) {
        if(!file_exists($params['file'])) {
            throw new Exception('文件: '.$params['file'].' 不存在.');
        }
        
        $file = $params['file'];
    }else {
        if(!file_exists(PATH_LOG.'/'.LATEST_FILE_NAME)) {
            throw new Exception(PATH_LOG.'/'.LATEST_FILE_NAME.' not exist.');
        }
        
        $file = file_get_contents(PATH_LOG.'/'.LATEST_FILE_NAME);   
    }

    // 2.1 start to parse
    HiBlog::ins()->setTplFile(PATH_HTML_TPL.'/post_tpl.html');
    HiBlog::ins()->setPostHtmlDir(PATH_POST_HTML);
    $htmlPath = HiBlog::ins()->parsePostMd($file);
    echo "[".date('Y-m-d H:i:s')."]\n解析: {$file}\nHtml path: {$htmlPath}\n";
} catch (Exception $ex) {
    exit('[-] Error: '.$ex->getMessage()."\n");
}