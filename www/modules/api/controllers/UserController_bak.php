<?php

namespace app\modules\api\controllers;

use app\models\AllInPayAliApi;
use app\models\City;
use app\models\Feedback;
use app\models\FinanceLog;
use app\models\Goods;
use app\models\GoodsCategory;
use app\models\IpCity;
use app\models\KeyMap;
use app\models\Order;
use app\models\OrderItem;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\ShopScore;
use app\models\System;
use app\models\SystemVersion;
use app\models\User;
use app\models\Sms;
use app\models\UserAccountLog;
use app\models\UserAddress;
use app\models\UserAppRegisterForm;
use app\models\UserBank;
use app\models\UserBindMobileForm;
use app\models\UserCommission;
use app\models\UserFavGoods;
use app\models\UserFavShop;
use app\models\UserLevel;
use app\models\UserLoginForm;
use app\models\UserMessage;
use app\models\UserPasswordForm;
use app\models\UserPaymentPasswordForm;
use app\models\UserRecharge;
use app\models\UserRecommend;
use app\models\UserRegisterForm;
use app\models\UserSearchHistory;
use app\models\UserSubsidy;
use app\models\UserWeixin;
use app\models\UserWithdraw;
use app\models\UserWxRegisterActivateForm;
use app\models\UserWxRegisterForm;
use app\models\Util;
use app\models\WeixinAppApi;
use app\models\WeixinH5Api;
use app\models\WeixinMpApi;
use app\models\WithdrawBank;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;

/**
 * 用户处理
 * Class UserController
 * @package app\modules\api\controllers
 */
