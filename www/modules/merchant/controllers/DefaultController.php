<?php

namespace app\modules\merchant\controllers;

/**
 * 商户默认控制器
 * Class DefaultController
 * @package app\modules\merchant\controllers
 */
class DefaultController extends BaseController
{
    /**
     * 商户主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index',[
            'sid' => $this->shop->id,
        ]);
    }
}
