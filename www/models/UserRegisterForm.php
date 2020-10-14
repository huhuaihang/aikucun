<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户注册表单
 * Class UserRegisterForm
 * @package app\models
 */
class UserRegisterForm extends Model
{
    /**
     * @var string 昵称
     */
    public $nickname;
    /**
     * @var string 明文密码
     */
    public $password;
    /**
     * @var string 手机号码
     */
    public $mobile;
    /**
     * @var string 短信验证码
     */
    public $code;
    /**
     * @var string 邀请码
     */
    public $invite_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'mobile', 'code'], 'required'],
            ['mobile', function() {
                if (User::find()->where(['mobile' => $this->mobile])->andWhere(['<>', 'status' , User::STATUS_DELETE])->one()) {
                    $this->addError('mobile', '该手机号已经注册，请直接登录');
                    return;
                }
            }],
            ['code', function () {
                if (!$this->hasErrors() && !Sms::checkMobCode($this->mobile, $this->code)) {
                    $this->addError('code', '验证码错误。');
                    return;
                }
            }, 'on' => 'client'],
            ['code', function () {
                if (!$this->hasErrors() && !Sms::checkCode($this->mobile, Sms::TYPE_REGISTER, $this->code)) {
                    $this->addError('code', '验证码错误。');
                    return;
                }
            }, 'on' => 'h5'],
            ['invite_code', 'safe'],
        ];
    }

    /**
     * 用户注册
     * @return bool
     * @throws Exception
     */
    public function register()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = new User();
        if (!empty($this->invite_code)) {
            /** @var User $parent */
            $parent = User::find()->andWhere(['invite_code' => $this->invite_code])->one();
            if (empty($parent)) {
                $this->addError('invite_code', '邀请码错误。');
                return false;
            } else {
                $user->pid = $parent->id;
            }
        }
        $user->mobile = $this->mobile;
        $user->password = Yii::$app->security->generatePasswordHash($this->password);
        $user->create_time = time();
        //$user->status = User::STATUS_OK;
        $user->status = User::STATUS_WAIT;
        return $user->save();
    }

    /**
     * 用户微信自动注册
     * @return bool
     * @throws Exception
     */
    public function wx_register()
    {
        if (!$this->validate()) {
            return false;
        }
        $user = new User();
        if (!empty($this->invite_code)) {
            /** @var User $parent */
            $parent = User::find()->andWhere(['invite_code' => $this->invite_code])->one();
            if (empty($parent)) {
                $this->addError('invite_code', '邀请码错误。');
                return false;
            } else {
                $user->pid = $parent->id;
            }
        }
        $user->mobile = $this->mobile;
        $user->password = Yii::$app->security->generatePasswordHash($this->password);
        $user->create_time = time();
        $user->status = User::STATUS_WAIT;
        return $user->save();
    }
}