class UserController_bak extends BaseController
{
    /**
     * 发送注册验证码
     * POST
     * {
     *     mobile 手机号码
     * }
     */
    public function actionSendRegisterSmsCode()
    {
        $json = $this->checkJson([
            [['mobile'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        $mobile = $json['mobile'];
        if (empty($mobile)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '手机号码错误。',
            ];
        }
        $user = User::find()
            ->andWhere(['mobile' => $mobile, 'status' => [User::STATUS_OK, User::STATUS_STOP]])
            ->one();
        if (!empty($user)) {
            return [
                'error_code' => ErrorCode::USER_MOBILE_EXIST,
                'message' => '手机号码已被注册。',
            ];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, null, $mobile, Sms::TYPE_REGISTER);
        if ($r !== true) {
            return [
                'error_code' => ErrorCode::USER_SEND_SMS,
                'message' => $r
            ];
        }
        return [];
    }

    /**
     * 用户注册
     * POST
     * {
     *     nickname, 昵称
     *     password, 密码
     *     mobile,   手机号码
     *     code,      短信验证码
     *     invite_code 邀请码
     * }
     */
    public function actionRegister()
    {
        $json = $this->checkJson([
            [['password', 'mobile', 'code', 'invite_code'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        $model = new UserRegisterForm(['scenario' => $json['client_type'] == 'client' ? 'client' : 'h5']);
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model->setAttributes($json);
        try {
            if (!$model->register()) {
                return [
                    'error_code' => ErrorCode::USER_REGISTER,
                    'message' => '用户注册失败。',
                    'errors' => $model->errors,
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::USER_REGISTER,
                'message' => '用户注册失败：' . $e->getMessage(),
            ];
        }
        return $this->actionLogin();
    }

    /**
     * 用户自动注册
     * POST
     * {
     *     nickname, 昵称
     *     password, 密码
     *     mobile,   手机号码
     *     code,      短信验证码
     *     invite_code 邀请码
     * }
     */
    public function actionWxRegister()
    {
        $json = $this->checkJson([
            [['real_name', 'password', 'mobile', 'code', 'invite_code', 'open_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $json['save_session'] = 1;

        //  此处先插入代码  如果已有账号  直接激活 不需要注册进入会员表
        /** @var User $old_user */
        $old_user = User::find()->where(['mobile' => $json['mobile']])->one();
        /** @var UserWeixin $old_wx_user */
        $old_wx_user = UserWeixin::find()->where(['open_id' => $json['open_id']])->one();

        if (!empty($old_user) && !empty($old_wx_user)) {
            //$old_user->status = User::STATUS_OK;
            $old_user->password = Yii::$app->security->generatePasswordHash($json['password']);
            if (!$old_user->save()){
                $errors = $old_user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            return $this->actionLogin();
        }

        $model = new UserWxRegisterForm(['scenario' => $json['client_type'] == 'client' ? 'client' : 'h5']);

        $model->setAttributes($json);
        try {
            if (!$model->wx_register()) {
                return [
                    'error_code' => ErrorCode::USER_REGISTER,
                    'message' => '用户注册失败。',
                    'errors' => $model->errors,
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::USER_REGISTER,
                'message' => '用户注册失败：' . $e->getMessage(),
            ];
        }
        return $this->actionLogin();
    }

    /**
     * 用户自动注册
     * POST
     * {
     *     nickname, 昵称
     *     password, 密码
     *     mobile,   手机号码
     *     code,      短信验证码
     *     real_name, 真是姓名
     * }
     */
    public function actionWxRegisterActivate()
    {
        $json = $this->checkJson([
            [['real_name', 'password', 'mobile', 'code', 'open_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }

        $json['save_session'] = 1;

        //  此处先插入代码  如果已有账号  直接激活 不需要注册进入会员表
        /** @var User $old_user */
        $old_user = User::find()->where(['mobile' => $json['mobile']])->one();
        /** @var UserWeixin $old_wx_user */
        $old_wx_user = UserWeixin::find()->where(['open_id' => $json['open_id']])->one();

        if (!empty($old_user) && !empty($old_wx_user)) {
            //$old_user->status = User::STATUS_OK;
            $old_user->password = Yii::$app->security->generatePasswordHash($json['password']);
            if (!$old_user->save()){
                $errors = $old_user->errors;
                $error = array_shift($errors)[0];
                throw new Exception('无法保存用户信息：' . $error);
            }
            return $this->actionAutoLogin($old_user);
            //return $this->actionLogin();
        }

        $model = new UserWxRegisterActivateForm(['scenario' => $json['client_type'] == 'client' ? 'client' : 'h5']);

        $model->setAttributes($json);
        try {
            if (!$model->wx_register()) {
                return [
                    'error_code' => ErrorCode::USER_REGISTER,
                    'message' => '用户注册失败。',
                    'errors' => $model->errors,
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::USER_REGISTER,
                'message' => '用户注册失败：' . $e->getMessage(),
            ];
        }
        /** @var UserWeixin $wx_user */
        $wx_user = UserWeixin::find()->where(['open_id' => $model->open_id])->one();
        Yii::warning($wx_user,$model->open_id);
        return $this->actionAutoLogin($wx_user->user);
        //return $this->actionLogin();
    }

    /**
     * 用户App自行注册
     * POST
     * {
     *     nickname, 昵称
     *     password, 密码
     *     mobile,   手机号码
     *     code,      短信验证码
     *     invite_code 邀请码
     *     team_code 邀请码
     * }
     */
    public function actionAppRegister()
    {
        $json = $this->checkJson([
            [['real_name', 'password', 'mobile', 'code', 'invite_code', 'team_invite_code'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $json['save_session'] = 1;
        $json['client_type'] = 'h5';

        $model = new UserAppRegisterForm(['scenario' => $json['client_type'] == 'client' ? 'client' : 'h5']);

        $model->setAttributes($json);
        try {
            if (!$model->register()) {
                return [
                    'error_code' => ErrorCode::USER_REGISTER,
                    'message' => '用户注册失败。',
                    'errors' => $model->errors,
                ];
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::USER_REGISTER,
                'message' => '用户注册失败：' . $e->getMessage(),
            ];
        }
        return $this->actionLogin();
    }

    /**
     * 用户激活先验证上级信息
     */
    public function actionCheckParent()
    {
        $json = $this->checkJson([
            [['p_phone', 'p_real_name'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        return [];
    }

    /**
     * 用户激活
     * POST
     * {
     *     real_name, 真实姓名
     *     nickname, 昵称
     *     password, 密码
     *     mobile,   手机号码
     *     code,      短信验证码
     *     invite_code, 邀请码
     *     open_id, 微信open_id
     * }
     */
    public function actionRegisterActivate()
    {
        $json = $this->checkJson([
            [['real_name', 'password', 'mobile', 'code', 'open_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $json['save_session'] = 1;

        /** @var User $user */
        $user = User::find()->where(['mobile' => $json['mobile'], 'real_name' => $json['real_name']])->one();
        if (empty($user)) {
            return [
                'error_code' => ErrorCode::USER_REGISTER,
                'message' => '用户验证失败，请检查手机号和真实姓名。',
                'errors' => '',
            ];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $p_user = User::findOne($user->pid);
            if (!empty($p_user)) {
                if ($p_user->prepare_count <=0 ) {
                    return [
                        'error_code' => ErrorCode::USER_REGISTER,
                        'message' => '用户上级预购数量不足不能激活了请联系上级。',
                        'errors' => '',
                    ];
                } else {
                    $r = User::updateAllCounters(['prepare_count' => -1], ['uid' => $p_user->id]);
                    if ($r <= 0) {
                        Yii::warning(['uid' => $user->id, 'pid'=>$p_user->id, 'message' => '无法更新上级账户预售数量-1。']);
                        throw new Exception('无法更新上级账户预售数量-1。');
                    }
                }
            }
            $user->status = User::STATUS_OK;
            $user->password = Yii::$app->security->generatePasswordHash($json['password']);
            if (!$user->save()) {
                throw new Exception('激活用户失败。');
            }
            $wx_user = new UserWeixin();
            $wx_user->uid = $user->id;
            $wx_user->open_id = $json['open_id'];
            $wx_user->create_time = time();
            if (!$wx_user->save()) {
                throw new Exception('用户绑定微信失败。');
            }
            $trans->commit();
            return $this->actionAutoLogin($user);
            //return $this->actionLogin();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
                return [
                    'error_code' => ErrorCode::USER_REGISTER,
                    'message' => '用户激活失败。',
                    'errors' => $e->getMessage(),
                ];
            }

        }
    }

    /**
     * 无账号密码登录
     * @param $user User
     * @return array
     */
    public function actionAutoLogin($user)
    {
        try {
            //$token = User::generateToken($this->client_api_version, $user->id);
            $token = $user->generateToken($this->app_id);
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法生成用户Token。',
            ];
        }
        Yii::$app->user->login($user, 86400 * 30);
        return [
            'uid' => $user->id,
            'token' => $token,
        ];
    }

    /**
     * 用户登录
     * POST
     * {
     *     mobile,   手机号码
     *     password, 密码
     * }
     */
    public function actionLogin()
    {
        $json = $this->checkJson([
            [['mobile', 'password'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        $model = new UserLoginForm();
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model->setAttributes($json);
        if (!$model->login(false)) {
            return [
                'error_code' => ErrorCode::USER_LOGIN,
                'message' => '登录失败，请检查登录参数。',
                'errors' => $model->errors,
            ];
        }
        $user = $model->getUser();
        try {
            //$token = User::generateToken($this->client_api_version, $user->id);
            $token = $user->generateToken($this->app_id);
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法生成用户Token。',
            ];
        }
        if (isset($json['save_session']) && $json['save_session'] == 1) {
            Yii::$app->user->login($user, 86400 * 30);
        }
        $cookie = Yii::$app->request->cookies->get('invite');
        if (!empty($cookie)) {
            // 推荐关系，需要保存到数据库中
            $invite = preg_split('/\|/', $cookie->value, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($invite as $item) {
                $item = explode(':', $item);
                $invite_user = User::findOne($item[0]);
                if (!empty($invite_user)) {
                    if (strpos($item[1], 's') === 0) {
                        UserRecommend::saveRecommend($invite_user->id, $user->id, substr($item[1], 1), null);
                    } elseif (strpos($item[1], 'g') === 1) {
                        UserRecommend::saveRecommend($invite_user->id, $user->id, null, substr($item[1], 1));
                    }
                }
            }
            Yii::$app->response->cookies->remove('invite');
        }
        return [
            'uid' => $user->id,
            'token' => $token,
        ];
    }

    /**
     * 用户微信自动登录
     * POST
     * {
     *     openid
     * }
     */
    public function actionWeiXinLogin()
    {
        $json = $this->checkJson([
            [['openid'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        /** @var UserWeixin $weixin_user */
        $weixin_user = UserWeixin::find()->where(['open_id' => $json['openid']])->one();
        if ($weixin_user) {
            $user = $weixin_user->user;
            try {
                //$token = User::generateToken($this->client_api_version, $user->id);
                $token = $user->generateToken($this->app_id);
            } catch (Exception $e) {
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '无法生成用户Token。',
                ];
            }
            if (isset($json['save_session']) && $json['save_session'] == 1) {
                Yii::$app->user->login($user, 86400 * 30);
            }
            $cookie = Yii::$app->request->cookies->get('invite');
            if (!empty($cookie)) {
                // 推荐关系，需要保存到数据库中
                $invite = preg_split('/\|/', $cookie->value, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($invite as $item) {
                    $item = explode(':', $item);
                    $invite_user = User::findOne($item[0]);
                    if (!empty($invite_user)) {
                        if (strpos($item[1], 's') === 0) {
                            UserRecommend::saveRecommend($invite_user->id, $user->id, substr($item[1], 1), null);
                        } elseif (strpos($item[1], 'g') === 1) {
                            UserRecommend::saveRecommend($invite_user->id, $user->id, null, substr($item[1], 1));
                        }
                    }
                }
                Yii::$app->response->cookies->remove('invite');
            }
            return [
                'uid' => $user->id,
                'token' => $token,
            ];
        } else {
            return ['error_code' => ErrorCode::PARAM, 'message' => '没有该账号'];
        }
    }

    /**
     * 获取新的登录Token
     * @return User|array
     */
    public function actionNewToken()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        try {
            //$token = User::generateToken($this->client_api_version, $user->id);
            $token = $user->generateToken($this->app_id);
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法生成用户Token。',
            ];
        }
        return [
            'uid' => $user->id,
            'token' => $token,
        ];
    }

    /**
     * 获取用户open_id
     * GET
     */
    public function actionGetOpen()
    {
        $code = $this->get('code');
        $api = new WeixinMpApi();
        $result = $api->code2Openid($code);
        return ['open_id' => $result];
    }

    /**
     * 验证openid的身份
     * POST{
     *    open_id,
     * }
     */
    public function actionCheckUser()
    {
        $json = $this->checkJson([
            [['open_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $json['save_session'] =1;
        $type = 3;
        $token = '';
        $user = new \stdClass();
        /** @var UserWeixin $wx_user */
        $wx_user = UserWeixin::find()->where(['open_id' => $json['open_id']])->one();
        if ($wx_user) {
            $user = $wx_user->user;
            if ($user->status == User::STATUS_OK || $user->status == User::STATUS_WAIT) {
                //正常微信自动登录
                $type = 1;
                try {
                    //$token = User::generateToken($this->client_api_version, $user->id);
                    $token = $user->generateToken($this->app_id);
                } catch (Exception $e) {
                    return [
                        'error_code' => ErrorCode::SERVER,
                        'message' => '无法生成用户Token。',
                    ];
                }
                Yii::$app->user->login($user, 86400 * 30);
                $cookie = Yii::$app->request->cookies->get('invite');
                if (!empty($cookie)) {
                    // 推荐关系，需要保存到数据库中
                    $invite = preg_split('/\|/', $cookie->value, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($invite as $item) {
                        $item = explode(':', $item);
                        $invite_user = User::findOne($item[0]);
                        if (!empty($invite_user)) {
                            if (strpos($item[1], 's') === 0) {
                                UserRecommend::saveRecommend($invite_user->id, $user->id, substr($item[1], 1), null);
                            } elseif (strpos($item[1], 'g') === 1) {
                                UserRecommend::saveRecommend($invite_user->id, $user->id, null, substr($item[1], 1));
                            }
                        }
                    }
                    Yii::$app->response->cookies->remove('invite');
                }
            }
        } else {
            //跳转注册 并 自动登录
            $type = 3;
        }
        return ['type' => $type, 'token' => $token, 'uid' => isset($user->id)? $user->id : ''];
    }

    /**
     * 用户基本信息
     * GET
     */
    public function actionDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        /** @var UserLevel  $user_level */
//        $user_level = UserLevel::find()
//            ->andWhere(['<=', 'money', $user->account->level_money])
//            ->orderBy('money DESC')
//            ->limit(1)
//            ->one();
        $user_level = $user->userLevel;
        return [
            'user' => [
                'id' => $user->id,
                'invite_code' => ($user->status == User::STATUS_OK )? $user->invite_code : '',
                'mobile' => $user->mobile,
                'have_password' => intval(!empty($user->password)),
                'have_payment_password' => intval(!empty($user->payment_password)),
                'nickname' => $user->nickname,
                'real_name' => $user->real_name,
                'gender' => $user->gender,
                'status' => $user->status,
                'avatar' => $user->getRealAvatar(true),
                'create_time' => $user->create_time,
                'level_name' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? $user_level->name : '普通会员') : '普通会员',
                'subsidy_money' => $user->subsidy_money,
                'subsidy_cumulative_money' =>$user->cumulative,
                'commission' => $user->account->commission,
                'compute_commission' => $user->computeCommission,
            ],
        ];
    }

    /**
     * 用户账户
     * GET
     */
    public function actionAccount()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        return [
            'account' => [
                'money' => $user->account->money,
                'commission' => $user->account->commission,
                'subsidy_money' => $user->account->subsidy_money,
                'score' => $user->account->score,
            ],
        ];
    }

    /**
     * 保存头像
     * POST
     * {
     *     avatar 头像地址
     * }
     */
    public function actionSaveAvatar()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['avatar'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (empty($json['avatar'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到头像参数。',
            ];
        }
        $user->avatar = $json['avatar'];
        $user->save();
        return [];
    }

    /**
     * 修改昵称
     * POST
     * {
     *     nickname 昵称
     * }
     */
    public function actionSaveNickname()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['nickname'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (empty($json['nickname'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到昵称参数。',
            ];
        }
        $user->nickname = $json['nickname'];
        $user->save();
        return [];
    }

    /**
     * 修改性别
     * POST
     * {
     *     gender 性别
     * }
     */
    public function actionSaveGender()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['gender'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (!isset($json['gender'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到性别参数。',
            ];
        }
        $user->gender = $json['gender'];
        return [];
    }

    /**
     * 发送短信验证码
     * GET
     * mobile 手机号
     * is_old 是否老手机号
     */
    public function actionSendSmsCode()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $mobile = $this->get('mobile');
        $is_old = $this->get('is_old');
        if (empty($is_old)) {
            $is_old = 0;
        }
        if (empty($mobile)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }

        if ($is_old == 0 && User::find()
                ->andWhere(['mobile' => $mobile, 'status' => [User::STATUS_OK, User::STATUS_STOP]])
                ->exists()) {
            return [
                'error_code' => ErrorCode::USER_MOBILE_EXIST,
                'message' => '手机号码已被注册。',
            ];
        }
        if ($is_old == 1 && !User::find()
                ->andWhere(['mobile' => $mobile, 'status' => [User::STATUS_OK, User::STATUS_STOP]])
                ->exists()) {
            return [
                'error_code' => ErrorCode::USER_MOBILE_EXIST,
                'message' => '手机号码不是原绑定手机号。',
            ];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, $user->id, $mobile, Sms::TYPE_BIND_MOBILE);
        if ($r !== true) {
            return [
                'error_code' => ErrorCode::USER_SEND_SMS,
                'message' => $r
            ];
        }
        return [];
    }

    /**
     * 绑定手机号
     * POST
     * {
     *     password 当前密码
     *     mobile 手机号
     *     code 验证码
     * }
     */
    public function actionBindMobile()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        if (empty($user->password)) {
            return [
                'error_code' => ErrorCode::USER_PASSWORD_EMPTY,
                'message' => '请先设置登录密码。',
            ];
        }
        $json = $this->checkJson([
            [['password', 'mobile', 'code'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model = new UserBindMobileForm();
        $model->uid = $user->id;
        $model->setAttributes($json);
        if (!$model->bindMobile()) {
            return [
                'error_code' => ErrorCode::USER_SAVE,
                'message' => '无法绑定新手机号码。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 修改密码
     * POST
     * {
     *     old_password 原密码
     *     password 新密码
     *     re_password 确认密码
     * }
     */
    public function actionSavePassword()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['old_password', 'password', 're_password'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        try {
            if (strpos($json['old_password'], '$base64aes$') === 0) {
                $json['old_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['old_password'], 11));
            }
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
            if (strpos($json['re_password'], '$base64aes$') === 0) {
                $json['re_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['re_password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model = new UserPasswordForm();
        $model->uid = $user->id;
        $model->setAttributes($json);
        if (!$model->savePassword()) {
            return [
                'error_code' => ErrorCode::USER_SAVE,
                'message' => '保存密码失败，请稍后重试。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 忘记密码发送短信验证码接口
     * GET
     * mobile 手机号
     */
    public function actionSendForgetSmsCode()
    {
        $mobile = $this->get('mobile');
        if (empty($mobile)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误',
            ];
        }
        /** @var User $user */
        $user = User::find()
            ->andWhere(['mobile' => $mobile, 'status' => User::STATUS_OK])
            ->one();
        if (empty($user)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到用户。',
            ];
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, $user->id, $user->mobile, Sms::TYPE_FORGOT_PASSWORD);
        if ($r !== true) {
            return [
                'error_code' => ErrorCode::USER_SEND_SMS,
                'message' => $r,
            ];
        }
        return [];
    }

    /**
     * 设置登录密码
     * POST
     * {
     *     mobile 手机号
     *     code 验证码
     *     password 密码
     *     re_password 确认密码
     * }
     */
    public function actionSetPassword()
    {
        $json = $this->checkJson([
            [['mobile', 'password', 'code'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (empty($json['mobile'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误',
            ];
        }
        /** @var User $user */
        $user = User::find()
            ->andWhere(['mobile' => $json['mobile'], 'status' => User::STATUS_OK])
            ->one();
        if (!$user) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '用户不存在。',
            ];
        }
        $model = new UserPasswordForm();
        $model->uid = $user->id;
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
            if (strpos($json['re_password'], '$base64aes$') === 0) {
                $json['re_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['re_password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model->setAttributes($json);
        if (!$model->setPassword()) {
            return [
                'error_code' => ErrorCode::USER_SAVE,
                'message' => '设置登录密码失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 支付密码发送短信验证码接口
     * GET
     */
    public function actionSendPaymentSmsCode()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $r = Sms::sendCode(Sms::U_TYPE_USER, $user->id, $user->mobile, Sms::TYPE_PAYMENT_PASSWORD);
        if ($r !== true) {
            return [
                'error_code' => ErrorCode::USER_SEND_SMS,
                'message' => $r
            ];
        }
        return [];
    }

    /**
     * 设置支付密码
     * POST
     * {
     *     code 验证码
     *     password 支付密码
     *     re_password 确认密码
     * }
     */
    public function actionSetPaymentPassword()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['password', 'code'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        try {
            if (strpos($json['password'], '$base64aes$') === 0) {
                $json['password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['password'], 11));
            }
            if (strpos($json['re_password'], '$base64aes$') === 0) {
                $json['re_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['re_password'], 11));
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
        $model = new UserPaymentPasswordForm();
        $model->uid = $user->id;
        $model->setAttributes($json);
        if (!$model->setPaymentPassword()) {
            return [
                'error_code' => ErrorCode::USER_SAVE,
                'message' => '设置支付密码失败，请稍后再试。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 获取收货地址列表
     * GET
     */
    public function actionAddressList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $address_list = [];
        foreach (UserAddress::find()
                     ->where(['uid' => $user->id, 'status' => UserAddress::STATUS_OK])
                     ->orderBy('is_default DESC, id DESC')
                     ->each() as $address) {
            /** @var UserAddress $address */
            $address_list[] = [
                'id' => $address->id,
                'area' => $address->area,
                'city' => $address->city->address(),
                'address' => $address->address,
                'name' => $address->name,
                'mobile' => $address->mobile,
                'is_default' => $address->is_default,
                'create_time' => $address->create_time,
            ];
        }
        return [
            'address_list' => $address_list,
        ];
    }

    /**
     * 获取收货地址详细
     * GET
     */
    public function actionAddressDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        if (!empty($id)) {
            $model = UserAddress::findOne($id);
            if (empty($model) || $model->uid != $user->id || $model->status == UserAddress::STATUS_DEL) {
                return [
                    'error_code' => ErrorCode::USER_ADDRESS_NOT_FOUND,
                    'message' => '用户收货地址不存在。',
                ];
            }
        } else {
            $model = new UserAddress();
            $city = IpCity::findByIp();
            if (!empty($city)) {
                $model->area = $city->area;
            }
            $model->name = $user->nickname;
            $model->mobile = $user->mobile;
        }
        return [
            'address' => $model,
        ];
    }

    /**
     * 增加修改 收货地址
     * POST
     * {
     *     id 收货地址编号 （修改地址有编号 新增没有）
     *     name 收人名字
     *     area 地区编码 371329  山东省临沂市河东区
     *     address 详细地址  XXX街道XX号
     *     mobile 收货人 手机号
     * }
     */
    public function actionSaveAddress()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['name', 'area', 'address', 'mobile'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (!empty($json['id'])) {
            $model = UserAddress::findOne($json['id']);
            if (empty($model) || $model->uid != $user->id || $model->status == UserAddress::STATUS_DEL) {
                return [
                    'error_code' => ErrorCode::USER_ADDRESS_NOT_FOUND,
                    'message' => '用户收货地址不存在。',
                ];
            }
        } else {
            $model = new UserAddress();
            $model->uid = $user->id;
            $model->status = UserAddress::STATUS_OK;
            $model->create_time = time();
        }
        $model->setAttributes($json);
        $area = City::find()->where(['code' => $json['area']])->one();
        if (empty($area)) {
            Yii::warning($area);
            return [
                'error_code' => ErrorCode::USER_ADDRESS_SAVE,
                'message' => '用户收货地址保存失败,地址编码不存在。',
                'errors' => '地址编码不存在',
            ];
        }
        $r = $model->save();
        if (!$r) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_SAVE,
                'message' => '用户收货地址保存失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 设置收货地址为默认地址
     * POST
     * {
     *      id 地址编号
     * }
     */
    public function actionSetAddressDefault()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $model = UserAddress::findOne($json['id']);
        if (empty($model) || $model->uid != $user->id || $model->status == UserAddress::STATUS_DEL) {
            return ['message' => '没有找到地址信息。'];
        }
        if ($model->is_default != 1) {
            $model->is_default = 1;
            UserAddress::updateAll(['is_default' => 0], ['uid' => $user->id]);
        }
        $r = $model->save();
        if (!$r) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_SAVE,
                'message' => '用户收货地址保存失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 删除用户收货地址
     * POST
     * {
     *      id 地址编号
     * }
     */
    public function actionDeleteAddress()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $model = UserAddress::findOne($json['id']);
        if (empty($model) || $model->uid != $user->id || $model->status == UserAddress::STATUS_DEL) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_NOT_FOUND,
                'message' => '用户收货地址不存在。',
            ];
        }
        if ($model->is_default == 1) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_DEL_DEFAULT,
                'message' => '默认收货地址不能删除。',
            ];
        }
        $model->status = UserAddress::STATUS_DEL;
        $r = $model->save();
        if (!$r) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_DELETE,
                'message' => '用户收货地址保存失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 收藏商品列表
     * GET
     */
    public function actionFavGoodsList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserFavGoods::find();
        $query->andWhere(['uid' => $user->id]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $fav_list = [];
        foreach ($query
                     ->orderBy('{{%user_fav_goods}}.create_time DESC')
                     ->offset($pagination->offset)
                     ->limit($pagination->limit)
                     ->each() as $fav) {
            /** @var UserFavGoods $fav */
            $fav_list[] = [
                'id' => $fav->id,
                'goods' => [
                    'id' => $fav->goods->id,
                    'title' => $fav->goods->title,
                    'desc' => $fav->goods->desc,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $fav->goods->main_pic,
                    'price' => $fav->goods->price,
                    'share_url' => Url::to(['/h5/goods/view', 'id' => $fav->goods->id, 'invite_code' => $user->invite_code], true),
                    'shop' => [
                        'id' => $fav->goods->shop->id,
                        'name' => $fav->goods->shop->name,
                    ],
                ],
            ];
        }
        return [
            'fav_list' => $fav_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 检查商品是否已经收藏
     */
    public function actionCheckFavGoods()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        return [
            'exist' => UserFavGoods::find()->andWhere(['uid' => $user->id, 'gid' => $id])->exists(),
        ];
    }

    /**
     * 添加收藏商品
     */
    public function actionAddFavGoods()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $goods = Goods::findOne($id);
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '商品不存在。',
            ];
        }
        $fav_goods = UserFavGoods::find()->where(['uid' => $user->id, 'gid' => $id])->one();
        if (!empty($fav_goods)) {
            return [
                'error_code' => ErrorCode::USER_FAV_EXIST,
                'message' => '商品已经收藏。',
            ];
        }
        $model = new UserFavGoods();
        $model->uid = $user->id;
        $model->gid = $id;
        $model->create_time = time();
        if (!$model->save()) {
            return [
                'error_code' => ErrorCode::USER_FAV_SAVE,
                'message' => '保存收藏失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 删除收藏商品
     * GET
     * id 单个编号
     * ids 多个编号，半角逗号隔开
     * gid 单个商品编号
     */
    public function actionDeleteFavGoods()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $gid = $this->get('gid');
        if (!empty($gid)) {
            UserFavGoods::deleteAll(['uid' => $user->id, 'gid' => $gid]);
            return [];
        }
        $id = $this->get('id');
        $ids = $this->get('ids');
        if (empty($id) && empty($ids)) {
            return [];
        }
        if (empty($ids)) {
            $ids = [$id];
        } else {
            $ids = explode(',', $ids);
        }
        UserFavGoods::deleteAll(['uid' => $user->id, 'id' => $ids]);
        return [];
    }

    /**
     * 收藏店铺列表
     * GET
     */
    public function actionFavShopList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserFavShop::find();
        $query->andWhere(['uid' => $user->id]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $fav_list = [];
        foreach ($query
                     ->orderBy('{{%user_fav_shop}}.create_time DESC')
                     ->offset($pagination->offset)
                     ->limit($pagination->limit)
                     ->each() as $fav) {
            /** @var UserFavShop $fav */
            $fav_list[] = [
                'id' => $fav->id,
                'shop' => [
                    'id' => $fav->shop->id,
                    'name' => $fav->shop->name,
                    'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($fav->sid, 'logo'),
                    'type' => KeyMap::getValue('merchant_type', $fav->shop->merchant->type),
                    'major_business' => (function () use ($fav) {
                        $cid_list = ShopConfig::getConfig($fav->sid, 'cid_list');
                        return empty($cid_list) ? [] : ArrayHelper::getColumn(GoodsCategory::find()->andWhere(['id' => json_decode($cid_list, true)])->all(), 'name');
                    })(),
                    'score' => (function () use ($fav) {
                        $score = ShopScore::find()->andWhere(['sid' => $fav->sid])->average('score');
                        return empty($score) ? intval(5): ceil($score);
                    })(),
                    'share_url' => Url::to(['/h5/shop/view', 'id' => $fav->shop->id, 'invite_code' => $user->invite_code], true),
                ],
            ];
        }
        return [
            'fav_list' => $fav_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 添加收藏店铺
     */
    public function actionAddFavShop()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $shop = Shop::findOne($id);
        if (empty($shop)) {
            return [
                'error_code' => ErrorCode::SHOP_NOT_FOUND,
                'message' => '店铺不存在。',
            ];
        }
        $user_fav_shop = UserFavShop::find()->where(['sid' => $id, 'uid' => $user->id])->one();
        if (!empty($user_fav_shop)) {
            return [
                'error_code' => ErrorCode::USER_FAV_EXIST,
                'message' => '店铺已经收藏。',
            ];
        }
        $model = new UserFavShop();
        $model->uid = $user->id;
        $model->sid = $id;
        $model->create_time = time();
        if (!$model->save()) {
            return [
                'error_code' => ErrorCode::USER_FAV_SAVE,
                'message' => '保存收藏失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 删除收藏店铺
     * GET
     * id 单个编号
     * ids 多个编号，半角逗号隔开
     * sid 单个店铺编号
     * sids 多个店铺编号，半角逗号隔开
     */
    public function actionDeleteFavShop()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $ids = $this->get('ids');
        $sid = $this->get('sid');
        $sids = $this->get('sids');
        $r1 = $r2 = 0;
        if (empty($id) && empty($ids) && empty($sid) && empty($sids)) {
            return [];
        }
        if (empty($ids)) {
            $ids = [$id];
        } else {
            $ids = explode(',', $ids);
        }
        if (!empty($ids)) {
            $r1 = UserFavShop::deleteAll(['uid' => $user->id, 'id' => $ids]);
        }
        if (empty($sids)) {
            $sids = [$sid];
        } else {
            $sids = explode(',', $sids);
        }
        if (!empty($sids)) {
            $r2 = UserFavShop::deleteAll(['uid' => $user->id, 'sid' => $sids]);
        }
        if ($r1 + $r2 > 0) {
            return [];
        }
        return [
            'error_code' => ErrorCode::USER_FAV_SAVE,
            'message' => '没有删除任何信息。',
        ];
    }

    /**
     * 用户搜索历史列表
     */
    public function actionHistoryList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserSearchHistory::find();
        $query->select(['keyword']);
        $query->andWhere(['uid' => $user->id]);
        $history_list = [];
        foreach ($query->orderBy('create_time DESC')->limit('10')->each() as $history) {
            $history_list[] = $history->keyword;
        }
        return ['history_list' => $history_list];
    }

    /**
     * 清空搜索历史
     */
    public function actionDeleteHistory()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        UserSearchHistory::deleteAll(['uid' => $user->id]);
        return [];
    }

    /**
     * 意见反馈
     * post
     * {
     *      content 反馈内容
     * }
     */
    public function actionFeedback()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['content'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (empty($json['client'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到客户端信息。',
            ];
        }
        if (empty($json['version'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到客户端版本号信息。',
            ];
        }
        if (empty($json['content'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '反馈内容必填。',
            ];
        }
        $model = new Feedback();
        $model->uid = $user->id;
        $model->status = Feedback::STATUS_WAIT;
        $model->create_time = time();
        $model->setAttributes($json);
        if (!$model->save()) {
            return [
                'error_code' => ErrorCode::FEEDBACK_ADD_FAIL,
                'message' => '无法保存反馈内容。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 用户消息列表
     */
    public function actionMessageList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $query = UserMessage::find();
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere(['<>', 'status', UserMessage::STATUS_DEL]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('status ASC, create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $message_list = [];
        /** @var UserMessage $message */
        foreach ($query->each() as $message) {
            $message_list[] = [
                'id' => $message->id,
                'title' => $message->title,
                'content' => $message->content,
                'status' => $message->status,
                'create_time' => $message->create_time,
            ];
        }
        return [
            'message_list' => $message_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 更新消息状态
     * GET
     * id 消息编号
     */
    public function actionSetMessageRead()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $message = UserMessage::findOne($id);
        if (empty($message) || $message->uid != $user->id) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到消息内容。',
            ];
        }
        $message->status = UserMessage::STATUS_OLD;
        $message->save(false);
        return [];
    }

    /**
     * 检查是否有新消息
     */
    public function actionCheckNewMessage()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        return [
            'have_new_msg' => UserMessage::find()->andWhere(['uid' => $user->id, 'status' => UserMessage::STATUS_NEW])->exists(),
        ];
    }

    /**
     * 客服中心
     * @return string
     */
    public function actionServiceCenter()
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('service_center');
    }

    /**
     * 佣金列表
     * GET
     */
    public function actionCommissionList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $type = $this->get('type', 1);
        if ($type == 1) {
            $query = UserCommission::find();
            $query->andWhere(['uid' => $user->id]);
            $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
            $commission_list = [];
            $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
            /** @var UserCommission $model */
            foreach ($query->each() as $model) {
                $from_user = User::findOne($model->from_uid);
                $commission_list[] = [
                    'id' => $model->id,
                    'logo' => $from_user->getRealAvatar(true),
                    'nickname' => !empty($from_user->nickname) ? $from_user->nickname : $from_user->real_name . '购买分享商品',
                    'commission' => $model->commission,
                    'time' => $model->time,
                    'remark' => $model->remark,
                ];
            }
        } else {
            if ($user->level_id == 1) {
                $share_commission_ratio_1 = 30;
            } elseif ($user->level_id == 2) {
                $share_commission_ratio_1 = 40;
            } elseif ($user->level_id == 3) {
                $share_commission_ratio_1 = 50;
            }
            $queryUser = User::find();
            $userList = $queryUser->andWhere(['pid' => $user->id])->asArray()->all();
            $userList = array_column($userList, 'id');

            $queryOrder = Order::find();
            $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
                ->andWhere(["<", 'status', Order::STATUS_COMPLETE]);

            $pagination = new Pagination(['totalCount' => $queryOrder->count(), 'validatePage' => false]);
            $commission_list = [];
            $queryOrder->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
            /** @var Order $model */
            foreach ($queryOrder->each() as $model) {
                $from_user = User::findOne($model->uid);
                $commission = 0 ;

                /** @var OrderItem $item */
                foreach ($model->itemList as $item) {
                    if (!in_array($item->goods->share_commission_type, [Goods::SHARE_COMMISSION_TYPE_MONEY, Goods::SHARE_COMMISSION_TYPE_RATIO])) {
                        // 此商品不参与分享佣金
                        continue;
                    }
                    if ($item->goods->is_pack == 1) {
                        continue;
                    }
                    // 一级分享
                    if (empty($share_commission_ratio_1) || Util::comp($share_commission_ratio_1, 0, 2) <= 0) {
                        // 店铺没有设置一级分享佣金比例
                        continue;
                    }
                    $item_commission_1 = 0;
                    if ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_MONEY) { // 固定金额
                        $item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                    } elseif ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_RATIO) { // 百分比
                        $item_commission_1 = round($item->price * $item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 10000, 2);
                    }
                    if (Util::comp($item_commission_1, 0, 2) > 0) {
                        $commission += $item_commission_1;
                    }
                }

                $commission_list[] = [
                    'id' => $model->id,
                    'logo' => $from_user->getRealAvatar(true),
                    'nickname' => !empty($from_user->nickname) ? $from_user->nickname : $from_user->real_name . '购买分享商品',
                    'commission' => $commission,
                    'time' => $model->create_time,
                    'remark' => '',
                ];
            }
        }

        return [
            'commission_list' => $commission_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 补贴列表
     * GET
     */
    public function actionSubsidyList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $query = UserSubsidy::find();
        $query->andWhere(['to_uid' => $user->id]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $subsidy_list = [];
        $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
        /** @var UserSubsidy $model */
        foreach ($query->each() as $model) {
            $from_user = User::findOne($model->from_uid);
            $subsidy_list[] = [
                'id' => $model->id,
                'logo' => $from_user->getRealAvatar(true),
                'nickname' => !empty($from_user->nickname) ? $from_user->nickname : $from_user->real_name,
                'money' => $model->money,
                'create_time' => $model->create_time,
                'remark' => KeyMap::getValue('user_subsidy_type', $model->type),
                'type' => $model->type,
                'type_str' => KeyMap::getValue('user_subsidy_type', $model->type),
            ];
        }
        return [
            'subsidy_list' => $subsidy_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 检查是否可以提现
     * GET
     */
    public function actionCheckCanWithdraw()
    {
        $can_withdraw = System::getConfig('withdraw_open') == 1;
        return [
            'can_withdraw' => $can_withdraw,
        ];
    }

    /**
     * 佣金提现列表
     * GET
     */
    public function actionWithdrawList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $type = $this->get('type', '2');
        $type = empty($type) ? 2 : $type;
        $query = UserWithdraw::find();
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere(['type' => $type]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $withdraw_list = [];
        /** @var UserWithdraw $withdraw_bank */
        foreach ($query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit)->each() as $withdraw_bank) {
            /** @var WithdrawBank $bank */
            $bank = WithdrawBank::find()->where(['name'=>$withdraw_bank->bank_name])->one();
            if ($withdraw_bank->status == 1 || $withdraw_bank->status == 2) {
                $withdraw_bank->status = 1;
            }
            if ($withdraw_bank->status == 3) {
                $withdraw_bank->status = 2;
            }
            $withdraw_list[] = [
                'id' => $withdraw_bank->id,
                'money' => $withdraw_bank->money,
                'bank_name' => $withdraw_bank->bank_name,
                'create_time' => $withdraw_bank->create_time,
                'remark' => $withdraw_bank->remark,
                'type' => $withdraw_bank->type,
                'type_str' => KeyMap::getValue('user_withdraw_type', $withdraw_bank->type),
                'status' => $withdraw_bank->status,
                'bank_logo' => empty($bank) ? '' :Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $bank->logo,
            ];
        }
        return [
            'withdraw_list' => $withdraw_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 获取用户绑定的银行卡列表
     * GET
     */
    public function actionWithdrawBankList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $query = UserBank::find();
        $query->andWhere(['uid' => $user->id]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $withdraw_bank_list = [];
        /** @var UserBank $withdraw_bank */
        foreach ($query->select('id, bank_name, account_name, account_no')->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit)->each() as $withdraw_bank ) {
            /** @var WithdrawBank $bank */
            $bank = WithdrawBank::find()->where(['name'=>$withdraw_bank->bank_name])->one();
            $withdraw_bank_list[] = [
                'id' => $withdraw_bank->id,
                'bank_name' => $withdraw_bank->bank_name,
                'account_name' => $withdraw_bank->account_name,
                'account_no' => $withdraw_bank->account_no,
                'bank_logo' => empty($bank) ? '' :Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $bank->logo,
            ];
        }
        return [
            'withdraw_bank_list' => $withdraw_bank_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 佣金提现
     * POST
     * {
     *     bank_id 用户提现银行卡编号
     *     bank_name 银行名称
     *     bank_address 开户行地址
     *     account_name 账户名
     *     account_no 账号
     *     money 提现金额
     *     payment_password 支付密码
     *     remark 备注
     * }
     */
    public function actionWithdraw()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        if ($user->status == User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '激活会员才可以提现。',
            ];
        }

        $json = $this->checkJson([
            [['money'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        if (empty($json['bank_id'])) {
            if (empty($json['bank_name']) || empty($json['account_name'])) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $withdraw_info = [
                'bank_name' => $json['bank_name'],
                'bank_address' => $json['bank_address'],
                'account_name' => $json['account_name'],
                'account_no' => $json['account_no'],
                'remark' => $json['remark'],
            ];
        } else {
            /** @var UserBank $user_bank */
            $user_bank = UserBank::find()->where(['id' => $json['bank_id']])->one();
            if (empty($user_bank)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '没有找到银行卡信息。',
                ];
            }
            if ($user_bank->uid != $user->id) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。'
                ];
            }
            $withdraw_info = [
                'bank_name' => $user_bank->bank_name,
                'bank_address' => $user_bank->bank_address,
                'account_name' => $user_bank->account_name,
                'account_no' => $user_bank->account_no,
                'remark' => '提现到' .  $user_bank->bank_name,
            ];
        }
        $withdraw_info['money'] = $json['money'];
        if (empty($withdraw_info['account_no'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '账号信息错误，请填写正确的账号',
            ];
        }
        if ($withdraw_info['money'] <= 0) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '金额参数错误，请填写正确的金额。',
            ];
        }
//        try {
//            if (strpos($json['payment_password'], '$base64aes$') === 0) {
//                $json['payment_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['payment_password'], 11));
//            }
//        } catch (Exception $e) {
//            return [
//                'error_code' => ErrorCode::SERVER,
//                'message' => $e->getMessage(),
//            ];
//        }
//        $payment_password = $json['payment_password'];
//        if (!$user->validatePaymentPassword($payment_password)) {
//            return [
//                'error_code' => ErrorCode::USER_PAYMENT_PASSWORD,
//                'message' => '支付密码错误。',
//            ];
//        }
//        if ($withdraw_info['money'] > $user->account->commission) {
//            return [
//                'error_code' => ErrorCode::USER_COMMISSION_LESS,
//                'message' => '余额不足。',
//            ];
//        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 减少账户余额
            $account = $user->account;
            $account->commission = $account->commission - $withdraw_info['money'];
            $r = $account->save();
            if (!$r) {
                throw new Exception('减少账户余额失败。', '', ErrorCode::USER_ACCOUNT_SAVE_FAIL);
            }
            // 增加账户明细记录
            $user_account_log = new UserAccountLog();
            $attributes = [
                'uid' => $user->id,
                'commission' => $withdraw_info['money'] * -1,
                'time' => time(),
                'remark' => $withdraw_info['remark'],
            ];
            $user_account_log->setAttributes($attributes);
            $r1 = $user_account_log->save();
            if (!$r1) {
                throw new Exception('添加账户明细失败。', '', ErrorCode::USER_ACCOUNT_LOG_SAVE_FAIL);
            }

            // 增加提现记录
            $user_withdraw = new UserWithdraw();
            $withdraw_info['uid'] = $user->id;
            $withdraw_info['tax'] = 0;
            $withdraw_info['create_time'] = time();
            $withdraw_info['type'] = UserWithdraw::TYPE_COMMISSION;
            $withdraw_info['status'] = UserWithdraw::STATUS_WAIT;
            $user_withdraw->setAttributes($withdraw_info);
            $r2 = $user_withdraw->save();
            if (!$r2) {
                throw new Exception('添加提现记录失败。', '', ErrorCode::USER_WITHDRAW_SAVE_FAIL);
            }

            // 保存用户提现账号
            $is_bank_exists = UserBank::find()->where(['account_no' => $withdraw_info['account_no']])->one();
            if (empty($is_bank_exists)) {
                $userBank = new UserBank();
                $userBank->uid = $user->id;
                $userBank->bank_name = $withdraw_info['bank_name'];
                $userBank->bank_address = $withdraw_info['bank_address'];
                $userBank->account_name = $withdraw_info['account_name'];
                $userBank->account_no = $withdraw_info['account_no'];
                $r3 = $userBank->save();
                if (!$r3) {
                    throw new Exception('保存用户账号失败。', '', ErrorCode::USER_BANK_SAVE_FAIL);
                }
            }
            $trans->commit();
            return [
                'id' => $user_withdraw->id,
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 提现进度
     * GET
     * withdraw_id 提现记录编号
     */
    public function actionWithdrawDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $withdraw_id = $this->get('withdraw_id');
        /** @var UserWithdraw $withdraw */
        $withdraw = UserWithdraw::find()->where(['id' => $withdraw_id])->one();
        if (empty($withdraw)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到提现记录。',
            ];
        }
        if ($withdraw->uid != $user->id) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误',
            ];
        }
        return [
            'withdraw' => $withdraw->attributes,
        ];
    }

    /**
     * 银行卡列表
     * GET
     */
    public function actionBankList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $query = WithdrawBank::find();
        $query->andFilterWhere(['like', 'name', $this->get('bank_name')]);
        $bank_list = [];
        /** @var WithdrawBank $bank */
        foreach ($query->each() as $bank) {
            $bank_list[] = [
                'name' => $bank->name,
                'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $bank->logo,
            ];
        }
        return [
            'bank_list' => $bank_list,
        ];
    }

    /**
     * 获取账户佣金明细
     * GET
     */
    public function actionAccountCommissionList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserAccountLog::find();
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere(['<', 'commission', 0]);
        $query->orderBy('time DESC, id DESC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $account_log_list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        $list = [];
        /** @var UserAccountLog $account_log */
        foreach ($account_log_list as $account_log) {
            $list[] = [
                'id' => $account_log->id,
                'logo' => $account_log->user->getRealAvatar(true),
                'nickname' => !empty($account_log->user->nickname) ? $account_log->user->nickname : '',
                'commission' => $account_log->commission,
                'time' => $account_log->time,
                'remark' => $account_log->remark,
            ];
        }
        return [
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 获取账户现金明细
     * GET
     */
    public function actionAccountMoneyList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserAccountLog::find();
        $query->select(['id', 'money', 'time', 'remark']);
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere('money IS NOT NULL');
        $query->orderBy('time DESC, id DESC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return [
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 获取账户积分明细
     * GET
     */
    public function actionAccountScoreList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserAccountLog::find();
        $query->select(['id', 'score', 'time', 'remark']);
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere('score IS NOT NULL');
        $query->orderBy('time DESC, id DESC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = $query->offset($pagination->offset)->limit($pagination->limit)->all();
        return [
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 下三级列表
     */
    public function actionSuperiorList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $superior_list = [];
        $list = $user->getChildList();
        if (empty($list)) {
            return [
                'superior_list' => $superior_list,
            ];
        }
        /** @var User $user1 */
        foreach ($list as $user1) {
            /** @var UserSubsidy $subsidy */
            $subsidy = UserSubsidy::find()->where(['to_uid' => $user, 'from_uid' => $user1->id])->one();
            $subsidy_money = empty($subsidy) ? 0 : $subsidy->money;
            $user1_info = [
                'nickname' => empty($user1->nickname) ? $user1->real_name :$user1->nickname,
                'level_name' => $user1->userLevel->name,
                'mobile' => $user1->mobile,
                'avatar' => $user1->getRealAvatar(true),
                'status' => $user1->status,
                'create_time' => $user1->create_time,
                'subsidy_money' => $subsidy_money
            ];
            if (!empty($user1->getChildList())) {
                /** @var User $user2 */
                foreach ($user1->getChildList() as $user2) {
                    $user2_info = [
                        'nickname' => empty($user2->nickname) ? $user2->real_name :$user2->nickname,
                        'level_name' => $user2->userLevel->name,
                        'mobile' => $user2->mobile,
                        'avatar' => $user2->getRealAvatar(true),
                        'status' => $user2->status,
                    ];
                    if (!empty($user2->getChildList())) {
                        /** @var User $user3 */
                        foreach ($user2->getChildList() as $user3) {
                            $user3_info = [
                                'nickname' => empty($user3->nickname) ? $user3->real_name :$user3->nickname,
                                'level_name' => $user3->userLevel->name,
                                'mobile' => $user3->mobile,
                                'avatar' => $user3->getRealAvatar(true),
                                'status' => $user3->status,
                            ];
                            $user2_info['c_list'][] = $user3_info;
                        }
                    } else {
                        $user2_info['c_list'] = [];
                    }
                    $user1_info['c_list'][] = $user2_info;
                }
            } else {
                $user1_info['c_list'] = [];
            }
            $superior_list[] = $user1_info;
        }
        return [
            'superior_list' => $superior_list,
        ];
    }

    /**
     * 充值金额列表
     * @return array
     */
    public function actionRechargeValues()
    {
        $recharge_values = UserLevel::find()->select('money')->all();
        $recharge_list = [];
        foreach ($recharge_values as $v) {
            $recharge_list[] = $v['money'];
        }
        return [
            'recharge_list' => $recharge_list,
        ];
    }

    /**
     * 等级列表
     * @return array
     */
    public function actionLevelList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        /** @var UserLevel  $user_level */
//        $user_level = UserLevel::find()
//            ->andWhere(['<=', 'money', $user->account->level_money])
//            ->orderBy('money DESC')
//            ->limit(1)
//            ->one();
        $user_level = $user->userLevel;
        /** @var UserLevel $user_next_level */
//        $user_next_level = UserLevel::find()
//            ->andWhere(['>', 'money', $user->account->level_money])
//            ->orderBy('money ASC')
//            ->limit(1)
//            ->one();
//        if (empty($user_next_level)) {
//            /** @var UserLevel $user_next_level */
//            $user_next_level = UserLevel::find()
//                ->orderBy('money DESC')
//                ->limit(1)
//                ->one();
//        }
        if ($user->status == User::STATUS_WAIT) {
            $user_next_level = UserLevel::find()->where(['id' => $user->level_id])->one();
        }
        if ($user->status == User::STATUS_OK && $user->level_id == 3) {
            $user_next_level = UserLevel::find()->where(['id' => $user->level_id])->one();
        }
        if ($user->status == User::STATUS_OK && $user->level_id == 2) {
            $user_next_level = UserLevel::find()->where(['id' => ($user->level_id + 1)])->one();
        }
        if ($user->status == User::STATUS_OK && $user->level_id == 1) {
            $user_next_level = UserLevel::find()->where(['id' => ($user->level_id + 1)])->one();
        }
//        if ($user->status == User::STATUS_OK || $user->level_id == 3 ) {
//            $user_next_level = UserLevel::find()->where(['id' => $user->level_id])->one();
//        } elseif ($user->status == User::STATUS_OK || $user->level_id == 1 ) {
//            $user_next_level = UserLevel::find()->where(['id' => ($user->level_id + 1)])->one();
//        } elseif($user->status == User::STATUS_WAIT || ($user->level_id == 1 || $user->level_id == 2)) {
//            $user_next_level = UserLevel::find()->where(['id' => ($user->level_id + 1)])->one();
//        }

        //$user_child = User::find()->where(['pid' => $user->id, 'status' => User::STATUS_OK])->count();
        //if ($user->status == User::STATUS_OK) {$money = (int)($user_child+1) * 399;} else { $money = 0;}
        $money = $user->growth_money;
        $user_info = [
            'id' => $user->id,
            'avatar' => $user->getRealAvatar(true),
            'nickname' => $user->nickname,
            'level_name' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? $user_level->name : '普通用户') : '普通用户',
            'money' => $money, //$user->account->level_money,
            'next_level_money' => $user_next_level->money,
            'parent' => [
                'real_name' => empty($user->parent) ? '' : $user->parent->real_name,
                'mobile' => empty($user->parent) ? '' : $user->parent->mobile,
            ],
            'prepare_count' => $user->prepare_count,
        ];
        $query = UserLevel::find();
        $query->andWhere(['status' => UserLevel::STATUS_OK]);
        $query->orderBy('create_time DESC, id DESC');
        $level_list = [];
        /** @var UserLevel $level */
        foreach ($query->each() as $level) {
            $level_list[] = [
                'id' => $level->id,
                'name' => $level->name,
                'money' => $level->money,
                'description' => $level->description,
                'commission_ratio_1' => $level->commission_ratio_1 . '%',
                'commission_ratio_2' => $level->commission_ratio_2 . '%',
                'commission_ratio_3' => $level->commission_ratio_3 . '%',
                'money_1' => $level->money_1 . '元',
                'money_2' => $level->money_2 . '元',
                'money_3' => $level->money_3 . '元',
            ];
        }
        return [
            'user_info' => $user_info,
            'level_list' => $level_list,
        ];
    }

    /**
     * 用户充值(进货)
     * @return User|array
     */
    public function actionRecharge()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $money = $this->get('money', 0, 'floatval');
        if (Util::comp($money, 0, 2) == 0) {
            return [
                'message' => '充值金额错误。',
            ];
        }
        $pay_method = $this->get('pay_method');
        $trans = Yii::$app->db->beginTransaction();
        try {
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_USER_RECHARGE;
            $financeLog->money = $money;
            $financeLog->pay_method = $pay_method;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            $financeLog->update_time = time();
            $financeLog->remark = '进货充值';
            if (!$financeLog->save()) {
                throw new Exception('无法保存财务记录。');
            }
            $userRecharge = new UserRecharge();
            $userRecharge->uid = $user->id;
            $userRecharge->fid = $financeLog->id;
            $userRecharge->money = $money;
            $userRecharge->create_time = time();
            $userRecharge->status = UserRecharge::STATUS_WAIT;
            $userRecharge->remark = '进货充值';
            if (!$userRecharge->save()) {
                throw new Exception('无法保存充值记录。');
            }
            switch ($pay_method) {
                case FinanceLog::PAY_METHOD_ALLINPAY_ALI:
                    if (System::getConfig('allinpay_ali_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_ALLINPAY_ALI;
                    if (empty($financeLog->trade_no)) {
                        $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $r =$financeLog->save();
                    if(!$r){
                        throw new Exception('没有生成财务记录。', ErrorCode::SERVER);
                    }
                    $allinpay_ali_api = new AllInPayAliApi();
                    try {
                        $json = $allinpay_ali_api->unitOrder($financeLog->trade_no, $financeLog->money);
                        if ($json['retcode'] != 'SUCCESS') {
                            throw new Exception($json['retmsg'], ErrorCode::SERVER);
                        }
                        if ($json['trxstatus'] != '0000') {
                            throw new Exception($json['errmsg'], ErrorCode::SERVER);
                        }
                        $result['payinfo'] = $json['payinfo'];
                        $result['recharge_id'] = $userRecharge->id;
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage(), ErrorCode::SERVER);
                    }
                    break;
                case FinanceLog::PAY_METHOD_WX_APP:
                    if (System::getConfig('weixin_app_pay_open') != 1) {
                        throw new Exception('系统没有开通微信APP支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_APP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-充值', $financeLog->trade_no, $financeLog->money, 'APP');
                    $result['weixin'] = [
                        'appid' => System::getConfig('weixin_app_app_id'),
                        'partnerid' => System::getConfig('weixin_app_mch_id'),
                        'prepayid' => $prepay_id,
                        'package' => 'Sign=WXPay',
                        'noncestr' => Util::randomStr(32, 7),
                        'timestamp' => time(),
                    ];
                    $result['weixin']['sign'] = $weixin_api->makeSign($result['weixin']);
                    $result['recharge_id'] = $userRecharge->id;
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    if (System::getConfig('weixin_mp_pay_open') != 1) {
                        throw new Exception('系统没有开通微信公众号支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_MP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinMpApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-充值', $financeLog->trade_no, $financeLog->money, 'JSAPI', $this->get('openid'));
                    $result['weixin'] = [
                        'timeStamp' => time(),
                        'nonceStr' => Util::randomStr(32, 7),
                        'package' => 'prepay_id=' . $prepay_id,
                        'signType' => 'MD5',
                    ];
                    $result['weixin']['paySign'] = $weixin_api->makeSign($result['weixin'], true);
                    $result['recharge_id'] = $userRecharge->id;
                    break;
                case FinanceLog::PAY_METHOD_WX_H5: // 微信H5支付
                    if (System::getConfig('weixin_h5_pay_open') != 1) {
                        throw new Exception('系统没有开通微信H5支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_H5;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinH5Api();
                    if (strpos(Yii::$app->request->userAgent, 'Android') > -1) {
                        $scene_info = [
                            'h5_info' => [
                                'type' => 'AndroidH5充值',
                                'app_name' => '云淘帮商城',
                                'package_name' => 'com.liuniukeji.yunyue', // TODO:安卓PackageName
                            ],
                        ];
                    } elseif (strpos(Yii::$app->request->userAgent, 'AppleWebKit') > -1) {
                        $scene_info = [
                            'h5_info' => [
                                'type' => 'IOSH5充值',
                                'app_name' => '惠民商城',
                                'bundle_id' => 'com.liuniukeji.yunyueOS', // TODO:苹果BundleId
                            ],
                        ];
                    } else {
                        $scene_info = [
                            'h5_info' => [
                                'type' => '微信H5充值',
                                'wap_url' => Url::to(['/h5/user/recharge-values'], true),
                                'wap_name' => System::getConfig('site_name'),
                            ],
                        ];
                    }
                    $redirect_url = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-充值', $financeLog->trade_no, $financeLog->money, 'MWEB', $scene_info);
                    $redirect_url .= '&redirect_url=' . urlencode(Url::to(['/h5/user/recharge-values'], true));
                    $result['redirect_url'] = $redirect_url;
                    $result['recharge_id'] = $userRecharge->id;
                    break;
                default:
                    throw new Exception('无法确定支付方式。', ErrorCode::SERVER);
            }
            $trans->commit();
            return $result;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 充值(进货)列表
     * @return User|array
     */
    public function actionRechargeList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $recharge_list = [];
        $query = UserRecharge::find()
            ->andWhere(['uid' => $user->id])
            ->andWhere(['status' => UserRecharge::STATUS_SUCCESS])
            ->orderBy('create_time DESC, id DESC');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->limit($pagination->limit)->offset($pagination->offset);
        /** @var UserRecharge $recharge */
        foreach ($query->each() as $recharge) {
            $recharge_list[] = [
                'id' => $recharge->id,
                'money' => $recharge->money,
                'pay_method' => $recharge->financeLog->pay_method,
                'create_time' => $recharge->create_time,
                'status' => $recharge->status,
                'remark' => $recharge->remark,
            ];
        }
        return[
            'recharge_list' => $recharge_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
        ];
    }

    /**
     * 充值(进货)详情
     * @return array
     */
    public function actionRechargeDetail()
    {
        $id = $this->get('id');
        $user_recharge = UserRecharge::findOne($id);
        if (empty($user_recharge)) {
            return [
                'error_code' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        return [
            'detail' => [
                'money' => $user_recharge->money,
                'status' => $user_recharge->status,
                'create_time' => $user_recharge->create_time,
            ],
        ];
    }

    /**
     * 分享链接
     * @return User|array
     */
    public function actionShareUrl()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $share_url = !empty(System::getConfig('share_url')) ? System::getConfig('share_url') . '?invite_code=' . $user->invite_code : '';
        return [
            'share_url' => $share_url,
        ];
    }

    /**
     * 补贴记录显示开关
     * @return array
     */
    public function actionSubOff()
    {
//        return [
//            'error_code' => '1111',
//            'message' => '未到开放时间。',
//        ];
        return [ 'error_code' => '0'];
    }

    /**
     * 补贴提现
     * POST
     * {
     *     bank_id 用户提现银行卡编号
     *     bank_name 银行名称
     *     bank_address 开户行地址
     *     account_name 账户名
     *     account_no 账号
     *     money 提现金额
     *     payment_password 支付密码
     *     remark 备注
     * }
     */
    public function actionSubsidyWithdraw()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        if ($user->status == User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '激活会员才可以提现。',
            ];
        }

        $json = $this->checkJson([
            [['money'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        if (empty($json['bank_id'])) {
            if (empty($json['bank_name']) || empty($json['account_name'])) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $withdraw_info = [
                'bank_name' => $json['bank_name'],
                'bank_address' => $json['bank_address'],
                'account_name' => $json['account_name'],
                'account_no' => $json['account_no'],
                'remark' => $json['remark'],
            ];
        } else {
            /** @var UserBank $user_bank */
            $user_bank = UserBank::find()->where(['id' => $json['bank_id']])->one();
            if (empty($user_bank)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '没有找到银行卡信息。',
                ];
            }
            if ($user_bank->uid != $user->id) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。'
                ];
            }
            $withdraw_info = [
                'bank_name' => $user_bank->bank_name,
                'bank_address' => $user_bank->bank_address,
                'account_name' => $user_bank->account_name,
                'account_no' => $user_bank->account_no,
                'remark' => '提现到' .  $user_bank->bank_name,
            ];
        }
        $withdraw_info['money'] = $json['money'];
        if (empty($withdraw_info['account_no'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '账号信息错误，请填写正确的账号',
            ];
        }
        if ($withdraw_info['money'] <= 0) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '金额参数错误，请填写正确的金额。',
            ];
        }
//        try {
//            if (strpos($json['payment_password'], '$base64aes$') === 0) {
//                $json['payment_password'] = SystemVersion::aesDecode($this->client_api_version, substr($json['payment_password'], 11));
//            }
//        } catch (Exception $e) {
//            return [
//                'error_code' => ErrorCode::SERVER,
//                'message' => $e->getMessage(),
//            ];
//        }
//        $payment_password = $json['payment_password'];
//        if (!$user->validatePaymentPassword($payment_password)) {
//            return [
//                'error_code' => ErrorCode::USER_PAYMENT_PASSWORD,
//                'message' => '支付密码错误。',
//            ];
//        }
        if (Util::comp($withdraw_info['money'], 100, 2) < 0) {
            return [
                'error_code' => ErrorCode::USER_SUBSIDY_LESS,
                'message' => '补贴不足100不能提现。',
            ];
        }
        if ($withdraw_info['money'] > $user->subsidy_money) {
            return [
                'error_code' => ErrorCode::USER_SUBSIDY_LESS,
                'message' => '补贴余额不足。',
            ];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 减少账户余额
            $user->subsidy_money = $user->subsidy_money - $withdraw_info['money'];
            $r = $user->save();
            if (!$r) {
                throw new Exception('减少账户余额失败。', '', ErrorCode::USER_ACCOUNT_SAVE_FAIL);
            }
            // 增加账户明细记录
            $user_account_log = new UserAccountLog();
            $attributes = [
                'uid' => $user->id,
                'subsidy_money' => $withdraw_info['money'] * -1,
                'time' => time(),
                'remark' => $withdraw_info['remark'],
            ];
            $user_account_log->setAttributes($attributes);
            $r1 = $user_account_log->save();
            if (!$r1) {
                throw new Exception('添加账户明细失败。', '', ErrorCode::USER_ACCOUNT_LOG_SAVE_FAIL);
            }

            // 增加提现记录
            $user_withdraw = new UserWithdraw();
            $withdraw_info['uid'] = $user->id;
            $withdraw_info['tax'] = 0;
            $withdraw_info['create_time'] = time();
            $withdraw_info['status'] = UserWithdraw::STATUS_WAIT;
            $withdraw_info['type'] = UserWithdraw::TYPE_SUBSIDY;
            $user_withdraw->setAttributes($withdraw_info);
            $r2 = $user_withdraw->save();
            if (!$r2) {
                throw new Exception('添加提现记录失败。', '', ErrorCode::USER_WITHDRAW_SAVE_FAIL);
            }

            // 保存用户提现账号
            $is_bank_exists = UserBank::find()->where(['account_no' => $withdraw_info['account_no']])->one();
            if (empty($is_bank_exists)) {
                $userBank = new UserBank();
                $userBank->uid = $user->id;
                $userBank->bank_name = $withdraw_info['bank_name'];
                $userBank->bank_address = $withdraw_info['bank_address'];
                $userBank->account_name = $withdraw_info['account_name'];
                $userBank->account_no = $withdraw_info['account_no'];
                $r3 = $userBank->save();
                if (!$r3) {
                    throw new Exception('保存用户账号失败。', '', ErrorCode::USER_BANK_SAVE_FAIL);
                }
            }
            $trans->commit();
            return [
                'id' => $user_withdraw->id,
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 补贴提现进度
     * GET
     * withdraw_id 提现记录编号
     */
    public function actionSubsidyWithdrawDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $withdraw_id = $this->get('withdraw_id');
        /** @var UserWithdraw $withdraw */
        $withdraw = UserWithdraw::find()->where(['id' => $withdraw_id])->one();
        if (empty($withdraw)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到提现记录。',
            ];
        }
        if ($withdraw->uid != $user->id) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误',
            ];
        }
        return [
            'withdraw' => $withdraw->attributes,
        ];
    }

    /**
     * 补贴银行提现开关
     * @return array
     */
    public function actionSubBankOff()
    {
        return [
            'bank' => 1,
            'ali' => 0,
        ];
//        return [
//            'error_code' => '1111',
//            'message' => '未到开放时间。',
//        ];
        return [ 'error_code' => '0'];
    }
}
