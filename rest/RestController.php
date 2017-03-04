<?php
namespace ihipop\yii2\rest;

use Yii;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\Response;

/**
 *  RestController implements the \yii\rest\Controller With Some `UnSmart` Changes :)
 *  @author ihipop@gmail.com 2016-12-07 14:03:21
 *  @package ihipop\yii2\rest
 */
class RestController extends \yii\rest\Controller
{

    //    {
    //    "statusCode": 404,
    //    "errorCode": "HTTP_404",
    //    "message": "HTTP 404 Not Found (#404)",
    //    "body": []
    //    }

    // Override the ErrorCode in rest Response root struct
    // if not set, will auto detect
    protected $restErrorCode;
    // Override the restMessage in rest Response root struct
    // if not set, will auto detect
    protected $restMessage;
    // some exception cause by \yii\web\ErrorHandler will run errorAction to render a error page in HTML
    // set this to true to suppress the html body
    protected $suppressNoneRestHttpBody = true;
    // $defaultCallbackName for JSONP
    protected $defaultCallbackName = 'callback';

    //Change me Here，not in Child behaviors()
    public  function  getContentNegotiator(){
        return [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
                //add support for jsonp
                'text/javascript' => Response::FORMAT_JSONP,
            ]
        ];
    }

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function behaviors (){
        $parentBehaviors = parent::behaviors();
        //Have Already done in init(), should not be done in behaviors
        unset($parentBehaviors['contentNegotiator']);
        $selfBehaviors = [];
        return ArrayHelper::merge($parentBehaviors,$selfBehaviors);
    }

    /**
     * @throws \Exception
     */
    public function init(){
        parent::init();
        //add spuuort for json input
        Yii::$app->request->parsers = ArrayHelper::merge(Yii::$app->request->parsers,[
            'application/json' => 'yii\web\JsonParser',
        ]);
        // add event to uniform the info
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND,[$this,'buildRestReponse']);
        //Must RUN before Before Action,So,Do it here.  by manually
        $this->negotiatorContent();
    }

    public function negotiatorContent(){
        $response = Yii::$app->response;
        // TODO： Check if the Content has already negotiated
        /** @var  $contentNegotiator \yii\filters\ContentNegotiator */
        $contentNegotiator = Yii::createObject($this->getContentNegotiator());
        if ($response->format == Response::FORMAT_HTML && !in_array($response->format,$contentNegotiator->formats)){
            try {
                $contentNegotiator->negotiate();
            }catch (\Exception $e) {
                // if negotiate failed select the first ``format`` defined in  $contentNegotiator configuration to avoid a uncaught exception
                $response->format = current($contentNegotiator->formats);
                // then expose the exception in that format
                //$response->setStatusCode(500);
                throw $e;
            }
        }
    }

    protected function buildRestReponse( \yii\base\Event $event){
        $response = $event->sender;
        $data = $response->data;
        if (!is_string($data)){
            $data = (array)$data;
        }
        $statusMessage = $response->statusText;
        $response->data = [];
        $response->data['statusCode'] = $response->getStatusCode();

        //build `errorCode` in response json's root struct
        if (!empty($this->restErrorCode)){
            $response->data['errorCode'] = strval($this->restErrorCode);
        }else{
            if (!empty($data['restErrorCode'])){
                $response->data['errorCode'] = $data['restErrorCode'];
            }elseif (!empty($data['code'])){
                $response->data['errorCode'] = $data['code'];
            }elseif($response->isSuccessful){
                $response->data['errorCode'] = 0;
            }else{
                $response->data['errorCode'] = 'HTTP_'.$response->data['statusCode'];
            }
            unset($data['code']);
            unset($data['restErrorCode']);
            //$response->data['errorCode'] = (!empty($data['code']) ? $data['code'] : $response->isSuccessful?0:'HTTP_'.$response->data['statusCode']);
            //$response->data['errorCode'] = strval($response->data['errorCode']);
        }

        //build `message` in response json's root struct
        if (!empty($this->restMessage)){
            $response->data['message'] = strval($this->restMessage);
        }else {
            if (!empty($data['restMessage'])) {
                $response->data['message'] = $data['restMessage'];
            } elseif (!empty($data['message'])) {
                $response->data['message'] = $data['message'];
            } else {
                $response->data['message'] = (!$response->isSuccessful && $statusMessage) ? sprintf('HTTP %s %s (#%1$s)',
                    $response->data['statusCode'], $statusMessage) : null;
            }
        }
        unset($data['restMessage']);
        unset($data['message']);
        $response->data['body']= $data;
        //add support for jsonp build struct for jsonp
        if ($response->format == $response::FORMAT_JSONP && empty($response->data['callback'])){
            $data = $response->data;
            $response->data = [];
            $response->data['data'] = $data;
            $response->data['callback'] = $this->defaultCallbackName;
        }
        return $response;
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        // override  me if necessary
        return [
            //'index' => ['POST'],
        ];
    }
}
