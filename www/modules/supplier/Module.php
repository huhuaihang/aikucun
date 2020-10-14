<?php

namespace app\modules\supplier;

use Yii;

/**
 * 供货商管理后台模块
 * Class Module
 * @package app\modules\supplier
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\supplier\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->errorHandler->errorAction = 'supplier/default/error';
    }
}
