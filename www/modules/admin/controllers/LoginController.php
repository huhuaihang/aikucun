<?php

namespace app\modules\admin\controllers;

use app\controllers\BaseController;
use app\models\ManagerLog;
use app\models\ManagerLoginForm;
use Yii;

/**
 * 管理员登录
 * Class LoginController
 * @package app\modules\admin\controllers
 */
class LoginController extends BaseController
{
    /**
     * 管理员登录
     * @return string
     */
    public function actionIndex()
    {

        $model = new ManagerLoginForm();
        if ($model->load($this->post()) && $model->login()) {
            $manager = $model->getManager();
            Yii::$app->manager->login($manager, $model->rememberMe ? 3600 * 24 * 30 : 0);
            ManagerLog::info($manager->id, '登录');
            return Yii::$app->response->redirect(Yii::$app->manager->getReturnUrl(['/admin']));
        }
        return $this->renderPartial('index', [
            'model' => $model
        ]);
    }
}