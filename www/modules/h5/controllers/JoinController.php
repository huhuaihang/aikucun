<?php

namespace app\modules\h5\controllers;

use app\models\Agent;
use app\models\AgentConfig;
use app\models\AgentFee;
use app\models\AgentJoinForm;
use app\models\Merchant;
use app\models\MerchantConfig;
use app\models\MerchantJoinForm;
use app\models\Sms;
use app\models\UserConfig;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * 商家入驻
 * Class JoinController
 * @package app\modules\h5\controllers
 */
class JoinController extends BaseController
{
    /**
     * 文件上传AJAX接口
     * @see \app\modules\h5\controllers\UploadControllerTrait
     */
    use UploadControllerTrait;

    /**
     * 商家入驻合作首页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 阅读协议页面
     * @throws BadRequestHttpException
     * @return string
     */
    public function actionAgreement()
    {
        $type = $this->get('type');
        if (empty($type)) {
            throw new BadRequestHttpException('参数错误。');
        }
        $merchant_id = UserConfig::getConfig(Yii::$app->user->id, 'join_merchant', 0);
        $agent_id = UserConfig::getConfig(Yii::$app->user->id, 'join_agent', 0);
        if (!empty($merchant_id) && ($type == 'merchant-person' || $type == 'merchant')) {
            if ($type == 'merchant') {
                return $this->redirect(['/h5/join/merchant']);
            } else if ($type == 'merchant-person') {
                return $this->redirect(['/h5/join/merchant-person']);
            }
        }
        if (!empty($agent_id) && $type == 'agent') {
            return $this->redirect(['/h5/join/agent']);
        }

        if ($type == 'merchant-person' || $type == 'merchant') {
            return $this->render('merchant_agreement', [
                'type' => $type
            ]);
        } else {
            return $this->render('agent_agreement');
        }
    }

