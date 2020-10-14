<?php

namespace app\modules\h5\controllers;
use Yii;

/**
 * 图文素材
 * Class SurveyController
 * @package app\modules\h5\controllers
 */
class SourceController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }
    /**
     * 图文素材主页
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest && !$this->isAjax()) {
            Yii::$app->user->loginRequired();
            return false;
        }
        return $this->render('index');
    }

    /**
     * 营销素材列表
     * @return string
     */
    public function actionMarket()
    {
        if (Yii::$app->user->isGuest && !$this->isAjax()) {
            Yii::$app->user->loginRequired();
            return false;
        }
        return $this->render('index_market');
    }

    /**
     * 搜索图文
     */
    public function actionSearch()
    {
        if (Yii::$app->user->isGuest && !$this->isAjax()) {
            Yii::$app->user->loginRequired();
            return false;
        }
        return $this->render('search');
    }

    /**
     * 图文详情
     */
    public function actionView()
    {
        return $this->render('view');
    }

    /**
     * 新人入门详情
     */
    public function actionNewView()
    {
        return $this->render('new_view');
    }
}
