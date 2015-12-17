<?php
class Pinyin
{
    /**
     * 把汉字转成拼音
     *
     * @param string $words gbk编辑的文字
     * @param bool $only_head 只取首字终
     * @param bool $is_gbk 不是gbk则默认从utf8转成gbk
     * @return string in:张三,out:zhangsan, in:张3,out:zhang_
     */
    public static function getPinyin($words, $only_head=0, $is_gbk=false) {
        if($is_gbk === false)
        {
            $words = mb_convert_encoding($words, 'gbk', 'utf8');
        }
        
        $restr = '';
        $words = trim($words);
        $slen = strlen($words);
        if($slen < 2)
        {
            return $words;
        }
        $pinyins = self::initPinyinData();
        for($i=0; $i<$slen; $i++)
        {
            if(ord($words[$i])>0x80)
            {
                $c = $words[$i].$words[$i+1];
                $i++;
                if(isset($pinyins[$c]))
                {
                    if($only_head == 0)
                    {
                        $restr .= $pinyins[$c];
                    }
                    else
                    {
                        $restr .= $pinyins[$c][0];
                    }
                }
                else
                {
                    $restr .= "_";
                }
            }
            else if(eregi("[a-z0-9]", $words[$i]) )
            {
                $restr .= $words[$i];
            }
            else
            {
                $restr .= "_";
            }
        }
        return $restr;
    }

    public static function initPinyinData() {
        static $pinyins = array();
        $data_file = dirname(__FILE__) . '/pinyin.dat';

        $fp = fopen($data_file, 'r');
        while(!feof($fp))
        {
            $line = trim(fgets($fp));
            $pinyins[$line[0] . $line[1]] = substr($line, 3);
        }
        fclose($fp);
        return $pinyins;
    }
}
