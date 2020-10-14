<?php

namespace app\modules\admin\controllers;

use app\models\Manager;
use app\models\ManagerLog;
use app\models\ManagerProfileForm;
use Yii;
use yii\web\Response;

/**
 * 管理员信息管理
 * Class IdentityController
 * @package app\modules\admin\controllers
 */
class IdentityController extends BaseController
{
    /**
     * 用户退出
     * @return Response
     */
    public function actionLogout()
    {
        ManagerLog::info($this->manager->id, '退出');
        $this->manager->logout();
        $this->manager->setReturnUrl(['/admin']);
        return $this->redirect(['/admin/login']);
    }

    /**
     * 用户设置
     * @return string
     */
    public function actionProfile()
    {
        $model = new ManagerProfileForm();
        $manager = Manager::findOne($this->manager->id);
        $model->setAttributes($manager->attributes, false);
        if ($model->load($this->post()) && $model->validate() && $model->save()) {
            // 更新Session
            $manager = Manager::findOne($this->manager->id);
            Yii::$app->manager->setIdentity($manager);
            Yii::$app->session->addFlash('success', '用户信息已保存。');
        }
        return $this->render('profile', [
            'model' => $model
        ]);
    }
}
