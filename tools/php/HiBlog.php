<?php
/**
 * Description of HiBlog
 *
 * @author HikoQiu
 */
class HiBlog {
    
    private static $_ins;
    private $_path;
    private $_tplFile;
    private $_postHtmlDir;
    private $_lang;
    
    /**
     * @return HiBlog
     */
    public static function ins() {
        if(!static::$_ins) {
            static::$_ins = new self;
        }
        
        return self::$_ins;
    }
    
    public function getLang() {
        return $this->_lang;
    }
    
    public function setLang($lang) {
        $this->_lang = $lang;
    }
    
    public function getPath() {
        return $this->_path;
    }
    
    public function setPath($path) {
        // mkdirs according to date time.
        $arrInfo = pathinfo($path);
        $this->_path = $arrInfo['dirname'].'/'.$this->getLang().'/'.date('Y').'/'.date('m').'/'.$arrInfo['basename'];
        
        $arrInfo = pathinfo($this->_path);
        if(!empty($arrInfo['dirname'])) {
            system("mkdir -p {$arrInfo['dirname']}");
            
            if(!is_dir($arrInfo['dirname'])) {
                throw new Exception("Mkdir -p {$arrInfo['dirname']} ... fail");
            }
        }
    }
    
    public function setTplFile($filename) {
        $this->_tplFile = $filename;
    }
    
    public function getTplFile() {
        return $this->_tplFile;
    }
    
    public function setPostHtmlDir($dir) {
        $this->_postHtmlDir = $dir;
    }
    
    public function getPostHtmlDir() {
        return $this->_postHtmlDir;
    }
    
    /**
     * Parse console parameter
     * @param string $param
     * @return array
     * @throws Exception
     */
    public static function parseParam($param) {
        $arrParam = explode('=', $param);
        if(count($arrParam) < 2) {
            throw new Exception('Parsing param failed', 1000);
        }
        
        return [
            'key' => ltrim($arrParam[0], '--'),
            'val' => $arrParam[1]
        ];
    }
    
    /**
     * Parse all console params
     * @return array
     * @throws Exception
     */
    public static function parseParams() {
        // validate params
        if(empty($_SERVER['argv']) || count($_SERVER['argv']) <= 1) {
            throw new Exception('Lack of params', 1001);
        }
        
        // parse parameters
        $data = [];
        foreach($_SERVER['argv'] as $i => $param) {
            if($i == 0) {
                continue;
            }

            $kv = array_merge(self::parseParam($param));
            $data[$kv['key']] = $kv['val'];
        }
        
        return $data;
    }

    /**
     * Generate new post.md
     * @param $title
     * @param $category
     * @param $stitle
     * @param $author
     * @return bool|int
     * @throws Exception
     */
    public function genPost($title, $category, $stitle, $author) {
        if(!$this->getPath()) {
            throw new Exception('Empty md path', 1002);
        }
        
        // post already exit
        if(file_exists($this->getPath())) {
            // @TODO 抛异常
            return true;
        }
        
        // copy tpl as basic content
        $content = $this->getDealtTplCnt();
        $content = $this->_replaceBasicInfo($content,
            str_replace('.', ' ', $title),
            $category,
            str_replace(',', ' ', $stitle),
            $author);
        return file_put_contents($this->getPath(), $content);
    }

    public function listMds($num) {
        $timeMapTitles = $this->getTimeDESCMdInfoList();
        $list = [];
        $counter = 0;
        foreach($timeMapTitles as $time => $info) {
            $list[] = '#'.$counter.'  '.$info['title'];
            if(++$counter >= $num) {
                break;
            }
        }

        return $list;
    }

    public function getMdPathByIndex($index) {
        $timeMapTitles = $this->getTimeDESCMdInfoList();
        if($index > count($timeMapTitles) - 1) {
            throw new Exception("[-] Error: 文章编号无效 {$index}");
        }
        $list = array_values($timeMapTitles);
        return $list[$index]['filename'];
    }

    public function getTimeDESCMdInfoList() {
        $files = $this->dirFiles(PATH_POST_MD);
        $timeMapTitles = [];
        foreach($files as $index => $file) {
            // 构造markdown的语法
            $params = $this->parseMdToParams($file);
            $timeMapTitles[$params['ctime']] = $params;
        }
        krsort($timeMapTitles);
        return $timeMapTitles;
    }
    
    /**
     * Replace tpl's basic info
     * @param string $content
     * @param string $title
     * @param string $category
     * @param string $stitle
     * @param string $author
     * @return string
     */
    private function _replaceBasicInfo($content, $title, $category, $stitle, $author) {
        $params = [
            '{title}' => $title,
            '{category}' => $category,
            '{stitle}' => $stitle,
            '{author}' => $author,
            '{ctime}' => date('Y-m-d H:i:s'),
            '{lang}' => $this->getLang()
        ];
        foreach($params as $key => $val) {
            $content = str_replace($key, $val, $content);
        }
        
        return $content;
    }

