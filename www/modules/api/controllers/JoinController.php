<?php

namespace app\modules\api\controllers;

use app\models\GoodsCategory;
use app\models\Merchant;
use app\models\MerchantJoinForm;
use app\models\Sms;
use app\models\SystemVersion;
use app\models\UserConfig;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;

/**
 * 商家入驻申请
 * Class JoinController
 * @package app\modules\api\controllers
 */
class JoinController extends BaseController
{
    /**
     * 保存商户入驻请求AJAX接口
     * @return array
     */
    public function actionSaveMerchant()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson();
        if (isset($json['error_code'])) {
            return $json;
        }
        if (!isset($json['is_person'])
            || empty($json['area'])
            || empty($json['shop_name'])
            || empty($json['contact_name'])
            || empty($json['mobile'])
            || empty($json['sms_code'])
            || empty($json['username'])
            || empty($json['password'])
            || empty($json['id_card_front'])
            || empty($json['id_card_back'])
            || empty($json['cid_list']
            || (
                $json['is_person'] == 0 && empty($json['business_license'])
            ))
        ) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数必填。',
            ];
        }
        $model = new MerchantJoinForm(['scenario' => ($json['is_person'] == 1 ? 'person_join' : 'company_join')]);
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model->setAttributes($json);
        if (!$model->setSave($user->id)) {
            $errors = $model->errors;
            $error = '';
            foreach ($errors as $attr => $_errors) {
                $error = $_errors[0];
                break;
            }
            return [
                'error_code' => ErrorCode::MERCHANT_SAVE_FAIL,
                'message' => '申请入驻资料保存失败：' . $error,
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 发送商户入驻短信验证码
     * get
     * {
     *      mobile 手机号
     * }
     */
    public function actionSendSmsCodeMerchant()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, $user->id, $mobile, Sms::TYPE_MERCHANT_JOIN);
        if ($r !== true) {
            return [
                'error_code' => ErrorCode::USER_SEND_SMS,
                'message' => $r,
            ];
        }
        return [];
    }

    /**
     * 获取 一级 二级商品分类
     */
    public function actionGetCategory()
    {
        $pid = $this->get('pid');
        if (!empty($pid)) {
            $cate_list = GoodsCategory::find()->select(['id', 'name'])->andWhere(['pid' => $pid])->all();
        } else {
            $cate_list = GoodsCategory::find()->select(['id', 'name'])->andWhere(['pid' => null])->all();
        }
        return ['cate_list' => $cate_list];
    }

    /**
     * 获取入驻状态
     */
    public function actionGetStatus()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        /** @var UserConfig $user_config */
        $user_config = UserConfig::find()->where(['uid' => $user->id])->one();
        $status = 0;
        if (empty($user_config)) {
            $status = 0;
        } else {
            $merchant = Merchant::findOne($user_config->v);
            if (!empty($merchant)) {
                $status = $merchant->status;
            }
        }
        $manager_url = '';
        if ($status == Merchant::STATUS_DATA1_OK || $status == Merchant::STATUS_COMPLETE || $status == Merchant::STATUS_DATA2_OK) {
            $manager_url = Yii::$app->params['site_host'] . '/merchant';
        }
        return [
            'status' => $status,
            'is_person' => empty($merchant->is_person) ? 0 : 1,
            'manager_url' => $manager_url,
        ];
    }
}