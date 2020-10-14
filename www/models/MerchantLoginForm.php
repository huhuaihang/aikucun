<?php

namespace app\models;

use yii\base\Model;

/**
 * 商户登录表单
 * Class MerchantLoginForm
 * @package app\models
 */
class MerchantLoginForm extends Model
{
    /**
     * @var string 登录邮箱账号
     */
    public $username;
    /**
     * @var string 登录密码
     */
    public $password;
    /**
     * @var bool 是否记住登录状态
     */
    public $rememberMe = false;
    /**
     * @var Merchant
     */
    public $_merchant = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['username', 'email'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * 验证登录密码
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            if ($this->_merchant === false) {
                $this->_merchant = Merchant::find()
                    ->where(['username' => $this->username])
                    ->orderBy('create_time DESC')
                    ->one();
            }
            if (empty($this->_merchant) || $this->_merchant->status == Merchant::STATUS_DELETED) {
                $this->addError('username', '登录账号错误。');
                return;
            }
            if (!$this->_merchant->validatePassword($this->password)) {
                $this->addError('password', '登录账号或密码错误。');
                return;
            }
            if (in_array($this->_merchant->status, [Merchant::STATUS_REQUIRE, Merchant::STATUS_WAIT_DATA1])) {
                $this->addError('username', '您的账号正在等待客服初审，审核结果将通过站内消息或短信的方式通知到您。');
                return;
            }
            if ($this->_merchant->status == Merchant::STATUS_STOPED) {
                $this->addError('username', '此账号状态异常，请联系客服解决。');
                return;
            }
        }
    }

    /**
     * 登录操作
     * @return bool
     */
    public function login()
    {
        if ($this->validate()) {
            $new_auth_key = Util::randomStr(32, 7);
            Merchant::updateAll(['auth_key' => $new_auth_key], ['id' => $this->_merchant->id]);
            $this->_merchant->auth_key = $new_auth_key;
            return true;
        }
        return false;
    }

    /**
     * 返回登录的商户
     * @return Merchant
     */
    public function getMerchant()
    {
        return $this->_merchant;
    }
}
