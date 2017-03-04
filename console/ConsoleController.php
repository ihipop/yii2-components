<?php
namespace ihipop\yii2\console;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Class ConsoleController
 * 控制台控制器基类
 * @author ihipop@gmail.com
 * @package ihipop\yii2\console
 */

class ConsoleController extends Controller
{
    public function msg($msg,$eol = PHP_EOL){
        $time = $this->ansiFormat(date('[Y-m-d H:i:s]: ').sprintf('[%s] ',$this->action->id), Console::FG_PURPLE);
        $this->stdout($time.$msg.$eol);
    }
    public function error($msg,$eol = PHP_EOL){
        $time = $this->ansiFormat(date('[Y-m-d H:i:s]: ').sprintf('[%s] ',$this->action->id), Console::FG_RED);
        $this->stderr($time.$msg.$eol);
    }

    public function wait($msg=null,$seconds=10){
        if ($msg === null) $msg = 'Nothing Found,';
        $msg = sprintf('%s Wait in seconds: ',$msg);
        $this->msg($msg.$seconds);
        sleep($seconds);
//        Console::startProgress(0,$seconds,$msg, false);
//        for ($i=1;$i<=$seconds;$i++){
//            Console::updateProgress($i,$seconds);
//            sleep(1);
//        }
//        Console::endProgress(sprintf('%s/%s Done',$i,$seconds));
    }
}