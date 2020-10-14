<?php

namespace app\modules\agent\controllers;

use app\controllers\BaseController;
use app\models\Agent;
use app\models\AgentLoginForm;
use app\models\Sms;
use app\models\Util;
use Yii;
use yii\helpers\Url;

/**
 * 代理商登录
 * Class LoginController
 * @package app\modules\agent\controllers
 */
class LoginController extends BaseController
{
    /**
     * 代理商登录
     * @return string
     */
    public function actionIndex()
    {
        $model = new AgentLoginForm();
        if ($model->load($this->post()) && $model->login()) {
            $agent = $model->getAgent();
            Yii::$app->agent->login($agent, $model->rememberMe ? 3600 * 24 * 30 : 0);
            return Yii::$app->response->redirect(Yii::$app->agent->getReturnUrl(['/agent']));
        }
        return $this->renderPartial('index', [
            'model' => $model,
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
            /** @var Agent $agent */
            $agent = Agent::find()->where(['mobile' => $mobile])->one();
            $new_auth_key = Util::randomStr(32, 7);
            Agent::updateAll(['auth_key' => $new_auth_key], ['id' => $agent->id]);
            $agent->auth_key = $new_auth_key;
            Yii::$app->agent->login($agent);
            return [
                'result' => 'success',
                'location' => Url::to(['/agent/identity/profile', 'tab' => 'edit-password'])
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
        /** @var Agent $agent */
        $agent = Agent::find()->where(['mobile' => $mobile])->one();
        if (empty($agent)) {
            return ['message' => '没有找到绑定手机号码。'];
        }
        $r = Sms::sendCode(Sms::U_TYPE_AGENT, $agent->id, $mobile, Sms::TYPE_FORGOT_PASSWORD);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }
}
