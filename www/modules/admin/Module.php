<?php

namespace app\modules\admin;

use Yii;

/**
 * 管理后台模块
 * Class Module
 * @package app\modules\admin
 */
class Module extends \yii\base\Module
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->errorHandler->errorAction = 'admin/default/error';
    }
}
