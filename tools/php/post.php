<?php
include_once 'include.inc.php';

try {
    $data = HiBlog::parseParams();
    $name = Pinyin::getPinyin($data['title']);
    HiBlog::ins()->setLang($data['lang'] == 'zh' ? 'zh' : 'en');
    HiBlog::ins()->setPath(PATH_POST_MD."/{$name}.md");
    HiBlog::ins()->setTplFile(PATH_MD_TPL.'/post_tpl.md');
    HiBlog::ins()->genPost($data['title'], $data['category'], $data['stitle'], $data['author']);
    
    file_put_contents(PATH_LOG.'/latest.file.log', HiBlog::ins()->getPath());
    echo "[".date('Y-m-d H:i:s')."]\nPost: {$data['title']} / {$data['stitle']} / {$data['author']}\nPath: ".HiBlog::ins()->getPath()."\n";
} catch (Exception $ex) {
    exit($ex->getMessage()."\n");
}