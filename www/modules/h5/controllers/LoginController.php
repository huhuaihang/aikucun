<?php

namespace app\modules\h5\controllers;

/**
 * 用户登录
 * Class LoginController
 * @package app\modules\h5\controllers
 */
class LoginController extends BaseController
{
    /**
     * 用户登录
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 用户登录手机号登录
     * @return string
     */
    public function actionMobile()
    {
        return $this->render('mobile');
    }

    /**
     * 用户登录
     * @return string
     */
    public function actionHead()
    {
        return $this->render('index_head');
    }

    /**
     * 忘记登录密码
     * @return string|array
     */
    public function actionLostPassword()
    {
        return $this->render('lost_password');
    }
}
