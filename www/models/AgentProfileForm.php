<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 代理商 账号设置表单
 * Class AgentProfileForm
 * @package app\models
 */
class AgentProfileForm extends Model
{
    /**
     * @var integer 代理商编号
     */
    public $id;
    /**
     * @var string 登录邮箱账号
     */
    public $username;
    /**
     * @var string 用户头像
     */
    public $avatar;
    /**
     * @var string 设置的新密码明文
     */
    public $new_password;
    /**
     * @var string 重复输入的新密码明文
     */
    public $confirm_password;
    /**
     * @var string 手机号
     */
    public $mobile;
    /**
     * @var string 验证码
     */
    public $sms_code;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username', 'mobile'], 'unique', 'targetClass' => Agent::className(),
                'filter' => function ($query) {
                    /** @var $query \yii\db\Query */
                    $query
                        ->andWhere(['<>', 'id', $this->id])
                        ->andWhere(['<>', 'status', Agent::STATUS_DELETED]);
                    return $query;
                }
            ],
            ['mobile', function () {
                if (!preg_match("/^1[34578]{1}\d{9}$/", $this->mobile)) {
                    $this->addError('mobile', '请输入正确的手机号');
                }
            }],
            [['new_password', 'confirm_password'], function () {
                if ($this->new_password != $this->confirm_password) {
                    $this->addError('new_password', '两次输入密码不同。');
                }
            }],
            [['avatar', 'sms_code'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '登录邮箱账号',
            'new_password' => '新密码',
            'confirm_password' => '确认密码',
            'mobile' => '手机号',
            'sms_code' => '验证码',
            'avatar' => '用户头像',
        ];
    }

    /**
     * 保存账号设置
     * @return boolean
     */
    public function save()
    {
        $agent = Agent::findOne($this->id);
        $agent->username = $this->username;
        if (!empty($this->avatar)) {
            $agent->avatar = $this->avatar;
        }
        if (!empty($this->sms_code)) {
            $agent->mobile = $this->mobile;
        }
        if (!empty($this->new_password)) {
            try {
                $agent->password = Yii::$app->security->generatePasswordHash($this->new_password);
            } catch (Exception $e) {
                $this->addError('password', '无法对密码加密。');
                return false;
            }
        }
        if ($agent->save()) {
            return true;
        }
        $this->addErrors($agent->errors);
        return false;
    }
}
