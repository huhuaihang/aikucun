<?php

namespace app\modules\api\controllers;

use Yii;

/**
 * Git服务器推送
 * Class GitController
 * @package app\modules\api\controllers
 */
class GitController extends \app\controllers\BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * GIT PUSH
     */
    public function actionPush()
    {
        $dir = dirname(Yii::getAlias('@app'));
        system('cd ' . $dir . ' && touch git_pull');
    }
}
