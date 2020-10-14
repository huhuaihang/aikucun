<?php

namespace app\modules\h5\controllers;

use Yii;

/**
 * 意见反馈
 * Class FeedbackController
 * @package app\modules\h5\controllers
 */
class FeedbackController extends BaseController
{
    /**
     * 编辑反馈
     * @return string|\yii\web\Response
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionEdit()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        return $this->render('edit');
    }
}
