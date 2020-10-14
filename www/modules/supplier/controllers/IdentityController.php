<?php

namespace app\modules\supplier\controllers;

use app\models\Supplier;
use app\models\SupplierConfigForm;
use Yii;
use yii\base\Exception;
use yii\web\Response;

/**
 * 供货商信息管理
 * Class IdentityController
 * @package app\modules\supplier\controllers
 */
class IdentityController extends BaseController
{
    /**
     * 用户退出
     * @return Response
     */
    public function actionLogout()
    {
        $this->supplier->logout();
        Yii::$app->user->setReturnUrl(['/supplier']);
        return $this->redirect(['/supplier/login']);
    }

    /**
     * 用户设置
     * @return string
     * @throws Exception
     */
    public function actionProfile()
    {
        $supplier = Supplier::findOne(['id' => $this->supplier->id]);
        $configForm = new SupplierConfigForm($supplier->id);
        if ($supplier->load($this->post()) && $configForm->load($this->post())) {
            $postSupplier = $this->post('Supplier');
            if (!empty($postSupplier) && !empty($postSupplier['password'])) {
                $supplier->password = Yii::$app->security->generatePasswordHash($postSupplier['password']);
            } else {
                $supplier->password = $supplier->oldAttributes['password'];
            }
            if ($supplier->save()) {
                $configForm->sid = $supplier->id;
                if (!$configForm->save()) {
                    Yii::$app->session->addFlash('warning', '无法保存供货商配置信息。');
                }
                Yii::$app->session->addFlash('success', '用户信息已保存。');
            }
        }
        return $this->render('profile', [
            'supplier' => $supplier,
            'configForm' => $configForm,
        ]);
    }
}
