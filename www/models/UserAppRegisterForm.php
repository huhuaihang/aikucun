<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户APP注册表单
 * Class UserRegisterForm
 * @package app\models
 */
class UserAppRegisterForm extends Model
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
     * @var string 团队邀请码
     */
    public $team_invite_code;
    /**
     * @var string open_id
     */
    public $open_id;
    /**
     * @var string 真实姓名
     */
    public $real_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'mobile', 'code'], 'required'],
            ['mobile', function() {
                if (User::find()->where(['mobile' => $this->mobile])->andWhere(['<>', 'status' , User::STATUS_DELETE])->one()) {
                    $this->addError('mobile', '该手机号已经注册，请直接登录 或者 激活');
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
            [['invite_code', 'open_id', 'real_name', 'team_invite_code'], 'safe'],
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
                $user->team_pid = $parent->id;
            }
        }
        if (!empty($this->team_invite_code)) {
            /** @var User $team_parent */
            $team_parent = User::find()->andWhere(['invite_code' => $this->team_invite_code])->one();
            if (empty($team_parent)) {
                $this->addError('team_invite_code', '团队邀请码错误。');
                return false;
            } else {
                $user->team_pid = $team_parent->id;
            }
        }
        $user->mobile = $this->mobile;
        $user->nickname = $this->nickname;
        $user->real_name = $this->real_name;
        $user->password = Yii::$app->security->generatePasswordHash($this->password);
        $user->create_time = time();
        $user->status = User::STATUS_WAIT;
        return $user->save();
    }
}