    /**
     * Parse post's md file into html file
     * @param $filename
     * @return string
     * @throws Exception
     */
    public function parsePostMd($filename) {
        $params = $this->parseMdToParams($filename);
        $body = Markdown::defaultTransform($params['body']);
        
        // 2.1 set to html tpl
        $repData = [
            '{$title}' => $params['title'],
            '{$subtitle}' => $params['subtitle'],
            '{$author}' => $params['author'],
            '{$ctime}' => $params['ctime'],
            '{$tags}' => $params['tags'],
            '{$category}' => $params['category'],
            '{$lang}' => $params['lang'],
            '{$body}' => $body
        ];

        // 根据文章类型设置模板
        if($params['lang'] == 'en') {
            $this->setTplFile(PATH_HTML_TPL.'/post_tpl_en.html');
        }else {
            $this->setTplFile(PATH_HTML_TPL.'/post_tpl_zh.html');
        }
        $tpl = $this->getDealtTplCnt();
        $tpl = str_replace(array_keys($repData), array_values($repData), $tpl);
        
        $ctime = $params['ctime'];
        $year = date('Y', strtotime($ctime));
        $month = date('m', strtotime($ctime));
        $lang = $params['lang'];
        $cat = $params['category'];
        
        $baseDir = $this->getPostHtmlDir();
        $path = $baseDir.'/'.$lang.'/'.$year.'/'.$month;
        system("mkdir -p {$path}");
        if(!is_dir($path)) {
            throw new Exception("Mkdir -p {$path} ... fail");
        }
        
        $filenameInfo = pathinfo($filename);
        $file = $path.'/'.$filenameInfo['filename']."_{$cat}".'.html';
        file_put_contents($file, $tpl);
        return $file;
    }

    private function parseMdToParams($filename) {
        $content = file_get_contents($filename);

        // 1.1 header & body of the post
        $data = explode('---', $content);
        if(count($data) <= 2) {
            throw new Exception('[-] Error: 无法解析文章: '.$filename);
        }

        $header = explode("\n", $data[1]);
        $params = [];
        foreach($header as $item) {
            $item = trim($item, ' 　');
            if(empty($item)) {
                continue;
            }
            $arr = explode(':', $item);
            $params[$arr[0]] = trim(implode(':', array_slice($arr, 1)));
        }

        $body = array_slice($data, 2);
        $body = implode('---', $body);
        $params['body'] = $body;
        $params['filename'] = $filename;
        return $params;
    }

    /**
     * create index to index.html
     * @param $path
     * @return array
     * @throws Exception
     */
    public function index($path) {
        if(!is_dir($path)) {
            throw new Exception("[-] Error: 路径 '{$path}' 不是文件夹!");
        }
        
        // through the dir to list all html files
        $files = $this->dirFiles($path);
        if(empty($files)) {
            throw new Exception("[-] Error: 暂无文章!");
        }
        
        $timeMapTitles = [];
        $timesMapMeta = [];
        foreach($files as $index => $file) {
            // 构造markdown的语法
            $meta = $this->getMeta($file);
            $file = ltrim($file, './');
            $datetime = date('Y/m/d', strtotime($meta['ctime']));
            $title = "{$datetime} &raquo; [{$meta['title']}]({$file})  \r\n";
            $timeMapTitles[$meta['ctime']] = $title;
            $timesMapMeta[$meta['ctime']] = $meta;
        }

        return $this->genIndexes($timeMapTitles, $timesMapMeta);
    }

