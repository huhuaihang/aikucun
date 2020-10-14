<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 用户微信注册表单
 * Class UserRegisterForm
 * @package app\models
 */
class UserWxRegisterForm extends Model
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
     * @var string 头像
     */
    public $avatar;
    /**
     * @var string union_id
     */
    public $union_id;

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
            [['invite_code', 'open_id', 'real_name', 'team_invite_code', 'nickname', 'avatar', 'union_id'], 'safe'],
            [['invite_code', 'team_invite_code'], 'trim'],
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
        $user->mobile = $this->mobile;
        $user->password = Yii::$app->security->generatePasswordHash($this->password);
        $user->create_time = time();
        $user->status = User::STATUS_OK;
        $user_message = new UserMessage();
        $user_message->MessageSend($user->pid,'有新人与您绑定关系,赶紧去邀请TA激活吧!','/h5/user/team-list?status=2','新人绑定关系通知');
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
//            $old_user->status = User::STATUS_OK;
//            $old_user->password = Yii::$app->security->generatePasswordHash($this->password);
//            if(!$old_user->subsidy($old_user)){
//                throw new Exception('激活失败，发放补贴失败');
//            }
//            if (!$old_user->save()){
//                $errors = $old_user->errors;
//                $error = array_shift($errors)[0];
//                throw new Exception('无法保存用户信息：' . $error);
//            }
//            return true;
        }
        if (!empty($old_user) && empty($old_wx_user)) {
            $trans = Yii::$app->db->beginTransaction();
            try {
                $new_wx_user = new UserWeixin();
                $new_wx_user->open_id = $this->open_id;
                $new_wx_user->uid = $old_user->id;
                $new_wx_user->create_time = time();
                $new_wx_user->app_id = $app_id;
                if (!$new_wx_user->save()){
                    $errors = $old_user->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception('无法保存用户信息：' . $error);
                }
                $old_user->status = User::STATUS_WAIT;
                $old_user->password = Yii::$app->security->generatePasswordHash($this->password);
                if (!$old_user->save()){
                    $errors = $old_user->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception('无法保存用户信息：' . $error);
                }

                $trans->commit();
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
            $user->real_name = $this->real_name;
            $user->nickname = $this->nickname;
            $user->avatar = $this->avatar;
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
            $user->create_time = time();
            $user->status = User::STATUS_WAIT;
            $user->level_id = 1;
            if (!$user->save()) {
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
            if (!$wx_user->save()) {
                $errors = $wx_user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            /** 新成员发送上级消息通知 */
            $user_message = new UserMessage();
            $user_message->MessageSend($user->pid, '有新人与您绑定关系,赶紧去邀请TA激活吧!', '/h5/user/team-list?status=2', '新人绑定关系通知');
            $num=System::getConfig('active_user_register_num');
            if(empty($num) || !preg_match("/^[1-9][0-9]*$/" , $num))
            {
              $num=30;
            }
            //获取上级邀请的人数 满30 如果上级是非激活状态则给予激活
            $p_user = $user->parent;//获取上级用户
            if ($p_user->status == User::STATUS_WAIT) {
                /** @var $invite_user_list array 邀请所有用户 */
                $invite_user_list = $p_user->childList;
                if (count($invite_user_list) >= $num-1) {
                    $p_user->status = User::STATUS_OK;
                    $p_user->handle_time = time();
                    $p_user->is_invite_active = 1;
                    if ($p_user->save()) {
                        /** 激活新会员发送给用户自身消息通知 */
                        $active_user_message = System::getConfig('active_user_message');
                        $id = $user_message->MessageSend($p_user->id, '恭喜您，您成功邀请了'.$num.'位会员用户，已免费帮您激活了会员身份，快去查看吧！', Yii::$app->params['site_host'] . '/h5/notice/umview', $active_user_message);
                        if ($id) {
                            $message = UserMessage::findOne($id);
                            $message->url = Yii::$app->params['site_host'] . '/h5/notice/umview?id=' . $id . '&app=1';
                            if (!$message->save(false)) {
                                Yii::warning('更新激活通知记录失败。');
                            }
                        }
                    }
                }
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
