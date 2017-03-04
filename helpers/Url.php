<?php

namespace ihipop\yii2\helpers;

use Yii;

/**
 * Class Url 相比原生的Yii多了一些方法 主要是处理相对协议URl等
 * @author ihipop@gmail.com
 * @package ihipop\yii2\helpers
 */
class Url extends \yii\helpers\Url
{

    /**
     * 为相对协议增加Scheme 见 RFC 3986 第4.2节 或者 https://www.paulirish.com/2010/the-protocol-relative-url/
     * 如果不是 裸协议（相对协议），不做任何处理 如果生成路由 建议使用Yii的Url:to() 会自动加头
     * 老的 RFC 2396 与 RFC 3986 矛盾的地方 自动失效，以 RFC 3986 为准
     *
     * @param      $url
     * @param null $forceScheme
     * @return string
     */
    public static function addScheme($url, $forceScheme = null)
    {
        if (static::isSchemeRelativeUrl($url)) {
            if (is_string($forceScheme)) {
                $http = $forceScheme;
            } else {
                $secure = Yii::$app->request->getIsSecureConnection();
                $http = $secure ? 'https' : 'http';
            }
            $url = $http . ":" . $url;
        }

        return $url;
    }

    /**
     * 判断是否是相对协议路径 (裸协议路径)
     *
     * @param $url
     * @return bool
     */
    public static function isSchemeRelativeUrl($url)
    {
        return strncmp($url, '//', 2) === 0;
    }


    /**
     * 判断是否是相对路径 如果是 返回 parse_url 数组，以便进一步利用 否 返回 false
     *
     * @param $url
     * @return bool|mixed
     */
    public static function isRelativeUrl($url)
    {
        $_url = parse_url($url);
        if (!empty($_url['path']) && ($_url['path'] === $url)) {
            return $_url;
        }

        return false;
    }

    /**
     * 按照 RFC3986 切掉协议头
     *
     * @param $url
     * @return string
     */
    public static function removeScheme($url)
    {
        $url = explode('//', $url);
        unset($url[0]);

        return "//" . implode('//', $url);
    }

    /**
     * 更换协议头 除非不是使用HTTP 或者追求效率，建议直接使用Url:to()的第二个参数进行协议替换
     *
     * @param $url
     * @param $scheme
     * @return string
     */
    public static function replaceScheme($url, $scheme)
    {
        return $scheme . ":" . static::removeScheme($url);
    }
}