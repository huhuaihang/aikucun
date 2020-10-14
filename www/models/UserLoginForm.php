<?php

namespace app\models;

use yii\base\Model;

/**
 * 用户登录表单
 * Class UserLoginForm
 * @package app\models
 */
class UserLoginForm extends Model
{
    /**
     * @var string 登录账号
     */
    public $mobile;
    /**
     * @var string 登录密码
     */
    public $password;
    /**
     * @var string 极光编号
     */
    public $jpush_id;
    /**
     * @var User 登录账号
     */
    public $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'password'], 'required'],
            ['password', 'validatePassword'],
            ['jpush_id', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => '登录账号',
            'password' => '登录密码',
        ];
    }

    /**
     * 验证登录密码
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            $this->_user = User::find()->andWhere(['mobile' => $this->mobile, 'status' => [User::STATUS_OK,User::STATUS_WAIT, User::STATUS_STOP]])->one();
            if (empty($this->_user)) {
                $this->addError('password', '没有找到用户。');
                return;
            }
            if (empty($this->_user->password)) {
                $this->addError('password', '没有设置密码。');
                return;
            }
            if (!$this->_user->validatePassword($this->password)) {
                $this->addError('password', '密码错误。');
                return;
            }
        }
    }

    /**
     * 登录操作
     * @param $update_auth_key boolean 是否更新auth_key
     * @return bool
     */
    public function login($update_auth_key = true)
    {
        if ($this->validate()) {
            if ($update_auth_key) {
                $new_auth_key = Util::randomStr(32, 7);
                User::updateAll(['auth_key' => $new_auth_key], ['id' => $this->_user->id]);
                $this->_user->auth_key = $new_auth_key;
            }
            if ($this->jpush_id) {
                User::updateAll(['jpush_id' => $this->jpush_id], ['id' => $this->_user->id]);
            }
            return true;
        }
        return false;
    }

    /**
     * 返回当前登录的用户
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}
