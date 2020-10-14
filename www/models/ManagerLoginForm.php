<?php

namespace app\models;

use yii\base\Model;
use yii\web\User;

/**
 * 管理员登录表单
 * Class ManagerLoginForm
 * @package app\models
 */
class ManagerLoginForm extends Model
{
    /**
     * @var string 用户名
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
     * @var bool|Manager|User 登录用户信息
     */
    private $_manager = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
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
            if ($this->_manager === false) {
                $this->_manager = Manager::find()
                    ->where(['username' => $this->username, 'status' => [Manager::STATUS_ACTIVE, Manager::STATUS_STOPED]])
                    ->one();
                if (!empty($this->_manager) && $this->_manager->status == Manager::STATUS_STOPED) {
                    $this->addError('username', '此账号已停用。');
                }
            }
            if (!$this->_manager || !$this->_manager->validatePassword($this->password)) {
                $this->addError('password', '用户名或密码错误。');
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
            Manager::updateAll(['auth_key' => $new_auth_key], ['id' => $this->_manager->id]);
            $this->_manager->auth_key = $new_auth_key;
            return true;
        }
        return false;
    }

    /**
     * 获取登录管理员
     * @return Manager
     */
    public function getManager()
    {
        return $this->_manager;
    }
}
