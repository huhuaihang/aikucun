<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 商户申请表单
 * Class MerchantJoinForm
 * @package app\models
 */
class MerchantJoinForm extends Model
{
    /**
     * @var string 所在地区
     */
    public $area;
    /**
     * @var string 店铺名称
     */
    public $shop_name;
    /**
     * @var string 联系人姓名
     */
    public $contact_name;
    /**
     * @var string 联系电话
     */
    public $mobile;
    /**
     * @var string 短信验证码
     */
    public $sms_code;
    /**
     * @var string 登录邮箱账号
     */
    public $username;
    /**
     * @var string 登录密码
     */
    public $password;
    /**
     * @var string 代理商用户名
     */
    public $agent_username;
    /**
     * @var string 身份证正面
     */
    public $id_card_front;
    /**
     * @var string 身份证反面
     */
    public $id_card_back;
    /**
     * @var string 营业执照
     */
    public $business_license;
    /**
     * @var array 经营类目
     */
    public $cid_list;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['area', 'shop_name', 'contact_name', 'mobile', 'sms_code', 'username', 'password', 'agent_username', 'id_card_frnt', 'id_card_back', 'business_license', 'cid_list'], 'safe', 'on' => 'company_join'],
            [['area', 'shop_name', 'contact_name', 'mobile', 'sms_code', 'username', 'password', 'agent_username', 'id_card_frnt', 'id_card_back', 'cid_list'], 'safe', 'on' => 'person_join'],

            [['area', 'shop_name', 'contact_name', 'mobile', 'sms_code', 'username', 'password', 'id_card_front', 'id_card_back', 'cid_list'], 'required'],
            [['business_license'], 'required', 'on' => 'company_join'],
            [['area', 'contact_name', 'mobile', 'sms_code'], 'string', 'max' => 32],
            [['shop_name'], 'string', 'max' => 128],
            [['username'], 'string', 'max' => 256],
            ['username', 'email'],
            [['agent_username', 'cid_list'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area' => '所在地区',
            'shop_name' => '店铺名称',
            'contact_name' => '联系人姓名',
            'mobile' => '联系电话',
            'sms_code' => '短信验证码',
            'username' => '登录邮箱',
            'password' => '登录密码',
            'agent_username' => '代理商登录邮箱',
            'id_card_front' => '身份证正面',
            'id_card_back' => '身份证反面',
            'business_license' => '营业执照',
        ];
    }

    /**
     * 保存入驻信息
     * @param $user_id integer 用户编号
     * @return boolean
     */
    public function save($user_id)
    {
        if (!$this->validate()) {
            return false;
        }
        $result = $this->setSave($user_id);
        return $result;
    }

    /**
     * 保存入驻信息
     * @param $user_id int
     * @return boolean
     */
    public function setSave($user_id)
    {
        $exist_mid = UserConfig::getConfig($user_id, 'join_merchant');
        if (!empty($exist_mid)) {
            $merchant = Merchant::findOne($exist_mid);
            if ($merchant->status != Merchant::STATUS_REQUIRE) {
                $this->addError('username', '你之前的申请正在处理。');
                return false;
            }
        } else {
            $merchant = Merchant::find()
                ->andWhere(['username' => $this->username])
                ->andWhere(['<>', 'status', Merchant::STATUS_DELETED])
                ->one();
            if (!empty($merchant)) {
                $this->addError('username', '您输入的登录邮箱已被使用。');
                return false;
            }
        }
        $merchant = Merchant::find()
            ->andWhere(['mobile' => $this->mobile])
            ->andWhere(['<>', 'status', Merchant::STATUS_DELETED])
            ->andWhere(['<>', 'status', Merchant::STATUS_REQUIRE])
            ->one();
        if (!empty($merchant)) {
            $this->addError('mobile', '您输入的联系电话已被使用。');
            return false;
        }
        if (!Sms::checkCode($this->mobile, Sms::TYPE_MERCHANT_JOIN, $this->sms_code)) {
            $this->addError('sms_code', '验证码错误。');
            return false;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            if (!empty($exist_mid)) {
                $merchant = Merchant::findOne($exist_mid);
            } else {
                $merchant = new Merchant();
            }
            if (!empty($this->agent_username)) {
                /** @var Agent $agent */
                $agent = Agent::find()
                    ->andWhere(['username' => $this->agent_username])
                    ->andWhere(['<>', 'status', Agent::STATUS_DELETED])
                    ->one();
                if (empty($agent)) {
                    throw new Exception('您输入的代理商账号错误。');
                }
                $merchant->aid = $agent->id;
            }
            $merchant->type = Merchant::TYPE_ONLINE;
            $merchant->username = $this->username;
            $merchant->password = Yii::$app->security->generatePasswordHash($this->password);
            $merchant->mobile = $this->mobile;
            $merchant->contact_name = $this->contact_name;
            if ($this->scenario == 'person_join') {
                $merchant->is_person = 1;
            }
            $merchant->status = Merchant::STATUS_WAIT_DATA1;
            $merchant->create_time = time();
            $r = $merchant->save();
            if (!$r) {
                $errors = $merchant->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存商户信息：' . $error);
            }
            if (!empty($merchant->shop)) {
                $shop = $merchant->shop;
            } else {
                $shop = new Shop();
            }
            $shop->mid = $merchant->id;
            $shop->name = $this->shop_name;
            $shop->area = $this->area;
            $shop->status = Shop::STATUS_WAIT;
            $r = $shop->save();
            if (!$r) {
                $errors = $merchant->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存店铺信息：' . $error);
            }
            MerchantConfig::setConfig($merchant->id, 'id_card_front', $this->id_card_front);
            MerchantConfig::setConfig($merchant->id, 'id_card_back', $this->id_card_back);
            if ($this->scenario == 'company_join') {
                MerchantConfig::setConfig($merchant->id, 'business_license', $this->business_license);
            }
            MerchantConfig::setConfig($merchant->id, 'register_from_uid', $user_id);
            UserConfig::setConfig($user_id, 'join_merchant', $merchant->id);
            ShopConfig::setConfig($shop->id, 'cid_list', json_encode($this->cid_list));
            ShopConfig::setConfig($shop->id, 'logo', 'shop/shop_icon_default.png');
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            $this->addError('username', '保存信息时出现错误：' . $e->getMessage());
        }
        return false;
    }
}
