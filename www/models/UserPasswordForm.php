<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户修改密码表单
 * Class UserPasswordForm
 * @package app\models
 */
class UserPasswordForm extends Model
{
    /**
     * @var int 用户编号
     */
    public $uid;
    /**
     * @var string 验证码
     */
    public $code;
    /**
     * @var string 原密码
     */
    public $old_password;
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
            [['password', 're_password'], 'required'],
            ['password', 'compare', 'compareAttribute' => 're_password', 'message' => '两次密码不一致。'],
            [['code', 'old_password', 'client_type'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => '短信验证码',
            'old_password' => '原密码',
            'password' => '新密码',
            're_password' => '确认密码'
        ];
    }

    /**
     * 保存新密码
     * @return bool
     */
    public function savePassword()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = User::findOne($this->uid);
        if (!$user->validatePassword($this->old_password)) {
            $this->addError('old_password', '原密码不对。');
            return false;
        }
        try {
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
        } catch (Exception $e) {
            $this->addError('password', '无法加密密码。');
        }
        return $user->save();
    }

    /**
     * 设置新密码
     * @return bool
     */
    public function setPassword()
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
            if (!Sms::checkCode($user->mobile, Sms::TYPE_FORGOT_PASSWORD, $this->code)) {
                $this->addError('code', '验证码错误。');
                return false;
            }
        }
        try {
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
        } catch (Exception $e) {
            $this->addError('password', '无法加密密码。');
        }
        return $user->save();
    }
}
