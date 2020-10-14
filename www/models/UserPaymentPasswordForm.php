<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户修改支付密码表单
 * Class UserPaymentPasswordForm
 * @package app\models
 */
class UserPaymentPasswordForm extends Model
{
    /**
     * @var int 用户id
     */
    public $uid;
    /**
     * @var string 验证码
     */
    public $code;
    /**
     * @var string 新密码
     */
    public $password;
    /**
     * @var string 确认密码
     */
    public $re_password;
    /**
     * @var string 客户端类型
     */
    public $client_type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['uid', 'required'],
            [['code', 'password', 're_password'], 'required'],
            ['password', 'compare', 'compareAttribute' => 're_password', 'message' => '两次密码不一致。'],
            ['client_type', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => '短信验证码',
            'password' => '支付密码',
            're_password' => '确认密码'
        ];
    }

    /**
     * 修改新密码
     * @return bool
     */
    public function setPaymentPassword()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = User::findOne($this->uid);
        if ($this->client_type == 'client') {
            if (!Sms::checkMobCode($user->mobile, $this->code)) {
                $this->addError('code', '验证码错误。');
                return false;
            }
        } else {
            if (!Sms::checkCode($user->mobile, Sms::TYPE_PAYMENT_PASSWORD, $this->code)) {
                $this->addError('code', '验证码错误。');
                return false;
            }
        }

        try {
            $user->payment_password = Yii::$app->security->generatePasswordHash($this->password);
        } catch (Exception $e) {
            $this->addError('payment_password', '无法加密密码。');
        }
        return $user->save();
    }
}
