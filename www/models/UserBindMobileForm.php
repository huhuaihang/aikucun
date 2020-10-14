<?php

namespace app\models;

use yii\base\Model;

/**
 * 用户绑定新手机号码表单
 * Class UserBindMobileForm
 * @package app\models
 */
class UserBindMobileForm extends Model
{
    /**
     * @var int 用户编号
     */
    public $uid;
    /**
     * @var string 手机号码
     */
    public $mobile;
    /**
     * @var string 短信验证码
     */
    public $code;
    /**
     * @var string 密码
     */
    public $password;
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
            [['mobile', 'code', 'password'], 'required'],
            ['mobile', function() {
                if (User::find()->where(['mobile' => $this->mobile])->andWhere(['<>', 'status' , User::STATUS_DELETE])->one()) {
                    $this->addError('mobile', '该手机号已经注册，请更换手机号。');
                    return;
                }
            }],
            [['client_type'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'mobile' => '手机号码',
            'code' => '短信验证码',
            'password' => '密码',
        ];
    }

    /**
     * 绑定新手机号码
     * @return bool
     */
    public function bindMobile()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = User::findOne($this->uid);
        if (!$user->validatePassword($this->password)) {
            $this->addError('password', '当前登录密码不对。');
            return false;
        }
        if ($this->client_type == 'client') {
            if (!Sms::checkMobCode($this->mobile, $this->code)) {
                $this->addError('code', '验证码错误。');
                return false;
            }
        } else {
            if (!Sms::checkCode($this->mobile, Sms::TYPE_BIND_MOBILE, $this->code)) {
                $this->addError('code', '验证码错误。');
                return false;
            }
        }
        $user->mobile = $this->mobile;
        return $user->save();
    }
}
