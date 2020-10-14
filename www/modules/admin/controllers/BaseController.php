<?php

namespace app\modules\admin\controllers;

use Yii;

/**
 * 管理后台控制器基类
 * Class BaseController
 * @package app\modules\admin\controllers
 */
class BaseController extends \app\controllers\BaseController
{
    /**
     * @var string 默认layouts文件名称
     */
    public $layout = 'main'; // @app/themes/basic/modules/admin/views/layouts/main.php
    /**
     * @var $manager false|\yii\web\User
     */
    protected $manager = false;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->manager = Yii::$app->get('manager');
        // 判断登录状态
        if (empty($this->manager) || $this->manager->isGuest) {
            $this->manager->loginRequired();
            return false;
        }
        return parent::beforeAction($action);
    }
}
