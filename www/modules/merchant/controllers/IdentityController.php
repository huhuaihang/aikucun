<?php

namespace app\modules\merchant\controllers;

use app\models\Merchant;
use app\models\MerchantProfileForm;
use app\models\Sms;
use Yii;
use yii\web\Response;

/**
 * 商户信息管理
 * Class IdentityController
 * @package app\modules\merchant\controllers
 */
class IdentityController extends BaseController
{
    /**
     * 上传文件AJAX接口
     * @see UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 用户退出
     * @return Response
     */
    public function actionLogout()
    {
        $this->merchant->logout();
        Yii::$app->user->setReturnUrl(['/merchant']);
        return $this->redirect(['/merchant/login']);
    }

    /**
     * 用户设置
     * @return string
     */
    public function actionProfile()
    {
        $model = new MerchantProfileForm();
        /** @var Merchant $merchant */
        $merchant = $this->merchant->identity;
        $model->setAttributes($merchant->attributes, false);
        if ($model->load($this->post()) && $model->validate() && $model->save()) {
            Yii::$app->session->addFlash('success', '用户信息已保存。');
        }
        return $this->render('profile', [
            'model' => $model
        ]);
    }

    /**
     * 发送绑定手机短信验证码AJAX接口
     * @return array
     */
    public function actionSendProfileSmsCode()
    {
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return ['message' => '必须输入手机号码。'];
        }
        if ($mobile == $this->merchant->identity['mobile']) {
            return ['message' => '当前账号已绑定此号码。'];
        }
        if (Merchant::find()
            ->where(['mobile' => $mobile])
            ->andWhere(['<>', 'status', Merchant::STATUS_DELETED])
            ->exists()) {
            return ['message' => '此号码已绑定其它账号。'];
        }
        $r = Sms::sendCode(Sms::U_TYPE_MERCHANT, $this->merchant->id, $mobile, Sms::TYPE_BIND_MOBILE);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }

}
