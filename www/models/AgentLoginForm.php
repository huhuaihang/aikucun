<?php

namespace app\models;

use yii\base\Model;
use yii\web\User;

/**
 * 代理商登录表单
 * Class AgentLoginForm
 * @package app\models
 */
class AgentLoginForm extends Model
{
    /**
     * @var string 用户邮箱账号
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
     * @var bool|Agent|User 登录用户信息
     */
    private $_agent = false;

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
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '登录邮箱账号',
            'password' => '登录密码',
        ];
    }

    /**
     * 验证登录密码
     */
    public function validatePassword()
    {
        if (!$this->hasErrors()) {
            if ($this->_agent === false) {
                $this->_agent = Agent::find()
                    ->where(['username' => $this->username, 'status' => [Agent::STATUS_ACTIVE, Agent::STATUS_STOPED]])
                    ->one();
                if (!empty($this->_agent) && $this->_agent->status == Agent::STATUS_STOPED) {
                    $this->addError('username', '此账号已停用。');
                }
            }
            if (!$this->_agent || !$this->_agent->validatePassword($this->password)) {
                $this->addError('password', '账号或密码错误。');
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
                Agent::updateAll(['auth_key' => $new_auth_key], ['id' => $this->_agent->id]);
                $this->_agent->auth_key = $new_auth_key;
            }
            return true;
        }
        return false;
    }

    /**
     * 返回当前登录的代理
     * @return Agent
     */
    public function getAgent()
    {
        return $this->_agent;
    }
}
