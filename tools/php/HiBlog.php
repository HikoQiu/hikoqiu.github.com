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
     * @param string $title
     * @param string $stitle
     * @param string $author
     */
    public function genPost($title, $stitle, $author) {
        if(!$this->getPath()) {
            throw new Exception('Empty md path', 1002);
        }
        
        // post already exit
        if(file_exists($this->getPath())) {
            return true;
        }
        
        // copy tpl as basic content
        $content = file_get_contents($this->getTplFile());
        $content = $this->_replaceBasicInfo($content, $title, $stitle, $author);
        return file_put_contents($this->getPath(), $content);
    }
    
    /**
     * Replace tpl's basic info
     * @param string $content
     * @param string $title
     * @param string $stitle
     * @param string $author
     * @return string
     */
    private function _replaceBasicInfo($content, $title, $stitle, $author) {
        $params = [
            '{title}' => $title,
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
     * @param string $filename
     */
    public function parsePostMd($filename) {
        $content = file_get_contents($filename);
        
        // 1.1 header & body of the post
        $data = explode('---', $content);
        if(count($data) <= 2) {
            throw new Exception('Invalid post md content');
        }
        
        $header = explode("\n", $data[1]);
        $arrHeader = [];
        foreach($header as $item) {
            $item = trim($item, ' 　');
            if(empty($item)) {
                continue;
            }
            $arr = explode(':', $item);
            $arrHeader[$arr[0]] = trim(implode(':', array_slice($arr, 1)));
        }
        
        $body = array_slice($data, 2);
        $body = implode('---', $body);
        $body = Markdown::defaultTransform($body);
        
        // 2.1 set to html tpl
        $repData = [
            '{$title}' => $arrHeader['title'],
            '{$subtitle}' => $arrHeader['subtitle'],
            '{$author}' => $arrHeader['author'],
            '{$ctime}' => $arrHeader['ctime'],
            '{$tags}' => $arrHeader['tags'],
            '{$body}' => $body
        ];
        $tpl = file_get_contents($this->getTplFile());
        foreach($repData as $key => $val) {
            $tpl = str_replace($key, $val, $tpl);
        }
        
        $ctime = $arrHeader['ctime'];
        $year = date('Y', strtotime($ctime));
        $month = date('m', strtotime($ctime));
        $lang = $arrHeader['lang'];
        $cat = $arrHeader['category'];
        
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
    
    /**
     * create index to index.html
     */
    public function index($path) {
        if(!is_dir($path)) {
            throw new Exception("Path '{$path}' for index is not dir.");
        }
        
        // through the dir to list all html files
        $files = $this->dirFiles($path);
        if(empty($files)) {
            // @todo ..
        }
        
        $timeMapCon = [];
        $times = [];
        foreach($files as $index => $file) {
            // parse title & title & tags
            $meta = $this->getMeta($file);
            $file = ltrim($file, './');
            $times[$index] = strtotime($meta['ctime']);
            $ctime = date('Y/m/d', $times[$index]);
            $line = "{$ctime} &raquo; [{$meta['title']}]({$file})  \r\n";
            $timeMapCon[$times[$index]] = $line;
        }
        
        // reverse order by ctime
        rsort($times);
        $content = '';
        $lastMonth = '';
        foreach($times  as $t) {
            $month = date('Y/m', $t);
            if($lastMonth!= $month) {
                $content .= "### {$month}  \r\n";
            }
            
            $content .= $timeMapCon[$t];
            
            $lastMonth = $month;
        }
        
        $content = Markdown::defaultTransform($content);
        $tpl = file_get_contents($this->getTplFile());
        $content = str_replace('{$content}', $content, $tpl);
        file_put_contents(PATH_ROOT.'/index.html', $content);
        return $files;
    }
    
    /**
     * get meta data of the file
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
            'tags' => $meta[3]
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
