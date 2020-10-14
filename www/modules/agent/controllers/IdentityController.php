<?php

namespace app\modules\agent\controllers;

use app\models\Agent;
use app\models\AgentProfileForm;
use app\models\Sms;
use Yii;
use yii\web\Response;

/**
 * 代理商信息管理
 * Class IdentityController
 * @package app\modules\agent\controllers
 */
class IdentityController extends BaseController
{
    use UploadControllerTrait;

    /**
     * 用户退出
     * @return Response
     */
    public function actionLogout()
    {
        $this->agent->logout();
        Yii::$app->user->setReturnUrl(['/agent']);
        return $this->redirect(['/agent/login']);
    }

    /**
     * 用户设置
     * @return string
     */
    public function actionProfile()
    {
        $model = new AgentProfileForm();
        $agent = Agent::findOne($this->agent->id);
        $model->setAttributes($agent->attributes, false);
        if ($model->load($this->post())) {
            $mobile = $model->mobile;
            $sms_code = $model->sms_code;
            if ($mobile != $agent->mobile && empty($sms_code)) {
                Yii::$app->session->addFlash('error', '请填写验证码');
            } elseif (!empty($mobile) && !empty($sms_code) && !Sms::checkCode($mobile, Sms::TYPE_BIND_MOBILE, $sms_code)) {
                Yii::$app->session->addFlash('error', '验证码错误');
            } elseif ($model->validate() && $model->save()) {
                Yii::$app->agent->setIdentity(Agent::findOne($agent->id));
                Yii::$app->session->addFlash('success', '用户信息已保存。');
            }
        }
        return $this->render('profile', [
            'model' => $model
        ]);
    }

    /**
     * 绑定手机号AJAX接口
     * @return array
     */
    public function actionSendBindPhoneSmsCode()
    {
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return ['message' => '必须输入手机号码。'];
        }
        /** @var Agent $agent */
        $agent = Agent::find()->where(['mobile' => $mobile])->one();
        if (!empty($agent)) {
            return ['message' => '手机号已被绑定请更换其他手机号。'];
        }
        $agent_info = Yii::$app->get('agent');
        $r = Sms::sendCode(Sms::U_TYPE_AGENT, $agent_info->id, $mobile, Sms::TYPE_BIND_MOBILE);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }
}
