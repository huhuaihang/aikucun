<?php

namespace app\modules\h5\controllers;
use Yii;

/**
 * 视频小站
 * Class SurveyController
 * @package app\modules\h5\controllers
 */
class VideoController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest && !$this->isAjax()) {
            Yii::$app->user->loginRequired();
            return false;
        }
        return parent::beforeAction($action);
    }
    /**
     * 视频小站主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 视频小站宣传短片列表
     * @return string
     */
    public function actionShort()
    {
        return $this->render('index_short');
    }

    /**
     * 视频小站营销课堂列表
     * @return string
     */
    public function actionMarket()
    {
        return $this->render('index_market');
    }

    /**
     * 搜索
     * @return string
     */
    public function actionSearch()
    {
        return $this->render('search');
    }

    /**
     * 详情
     */
    public function actionView()
    {
        return $this->render('view');
    }
}
