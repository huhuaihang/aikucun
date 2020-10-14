<?php

namespace app\modules\h5\controllers;

/**
 * 问卷调查
 * Class SurveyController
 * @package app\modules\h5\controllers
 */
class SurveyController extends BaseController
{
    /**
     * 问卷调查主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
