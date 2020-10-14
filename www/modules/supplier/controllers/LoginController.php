<?php

namespace app\modules\supplier\controllers;

use app\controllers\BaseController;
use app\models\SupplierLoginForm;
use Yii;

/**
 * 供货商登录
 * Class LoginController
 * @package app\modules\supplier\controllers
 */
class LoginController extends BaseController
{
    /**
     * 供货商登录
     * @return string
     */
    public function actionIndex()
    {
        $model = new SupplierLoginForm();
        if ($model->load($this->post()) && $model->login()) {
            $supplier = $model->getSupplier();
            Yii::$app->supplier->login($supplier, $model->rememberMe ? 3600 * 24 * 30 : 0);
            return Yii::$app->response->redirect(Yii::$app->supplier->getReturnUrl(['/supplier']));
        }
        return $this->renderPartial('index', [
            'model' => $model
        ]);
    }
}
