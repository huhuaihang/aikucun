<?php

namespace app\modules\h5\controllers;

/**
 * 购物车控制器
 * Class CartController
 * @package app\modules\h5\controllers
 */
class CartController extends BaseController
{
    /**
     * 购物车列表
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
