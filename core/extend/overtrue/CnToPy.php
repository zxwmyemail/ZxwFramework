<?php
namespace core\extend\overtrue;
/********************************************************************************************
 汉字转拼音，github地址： 
 https://github.com/overtrue/pinyin
*********************************************************************************************/

use Overtrue\Pinyin\Pinyin;
use Overtrue\Pinyin\MemoryFileDictLoader;
use Overtrue\Pinyin\GeneratorFileDictLoader;

class CnToPy {
    
    private static $_instance;
    private static $_pinyin;

    /**
     * 私有化克隆机制
     *
     */
    private function __clone() {}

    /**
     * 构造函数
     * @param string  $type         allmem、io、littlemem
     *
     */
    public function __construct($type) {
        // 小内存型，默认，将字典分片载入内存，适用于内存比较紧张的环境，优点：占用内存小，转换不如内存型快
        if ($type == 'littlemem') {
            self::$_pinyin = new Pinyin();
        } else if ($type == 'allmem') {
            self::$_pinyin = new Pinyin('Overtrue\Pinyin\MemoryFileDictLoader');
        } else {
            self::$_pinyin = new Pinyin('Overtrue\Pinyin\GeneratorFileDictLoader');
        }
    }

    /**
     * 获取单例
     *
     */
    public static function getInstance($type = 'littlemem')
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($type);
        }
        return self::$_instance;
    }

    /**
     * 生成用于链接的拼音字符串
     * 如：$pinyin->permalink('带着希望去旅行', '.'); 
     * 结果：dai.zhe.xi.wang.qu.lyu.xing
     */
    public static function permalink($content, $splitChar = '-')
    {
        self::getInstance(); 
        return self::$_pinyin->permalink($content, $splitChar);
    }
    
    /**
     * 获取首字符字符串
     * 如：
     * $pinyin->abbr('带着希望去旅行'); // dzxwqlx
     * $pinyin->abbr('带着希望去旅行', '-'); // d-z-x-w-q-l-x
     * $pinyin->abbr('你好2018！', PINYIN_KEEP_NUMBER); // nh2018
     * $pinyin->abbr('Happy New Year! 2018！', PINYIN_KEEP_ENGLISH); // HNY2018
     *
     */
    public static function abbr($content, $splitChar = '-')
    {
        self::getInstance(); 
        return self::$_pinyin->abbr($content, $splitChar);
    }


    /**
     * 翻译整段文字为拼音
     * 如：
     * $pinyin->sentence('带着希望去旅行，比到达终点更美好！');
     * 结果： dai zhe xi wang qu lyu xing, bi dao da zhong dian geng mei hao!
     * $pinyin->sentence('带着希望去旅行，比到达终点更美好！', PINYIN_TONE);
     * 结果： dài zhe xī wàng qù lǚ xíng, bǐ dào dá zhōng diǎn gèng měi hǎo!
     *
     */
    public static function sentence($content, $withTone = false)
    {
        self::getInstance(); 
        if ($withTone) {
            return self::$_pinyin->sentence($content, PINYIN_TONE);
        } else {
            return self::$_pinyin->sentence($content);
        }
    }
        

    /**
     * 翻译姓名, 姓名的姓的读音有些与普通字不一样，比如 ‘单’ 常见的音为 dan，而作为姓的时候读 shan。
     * $pinyin->name('单某某'); // ['shan', 'mou', 'mou']
     * $pinyin->name('单某某', PINYIN_TONE); // ["shàn","mǒu","mǒu"]
     *
     */
    public static function name($name, $withTone = false)
    {
        self::getInstance(); 
        if ($withTone) {
            return self::$_pinyin->name($name, PINYIN_TONE);
        } else {
            return self::$_pinyin->name($name);
        }
    }

}

?>
