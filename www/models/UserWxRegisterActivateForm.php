<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户微信激活注册表单
 * Class UserRegisterForm
 * @package app\models
 */
class UserWxRegisterActivateForm extends Model
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
     * @var string open_id
     */
    public $open_id;
    /**
     * @var string 真是姓名
     */
    public $real_name;
    /**
     * @var string union_id
     */
    public $union_id;
    /**
     * @var string 头像
     */
    public $avatar;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['password', 'mobile', 'code'], 'required'],
            ['mobile', function() {
                if (!User::find()->where(['mobile' => $this->mobile])->andWhere(['<>', 'status' , User::STATUS_DELETE])->one()) {
                    $this->addError('mobile', '该手机号未注册会员，请去注册');
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
            [['invite_code', 'open_id', 'real_name', 'union_id', 'nickname', 'avatar'], 'safe'],
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
        $user->status = User::STATUS_OK;
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
        $app_id = System::getConfig('weixin_mp_app_id');
        //  此处先插入代码  如果已有账号  直接激活 不需要注册进入会员表
        /** @var User $old_user */
        $old_user = User::find()->where(['mobile' => $this->mobile])->one();
        /** @var UserWeixin $old_wx_user */
        $old_wx_user = UserWeixin::find()->where(['open_id' => $this->open_id])->one();

        if (!empty($old_user) && !empty($old_wx_user)) {
            //$old_user->status = User::STATUS_OK;
            $old_user->password = Yii::$app->security->generatePasswordHash($this->password);
            if (!$old_user->save()){
                $errors = $old_user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            return true;
        }
        if (!empty($old_user) && empty($old_wx_user)) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $new_wx_user = new UserWeixin();
                $new_wx_user->open_id = $this->open_id;
                $new_wx_user->union_id = $this->union_id;
                $new_wx_user->uid = $old_user->id;
                $new_wx_user->create_time = time();
                $new_wx_user->app_id = $app_id;
                if (!$new_wx_user->save()){
                    $errors = $new_wx_user->errors;
                    $error = array_shift($errors)[0];
                    Yii::warning($errors);
                    throw new Exception('无法保存用户信息：' . $error);
                }
                if (empty($old_user->avatar)) {
                    $old_user->avatar = $this->avatar;
                }
                if (empty($old_user->nickname)) {
                    $old_user->avatar = $this->nickname;
                }
                $old_user->status = User::STATUS_OK;
                $old_user->password = Yii::$app->security->generatePasswordHash($this->password);

//                if(!$old_user->subsidy($old_user)){
//                    //throw new Exception('激活失败，发放补贴失败');
//                }
                if (!$old_user->save()){
                    $errors = $old_user->errors;
                    $error = array_shift($errors)[0];
                    Yii::warning($errors);
                    throw new Exception('无法保存用户信息：' . $error);
                }

                $trans->commit();
                return true;
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                    $this->addError('mobile', $e->getMessage());
                    return false;
                }
            }
            return true;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
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
            $user->real_name = $this->real_name;
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
            $user->create_time = time();
            $user->status = User::STATUS_WAIT;
            if (empty($user->avatar)) {
                $user->avatar = $this->avatar;
            }
            if (empty($user->nickname)) {
                $user->avatar = $this->nickname;
            }
            if (!$user->save()){
                $errors = $user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            /** @var WeixinMpApi $api */
            $wx_user = new UserWeixin();
            $wx_user->app_id = $app_id;
            $wx_user->uid = $user->id;
            $wx_user->open_id = $this->open_id;
            $wx_user->union_id = $this->union_id;
            $wx_user->create_time = time();
            if (!$wx_user->save()){
                $errors = $wx_user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
                $this->addError('mobile', $e->getMessage());
                return false;
            }

        }
        return false;
    }
}
