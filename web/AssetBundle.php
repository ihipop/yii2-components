<?php


namespace ihipop\yii2\web;


use Yii;

/**
 * Class AssetBundle
 * 为原生的AssetBundle添加一些适合国情的方法
 * @package ihipop\yii2\web
 */
class AssetBundle extends \yii\web\AssetBundle
{
    //配合有些页面内的js是需要reayd再运行的 默认改成在头部输出
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];

    // public $baseUrl = '@static';

    public static $version = '20170306.v1';

    public function init()
    {
        parent::init();
        //为资源添加版本号支持 更加适合CDN环境
        foreach ($this->css as $k=>$v) {
            $glue =  (strrpos($v,'?') !== false)?'&':'?'.'ver=';
            $this->css["$k"] = $v.$glue.self::$version;
        }
        foreach ($this->js as $k=>$v) {
            $glue =  (strrpos($v,'?') !== false)?'&':'?'.'ver=';
            $this->js["$k"] = $v.$glue.self::$version;;
        }
    }
}