<?php

namespace app\modules\supplier\controllers;

use Yii;

/**
 * 供货商后台控制器基类
 * Class BaseController
 * @package app\modules\supplier\controllers
 */
class BaseController extends \app\controllers\BaseController
{
    /**
     * @var string 默认layouts文件名称
     */
    public $layout = 'main'; // @app/themes/basic/modules/supplier/views/layouts/main.php
    /**
     * @var $supplier false|\yii\web\User
     */
    protected $supplier = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->supplier = Yii::$app->get('supplier');
        // 判断登录状态
        if (empty($this->supplier) || $this->supplier->isGuest) {
            $this->supplier->loginRequired();
            return false;
        }
        return parent::beforeAction($action);
    }
}
