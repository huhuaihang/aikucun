<?php

namespace app\modules\h5\controllers;

/**
 * 用户注册
 * Class RegisterController
 * @package app\modules\h5\controllers
 */
class RegisterController extends BaseController
{
    /**
     * 用户注册
     * @return string|array
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 用户激活
     * @return string|array
     */
    public function actionActivate()
    {
        return $this->render('activate');
    }

    /**
     * 用户绑定
     * @return string|array
     */
    public function actionBind()
    {
        return $this->render('bind');
    }
}
