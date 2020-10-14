<?php

namespace app\models;

use yii\base\Model;
use yii\web\User;

/**
 * 供货商登录表单
 * Class SupplierLoginForm
 * @package app\models
 */
class SupplierLoginForm extends Model
{
    /**
     * @var string 手机号
     */
    public $mobile;
    /**
     * @var string 登录密码
     */
    public $password;
    /**
     * @var bool 是否记住登录状态
     */
    public $rememberMe = false;
    /**
     * @var bool|Supplier|User 登录用户信息
     */
    private $_supplier = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'password'], 'required'],
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
            if ($this->_supplier === false) {
                $this->_supplier = Supplier::find()
                    ->where(['mobile' => $this->mobile, 'status' => [Supplier::STATUS_OK, Supplier::STATUS_STOP]])
                    ->one();
                if (!empty($this->_supplier) && $this->_supplier->status == Supplier::STATUS_STOP) {
                    $this->addError('mobile', '此账号已停用。');
                }
            }
            if (!$this->_supplier || !$this->_supplier->validatePassword($this->password)) {
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
            Supplier::updateAll(['auth_key' => $new_auth_key], ['id' => $this->_supplier->id]);
            $this->_supplier->auth_key = $new_auth_key;
            return true;
        }
        return false;
    }

    /**
     * 获取登录管理员
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->_supplier;
    }
}
