<?php

namespace app\modules\agent\controllers;

use Yii;

/**
 * 代理商后台控制器基类
 * Class BaseController
 * @package app\modules\agent\controllers
 */
class BaseController extends \app\controllers\BaseController
{
    /**
     * @var string 默认layouts文件名称
     */
    public $layout = 'main'; // @app/themes/basic/modules/agent/views/layouts/main.php
    /**
     * @var $agent false|\yii\web\User
     */
    protected $agent = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->agent = Yii::$app->get('agent');
        // 判断登录状态
        if (empty($this->agent) || $this->agent->isGuest) {
            $this->agent->loginRequired();
            return false;
        }
        return parent::beforeAction($action);
    }
}