    /**
     * 个人商户入驻申请
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionMerchantPerson()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $model = new MerchantJoinForm(['scenario' => 'person_join']);
        $merchant_id = UserConfig::getConfig(Yii::$app->user->id, 'join_merchant', 0);
        if (!empty($merchant_id)) {
            $merchant = Merchant::findOne($merchant_id);
            if ($merchant->status == Merchant::STATUS_REQUIRE) {
                $model->area = $merchant->shop->area;
                $model->shop_name = $merchant->shop->name;
                $model->contact_name = $merchant->contact_name;
                $model->mobile = $merchant->mobile;
                $model->username = $merchant->username;
                if (!empty($merchant->agent)) {
                    $model->agent_username = $merchant->agent->username;
                }
                $model->id_card_front = MerchantConfig::getConfig($merchant->id, 'id_card_front');
                $model->id_card_back = MerchantConfig::getConfig($merchant->id, 'id_card_back');
            } elseif ($merchant->status == Merchant::STATUS_WAIT_DATA1 || $merchant->status == Merchant::STATUS_WAIT_DATA2) {
                return $this->render('merchant_join_wait');
            } elseif (in_array($merchant->status, [
                Merchant::STATUS_DATA1_OK,
                Merchant::STATUS_DATA2_OK,
                Merchant::STATUS_COMPLETE
            ])) {
                return $this->render('merchant_joined');
            } elseif ($merchant->status == Merchant::STATUS_STOPED) {
                return $this->render('merchant_join_stop');
            }
        }
        return $this->render('merchant_person', [
            'model' => $model,
        ]);
    }

    /**
     * 商户入驻申请
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionMerchant()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $model = new MerchantJoinForm(['scenario' => 'company_join']);
        $merchant_id = UserConfig::getConfig(Yii::$app->user->id, 'join_merchant', 0);
        if (!empty($merchant_id)) {
            $merchant = Merchant::findOne($merchant_id);
            if ($merchant->status == Merchant::STATUS_REQUIRE) {
                $model->area = $merchant->shop->area;
                $model->shop_name = $merchant->shop->name;
                $model->contact_name = $merchant->contact_name;
                $model->mobile = $merchant->mobile;
                $model->username = $merchant->username;
                if (!empty($merchant->agent)) {
                    $model->agent_username = $merchant->agent->username;
                }
                $model->id_card_front = MerchantConfig::getConfig($merchant->id, 'id_card_front');
                $model->id_card_back = MerchantConfig::getConfig($merchant->id, 'id_card_back');
                $model->business_license = MerchantConfig::getConfig($merchant->id, 'business_license');
            } elseif ($merchant->status == Merchant::STATUS_WAIT_DATA1) {
                return $this->render('merchant_join_wait');
            } elseif (in_array($merchant->status, [
                Merchant::STATUS_DATA1_OK,
                Merchant::STATUS_WAIT_DATA2,
                Merchant::STATUS_DATA2_OK,
                Merchant::STATUS_COMPLETE,
                Merchant::STATUS_STOPED
            ])) {
                return $this->render('merchant_joined');
            }
        }
        return $this->render('merchant', [
            'model' => $model,
        ]);
    }

    /**
     * 发送商户短信验证码AJAX接口
     * @return array
     */
    public function actionSendSmsCodeMerchant()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '没有登录。'];
        }
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return ['message' => '参数错误。'];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, Yii::$app->user->id, $mobile, Sms::TYPE_MERCHANT_JOIN);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }

    /**
     * 代理商入驻申请
     * @return string
     * @throws ServerErrorHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionAgent()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginRequired();
        }
        $model = new AgentJoinForm();
        $agent_id = UserConfig::getConfig(Yii::$app->user->id, 'join_agent', 0);
        if (!empty($agent_id)) {
            $agent = Agent::findOne($agent_id);
            if ($agent->status == Agent::STATUS_REQUIRE) {
                $model->area = $agent->area;
                $model->contact_name = $agent->contact_name;
                $model->mobile = $agent->mobile;
                $model->username = $agent->username;
                $model->id_card_front = AgentConfig::getConfig($agent->id, 'id_card_front');
                $model->id_card_back = AgentConfig::getConfig($agent->id, 'id_card_back');
            } elseif (in_array($agent->status, [Agent::STATUS_WAIT_CONTACT, Agent::STATUS_WAIT_INITIAL_FEE, Agent::STATUS_WAIT_FINANCE])) {
                return $this->render('agent_join_wait');
            } elseif ($agent->status == Agent::STATUS_WAIT_EARNEST_MONEY) {
                /** @var AgentFee $agent_fee */
                $agent_fee = AgentFee::find()->andWhere(['area' => $agent->area])->one();
                if (empty($agent_fee)) {
                    throw new ServerErrorHttpException('没有找到您所在的地区的保证金设置，请联系客服解决此问题。');
                }
                // TODO：开通代理商入驻时再添加
                return null;
            } elseif ($agent->status == Agent::STATUS_ACTIVE) {
                return $this->render('agent_joined');
            }
        }
        return $this->render('agent', [
            'model' => $model,
        ]);
    }

    /**
     * 保存代理商入驻请求AJAX接口
     * @return array
     */
    public function actionSaveAgent()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '没有登录。'];
        }
        $model = new AgentJoinForm();
        if ($model->load($this->post()) && $model->save(Yii::$app->user->id)) {
            return ['result' => 'success'];
        }
        $errors = $model->errors;
        $error = array_shift($errors)[0];
        return ['message' => $error, 'errors' => $errors];
    }

    /**
     * 发送代理商短信验证码AJAX接口
     * @return array
     */
    public function actionSendSmsCodeAgent()
    {
        if (Yii::$app->user->isGuest) {
            return ['message' => '没有登录。'];
        }
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return ['message' => '参数错误。'];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, Yii::$app->user->id, $mobile, Sms::TYPE_AGENT_JOIN);
        if ($r !== true) {
            return ['message' => $r];
        }
        return ['result' => 'success'];
    }
}
