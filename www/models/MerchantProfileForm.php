<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 商户账号设置表单
 * Class MerchantProfileForm
 * @package app\models
 */
class MerchantProfileForm extends Model
{
    /**
     * @var integer 商户编号
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
     * @var string 登录用户手机号码
     */
    public $mobile;
    /**
     * @var string 验证码
     */
    public $sms_code;
    /**
     * @var string 老密码明文
     */
    public $password;
    /**
     * @var string 设置的新密码明文
     */
    public $new_password;
    /**
     * @var string 重复输入的新密码明文
     */
    public $confirm_password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'required'],
            [['username'], 'unique', 'targetClass' => Merchant::className(),
                'filter' => function ($query) {
                    /** @var $query \yii\db\Query */
                    $query
                        ->andWhere(['<>', 'id', $this->id])
                        ->andWhere(['<>', 'status', Merchant::STATUS_DELETED]);
                    return $query;
                }
            ],
            [['mobile'], 'required'],
            [['mobile'], 'match', 'pattern'=>'/^[1][34578][0-9]{9}$/', 'message'=>'输入正确手机号码'],
            [['new_password', 'confirm_password'], function () {
                if ($this->new_password != $this->confirm_password) {
                    $this->addError('new_password', '两次输入密码不同。');
                }
            }],
            [['sms_code' ,'avatar', 'password'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => '登录邮箱账号',
            'avatar' => '头像',
            'mobile' => '手机号码',
            'password' => '原密码密码',
            'new_password' => '新密码',
            'confirm_password' => '确认密码',
        ];
    }

    /**
     * 保存账号设置
     * @return boolean
     */
    public function save()
    {
        $merchant = Merchant::findOne($this->id);
        $merchant->username = $this->username;
        $merchant->avatar = $this->avatar;
        if (!empty($this->new_password)) {
            $this->sms_code = '';
            if (!empty($this->password) && !$merchant->validatePassword($this->password)) {
                $this->addError('password', '原密码输入错误');
                return false;
            }
            try {
                $merchant->password = Yii::$app->security->generatePasswordHash($this->new_password);
            } catch (Exception $e) {
                $this->addError('new_password', '无法加密密码。');
                return false;
            }
        }
        if(!empty($this->mobile) && !empty($this->sms_code)) {
            $merchant->mobile = $this->mobile;
            if (!Sms::checkCode($this->mobile, Sms::TYPE_BIND_MOBILE, $this->sms_code)) {
                $this->addError('sms_code', '短信验证码错误。');
                return false;
            }
        }

        if ($merchant->save()) {
            // 更新Session
            Yii::$app->merchant->setIdentity($merchant);
            return true;
        }
        $this->addErrors($merchant->errors);
        return false;
    }
}
