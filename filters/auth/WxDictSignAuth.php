<?php
namespace ihipop\yii2\filters\auth;

use yii\base\InvalidConfigException;
use yii\web\UnauthorizedHttpException;

/**
 * Class WxDictSignAuth
 * 完全模拟微信的字典序签名算法 https://pay.weixin.qq.com/wiki/doc/api/jsapi.php?chapter=4_3
 * @author ihipop@gmail.com 2016-12-07 14:08:58
 * @package ihipop\yii2\filters\auth
 */
class WxDictSignAuth extends \yii\filters\auth\AuthMethod
{
    /**
     * @var string the parameter name for passing the access token
     */
    public $signParam = 'sign';
    public $key;
    public $keyParam = 'key';

    protected  $message;

    public function init(){
        parent::init();
        if ($this->key === null && !empty($this->keyParam)){
            throw new InvalidConfigException('key Cannot be Null When $keyParam is set');
        }
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        if ($request->isGet){
            $sign = $request->get($this->signParam);
            $data = $request->get();
        }else{
            $sign = $request->getBodyParam($this->signParam);
            $data = $request->getBodyParams();
        }

        if (is_string($sign)) {
            $rightSign = $this->calcSign($data);
            if ($sign === $rightSign) {
                return $sign;
            }
        }

        if ($sign !== null) {
            if (YII_DEBUG){
                $this->message = isset($rightSign)?sprintf('Right Sign is: %s with Paras: %s',$rightSign,urldecode(http_build_query($data))):'';
            }
            $this->handleFailure($response);
        }

        return null;
    }


    /**
     * @inheritdoc
     */
    public function handleFailure($response)
    {
        $message = 'You are requesting with an invalid credential.';
        if ($this->message){
            $message = $message.$this->message;
        }
        throw new UnauthorizedHttpException($message);
    }


    public  function calcSign($data,$key=null){
        $key = ($key===null)?$this->key:$key;
        $keyStr = empty($this->keyParam)?'':sprintf('&%s=%s',$this->keyParam,$key);
        unset($data["$this->signParam"]);
        ksort($data);
        $data = array_filter($data,function($v){ return $v !== '';});
        $str = urldecode(http_build_query($data).$keyStr);
        return strtoupper(md5($str));
    }
}
