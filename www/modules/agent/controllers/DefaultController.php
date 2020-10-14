<?php

namespace app\modules\agent\controllers;

/**
 * 代理后台默认控制器
 * Class DefaultController
 * @package app\modules\agent\controllers
 */
class DefaultController extends BaseController
{
    /**
     * 代理后台主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
