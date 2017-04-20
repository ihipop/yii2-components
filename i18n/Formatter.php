<?php
/**
 * @link https://github.com/ihipop/yii2-components
 * @copyright  Copyright (c) 2017 ihipop@gmail.com
 * @license https://github.com/ihipop/yii2-components/blob/master/LICENSE.md
 */
namespace ihipop\yii2\i18n;

/**
 * Extend the Formatter which provides a set of commonly used data formatting methods from \yii\i18n\Formatter.
 *
 * @since @todo
 */
class Formatter extends \yii\i18n\Formatter {

    public function asTimestampToDatetime($value,$format = null)
    {
        if ($value === null || $value === 0) {
            return $this->nullDisplay;
        }
        return $this->asDatetime($value,$format);
    }


    public function asTimestampToDate($value,$format = null)
    {
        if ($value === null || $value === 0) {
            return $this->nullDisplay;
        }
        return $this->asDate($value,$format);
    }
}