    private function genIndexes(&$timeMapTitles, &$timeMapMeta) {
        krsort($timeMapTitles);

        $categories = explode(' ', CATEGORIES);
        $langs = explode(' ', LANGS);

        // 索引页顶部标题
        $headerLabels = [];
        $consts = get_defined_constants();
        foreach($categories as $i => $c) {
            foreach($langs as $l) {
                $constKey = strtoupper('CATEGORIES_LABELS_'.$l);
                $arrCatLabels = explode(' ', $consts[$constKey]);
                $headerLabels[$c.'_'.$l] = str_replace('.', ' ', $arrCatLabels[$i]);
            }
        }

        // 需要生成索引的文件
        $datas = [
            'all' => [
                'filename' => 'index.html',
                'tpl' => 'index_tpl.html',
                'name' => BLOG_NAME,
                'slogan' => BLOG_SLOGAN,
                'cnt' => '',
            ],
        ];
        foreach($langs as $l) {
            $name = $consts[strtoupper('BLOG_NAME_'.$l)];
            $slogan = $consts[strtoupper('BLOG_SLOGAN_'.$l)];
            $datas[$l] = [ // e.g: en
                'filename' => "index_{$l}.html",
                'tpl' => "index_tpl_{$l}.html",
                'name' => $name,
                'slogan' => $slogan,
                'cnt' => '',
            ];
        }
        foreach($categories as $c) {
            foreach($langs as $l) {
                $name = $headerLabels["{$c}_{$l}"];
                $slogan = strtoupper($c);
                $datas["{$c}_{$l}"] = [ // e.g: tech_en
                    'filename' => "{$c}_{$l}.html",
                    'tpl' => "index_tpl_{$l}.html",
                    'name' => $name,
                    'slogan' => $slogan,
                    'cnt' => '',
                ];
            }
        }

        // 生成索引文件
        $arrLastMonth = array_flip(array_keys($datas));
        foreach($timeMapTitles as $t => $title) {
            $month = date('Y/m', strtotime($t));

            // all
            if($arrLastMonth['all'] != $month) {
                $datas['all']['cnt'] .= "### {$month}  \r\n";
                $arrLastMonth['all'] = $month;
            }
            $datas['all']['cnt'] .= $title;

            // en_all & zh_all
            $meta = $timeMapMeta[$t];
            $lang =$meta['lang'];
            if(!in_array($lang, $langs)) {
                throw new Exception("[-] Error: 语言错误 {$lang}: $title");
            }
            if($arrLastMonth[$lang] != $month) {
                $datas[$lang]['cnt'] .= "### {$month}  \r\n";
                $arrLastMonth[$lang] = $month;
            }
            $datas[$lang]['cnt'] .= $title;

            // category_lang
            $categoryLang = $meta['category'].'_'.$lang;
            if($arrLastMonth[$categoryLang] != $month) {
                $datas[$categoryLang]['cnt'] .= "### {$month}  \r\n";
                $arrLastMonth[$categoryLang] = $month;
            }
            $datas[$categoryLang]['cnt'] .= $title;
        }

        foreach($datas as $item) {
            $filename = $item['filename'];
            $cnt = Markdown::defaultTransform($item['cnt']);
            $this->setTplFile(PATH_HTML_TPL.'/'.$item['tpl']);
            $tpl = $this->getDealtTplCnt();

            $content = str_replace(
                ['{$content}', '{$blogName}', '{$blogSlogan}'],
                [$cnt, $item['name'], $item['slogan']],
                $tpl);


            file_put_contents(rtrim(PATH_ROOT, '/')."/{$filename}", $content);
        }
        return $timeMapTitles;
    }

    /**
     * 处理导入公共文件
     * @param $tpl
     * @return mixed
     */
    private function dealInclude($tpl) {
        preg_match_all('/\{@include\(\"(.*)\"\)\}/', $tpl, $data);
        if(count($data) >= 2) {
            foreach($data[1] as $i => $includeFile) {
                $incCnt = file_get_contents(rtrim(PATH_HTML_TPL).'/'.$includeFile);

                // 处理导航
                if(stripos($includeFile, 'common_nav') !== false) {
                    $incCnt = $this->dealCommonNav($includeFile, $incCnt);
                }
                $tpl = str_replace($data[0][$i], $incCnt, $tpl);
            }
        }
        return $tpl;
    }

    /**
     * 处理导航栏
     * @param $navFile
     * @param $cnt
     * @return mixed
     * @throws Exception
     */
    private function dealCommonNav($navFile, $cnt) {
        if(stripos($navFile, '_en') !== false) { // 英文
            $navConf = HEADER_NAV_EN;
        }elseif(stripos($navFile, '_zh') !== false){
            $navConf = HEADER_NAV_ZH;
        }else {
            $navConf = HEADER_NAV_ALL;
        }

        $arrNav = explode(',', $navConf);
        $navStr = '';
        foreach($arrNav as $item) {
            $arr = explode(':', $item);
            if(count($arr) != 2) {
                throw new Exception("[-]Error: 导航配置参数错误: ".$navConf);
            }

            $navStr .= "<a href='{$arr[1]}'>$arr[0]</a>";
        }

        return str_replace('{$navList}', $navStr, $cnt);
    }

    private function getDealtTplCnt() {
        $tpl = file_get_contents($this->getTplFile());
        $tpl = $this->dealInclude($tpl);
        return $tpl;
    }
    
    /**
     * get meta data of the (post Html)file
     * @param string $file
     * @return array
     */
    private function getMeta($file) {
        $fcon =  file_get_contents($file);
        $ts = strpos($fcon, '<!--HiBlog-') + 11;
        $te = strpos($fcon, '-HiBlog-->');
        $meta = substr($fcon, $ts, $te - $ts);
        $meta = explode('`', $meta);
        return [
            'title' => $meta[0],
            'author' => $meta[1],
            'ctime' => $meta[2],
            'tags' => $meta[3],
            'category' => $meta[4],
            'lang' => $meta[5],
        ];
    }

    /**
     * Get all files of the path
     * @param string $path
     * @return array
     */
    public function dirFiles($path) {
        $files = [];
        
        $dir = dir($path); 
        while($file = $dir->read()) { 
            if($file == "." || $file == "..") {
                continue;
            }
            
            if(is_dir("{$path}/$file")) {
                $files = array_merge($files, $this->dirFiles("$path/$file")); 
            }else {
                $files[] ="{$path}/$file";
            }
        } 
        $dir->close(); 
        
        return $files;
    }
}
