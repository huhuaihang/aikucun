<?php

namespace app\modules\merchant\controllers;

use app\controllers\BaseController;
use app\models\Merchant;
use app\models\MerchantLoginForm;
use app\models\Sms;
use app\models\Util;
use yii\helpers\Url;
use Yii;

/**
 * 商户登录
 * Class LoginController
 * @package app\modules\merchant\controllers
 */
class LoginController extends BaseController
{
    /**
     * 商户登录
     * @return string
     */
    public function actionIndex()
    {
        $model = new MerchantLoginForm();
        if ($model->load($this->post()) && $model->login()) {
            $merchant = $model->getMerchant();
            Yii::$app->merchant->login($merchant, $model->rememberMe ? 3600 * 24 * 30 : 0);
            return Yii::$app->response->redirect(Yii::$app->merchant->getReturnUrl(['/merchant']));
        }
        return $this->renderPartial('index', [
            'model' => $model
        ]);
    }

    /**
     * 忘记密码
     * @return string|array
     */
    public function actionForgotPassword()
    {
        if ($this->isPost()) {
            $mobile = $this->post('mobile');
            $sms_code = $this->post('sms_code');
            if (empty($mobile)) {
                return ['message' => '必须输入手机号码。'];
            }
            if (empty($sms_code)) {
                return ['message' => '必须输入短信验证码。'];
            }
            if (!Sms::checkCode($mobile, Sms::TYPE_FORGOT_PASSWORD, $sms_code)) {
                return ['message' => '短信验证码错误。'];
            }
            /** @var Merchant $merchant */
            $merchant = Merchant::find()->where(['mobile' => $mobile])->one();
            $new_auth_key = Util::randomStr(32, 7);
            Merchant::updateAll(['auth_key' => $new_auth_key], ['id' => $merchant->id]);
            $merchant->auth_key = $new_auth_key;
            Yii::$app->merchant->login($merchant);
            return [
                'result' => 'success',
                'location' => Url::to(['/merchant/identity/profile', 'tab' => 'edit-password'])
            ];
        }
        return $this->renderPartial('forgot_password');
    }

    /**
     * 发送忘记密码短信验证码AJAX接口
     * @return array
     */
    public function actionSendForgotPasswordSmsCode()
    {
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return ['message' => '必须输入手机号码。'];
        }
        /** @var Merchant $merchant */
        $merchant = Merchant::find()->where(['mobile' => $mobile])->one();
        if (empty($merchant)) {
            return ['message' => '没有找到绑定手机号码。'];
        }
        $r = Sms::sendCode(Sms::U_TYPE_MERCHANT, $merchant->id, $mobile, Sms::TYPE_FORGOT_PASSWORD);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }
}
