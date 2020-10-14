<?php

namespace app\modules\api\controllers;

use app\models\Ad;
use app\models\AdLocation;
use app\models\AlipayApi;
use app\models\AllInPayAliApi;
use app\models\City;
use app\models\Feedback;
use app\models\FinanceLog;
use app\models\Goods;
use app\models\GoodsCategory;
use app\models\GoodsCouponGift;
use app\models\GoodsCouponGiftUser;
use app\models\GoodsCouponRule;
use app\models\GoodsExpress;
use app\models\GoodsSku;
use app\models\GoodsSource;
use app\models\GoodsTraceVideo;
use app\models\IpCity;
use app\models\KeyMap;
use app\models\MerchantFinancialSettlement;
use app\models\NewHand;
use app\models\Notice;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderDeliverItem;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\Package;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\ShopScore;
use app\models\System;
use app\models\SystemVersion;
use app\models\User;
use app\models\Sms;
use app\models\UserAccount;
use app\models\UserAccountLog;
use app\models\UserAddress;
use app\models\UserAppRegisterForm;
use app\models\UserBank;
use app\models\UserBindMobileForm;
use app\models\UserBuyPack;
use app\models\UserCommission;
use app\models\UserFavGoods;
use app\models\UserFavShop;
use app\models\UserLevel;
use app\models\UserLoginForm;
use app\models\UserMessage;
use app\models\UserNewHand;
use app\models\UserNewschCat;
use app\models\UserNotice;
use app\models\UserPackageCoupon;
use app\models\UserPasswordForm;
use app\models\UserPaymentPasswordForm;
use app\models\UserRecharge;
use app\models\UserRecommend;
use app\models\UserRegisterForm;
use app\models\UserSaleLog;
use app\models\UserScoreLog;
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
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Response;

/**
 * 用户处理
 * Class UserController
 * @package app\modules\api\controllers
 */
class UserController extends BaseController
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
        $preg_phone='/^1[3456789]\d{9}$/ims';
        if(!preg_match($preg_phone,$mobile)){
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '手机号码错误。',
            ];
        }
        $user = User::find()
            ->andWhere(['mobile' => $mobile, 'status' => [User::STATUS_OK, User::STATUS_STOP, User::STATUS_WAIT]])
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
     *     open_id, 微信OPENID
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
            [['real_name', 'password', 'mobile', 'code', 'invite_code', 'open_id', 'union_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $preg_phone='/^1[3456789]\d{9}$/ims';
        if(!preg_match($preg_phone,$json['mobile'])){
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '手机号码错误。',
            ];
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
        $wxUser = UserWeixin::find()->where(['open_id' => $json['open_id']])->orWhere(['union_id' => $json['union_id']])->one();
        if (!empty($wxUser)) {
            return [
                'error_code' => ErrorCode::USER_UNION_ID_EXIST,
                'message' => '微信union_id已经绑定。',
            ];
        }
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
        $json['client_type'] = 'h5';
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
            [['real_name', 'password', 'mobile', 'code', 'open_id', 'union_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if (UserWeixin::find()->where(['open_id' => $json['open_id']])->orWhere(['union_id' => $json['union_id']])->count() > 0) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '微信号已经绑定请更换微信号绑定。',
            ];
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
            if ($this->client_api_version > '1.0.2') {
                $token = $user->generateToken($this->app_id);
            } else {
                $token = User::generateTokenVersion($this->client_api_version, $user->id);
            }
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
            Yii::warning($model->errors['password']);
            return [
                'error_code' => ErrorCode::USER_LOGIN,
                'message' => '登录失败，' . $model->errors['password'][0],
                'errors' => $model->errors,
            ];
        }
        $user = $model->getUser();
        try {
            if ($this->client_api_version > '1.0.2') {
                $token = $user->generateToken($this->app_id);
            } else {
                $token = User::generateTokenVersion($this->client_api_version, $user->id);
            }

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
            [['openid', 'union_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        /** @var UserWeixin $weixin_user */
        $weixin_user = UserWeixin::find()
            //->where(['open_id' => $json['openid']])
            ->where(['union_id' => $json['union_id']])->one();
        if ($weixin_user) {
            $user = $weixin_user->user;
            try {
                if ($this->client_api_version > '1.0.2') {
                    $token = $user->generateToken($this->app_id);
                } else {
                    $token = User::generateTokenVersion($this->client_api_version, $user->id);
                }
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
            return ['error_code' => ErrorCode::NO_RESULT, 'message' => '没有该账号'];
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
            if ($this->client_api_version > '1.0.2') {
                $token = $user->generateToken($this->app_id);
            } else {
                $token = User::generateTokenVersion($this->client_api_version, $user->id);
            }
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
                    if ($this->client_api_version > '1.0.2') {
                        $token = $user->generateToken($this->app_id);
                    } else {
                        $token = User::generateTokenVersion($this->client_api_version, $user->id);
                    }
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
     * 验证openid的身份
     * POST{
     *    open_id,
     *    union_id,
     * }
     */
    public function actionCheckUserNew()
    {
        $json = $this->checkJson([
            [['open_id', 'union_id'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $json['save_session'] =1;
        $type = 3;
        $token = '';
        $user = new \stdClass();
        /** @var UserWeixin $wx_user */
        $wx_user = UserWeixin::find()->where(['open_id' => $json['open_id']])->orWhere(['union_id' => $json['union_id']])->one();
        //$wx_user = UserWeixin::find()->where(['union_id' => $json['union_id']])->one();
        if ($wx_user) {
            $user = $wx_user->user;
            if ($user->status == User::STATUS_OK || $user->status == User::STATUS_WAIT) {
                //如果union_id为空  补充完整
                if (empty($wx_user->union_id)) {
                    $wx_user->union_id = $json['union_id'];
                    $wx_user->save();
                }
                //正常微信自动登录
                $type = 1;
                try {
                    if ($this->client_api_version > '1.0.2') {
                        $token = $user->generateToken($this->app_id);
                    } else {
                        $token = User::generateTokenVersion($this->client_api_version, $user->id);
                    }
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
        /** @var UserWeixin $wx */
        $wx = UserWeixin::find()->where(['uid' => $user->id])->one();
        //已使用积分信息
        $used_score=UserAccountLog::find()
            ->joinWith('order')
            ->andWhere(['{{%user_account_log}}.uid' => $user->id])
            ->andWhere(['<>','{{%order}}.status',Order::STATUS_CANCEL])
            ->sum('{{%user_account_log}}.score');
        $used_score=abs($used_score);
        return [
            'user' => [
                'id' => $user->id,
                'invite_code' => $user->invite_code,
                'mobile' => $user->mobile,
                'have_password' => intval(!empty($user->password)),
                'have_payment_password' => intval(!empty($user->payment_password)),
                'nickname' => $user->nickname,
                'real_name' => $user->real_name,
                'gender' => $user->gender,
                'status' => $user->status,
                'avatar' => $user->getRealAvatar(true),
                'create_time' => $user->create_time,
                'level_name' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? $user_level->name : '普通用户') : '普通用户',
                'level_logo' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$user_level->logo : '') : '',
                'subsidy_money' => $user->subsidy_money,
                'subsidy_cumulative_money' =>$user->cumulative,
                'commission' => $user->account->commission,
                'compute_commission' => $user->computeCommission,
                'prepare_count' => $user->prepare_count,
                'sale_count' => $user->saleCount,
                'is_sale' => ($user->prepare_count + $user->saleCount) > 0 ? 1 : 0,
                'union_id' => empty($wx->union_id) ? ((Yii::$app->params['site_host'] == 'http://yuntaobang.ysjjmall.com') ?  '123': '') : $wx->union_id,
                'team_count' => $user->teamCount,
                'score' => $user->account->score,
                'used_score'=>$used_score,//已使用积分
                'level_id' => $user->level_id,
                'is_service' => ($user->level_id == 3) ? 1 : 0,
                'share_url' => !empty(System::getConfig('share_url')) ? System::getConfig('share_url') . '?invite_code=' . $user->invite_code : ''
            ],
        ];
    }

    /**
     * 用户VIP信息
     * GET
     */
    public function actionVipDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        /** @var UserLevel  $user_level */
        /** @var UserLevel  $user_next_level */
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
        $user_level = $user->userLevel;

        //今日补贴
        $today_subsidy_money=UserSubsidy::find()
            ->where(['to_uid'=>$user->id])
            ->andWhere(['>','money',0])
            ->andWhere(['>','create_time',strtotime(date('Y-m-d',time()))])
            ->andWhere(['<=','create_time',time()])
            ->sum('money');

        //本月补贴
        $month_subsidy_money=UserSubsidy::find()
            ->where(['to_uid'=>$user->id])
            ->andWhere(['>','money',0])
            ->andWhere(['>','create_time',strtotime(date('Y-m',time()))])
            ->andWhere(['<=','create_time',time()])
            ->sum('money');

        //今日佣金
        $today_commission=UserAccountLog::find()
            ->where(['uid'=>$user->id])
            ->andWhere(['>','commission',0])
            ->andWhere(['>','time',strtotime(date('Y-m-d',time()))])
            ->andWhere(['<=','time',time()])
            ->sum('commission');

        //本月佣金
        $month_commission=UserAccountLog::find()
            ->where(['uid'=>$user->id])
            ->andWhere(['>','commission',0])
            ->andWhere(['>','time',strtotime(date('Y-m',time()))])
            ->andWhere(['<=','time',time()])
            ->sum('commission');
        //已使用积分信息
        $used_score=UserAccountLog::find()
            ->joinWith('order')
            ->andWhere(['{{%user_account_log}}.uid' => $user->id])
            ->andWhere(['<>','{{%order}}.status',Order::STATUS_CANCEL])
            ->sum('{{%user_account_log}}.score');
        $used_score=abs($used_score);
        /** @var UserWeixin $wx */
        //$wx = UserWeixin::find()->where(['uid' => $user->id])->one();
        //广告图
        $vip_pic='';
        $vip_pic_url='';
        foreach (AdLocation::findOne(34)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */

            $vip_pic = Util::fileUrl($ad->img);
            $vip_pic_url = $ad->url;

        }
        return [
            'user' => [
                'vip_pic' => $vip_pic,
                'vip_pic_url' => $vip_pic_url,
                //vip用户基本信息
                'id' => $user->id,
                'invite_code' => $user->invite_code,
                'nickname' => $user->nickname,
                'status' => $user->status,
                'avatar' => $user->getRealAvatar(true),
                //'level_logo' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $user_level->logo : '') : '',
                //vip用户等级信息
                'level_name' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? $user_level->name : '普通用户') : '普通用户',
                'level_logo' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $user_level->logo : '') : '',
                'growth_money' => intval($user->growth_money),// 成长值
                'level_money' => intval($user_next_level->money),//下一等级额度
                'gap_money' => $user_next_level->money - $user->growth_money,//下一等级 成长差值
                'next_level_name' => $user_next_level->name,
                //vip用户补贴信息
                'subsidy_money' => empty($user->subsidy_money) ? 0 : $user->subsidy_money,//总补贴金额
                'today_subsidy_money' => empty($today_subsidy_money) ? 0 : $today_subsidy_money,//今日补贴金额
                'month_subsidy_money' => empty($month_subsidy_money) ? 0 : $month_subsidy_money,//本月补贴金额
                //vip用户佣金信息
                'commission' => empty($user->account->commission) ? 0 : $user->account->commission,//总佣金
                'today_commission' => empty($today_commission) ? 0 : $today_commission,//今日佣金
                'month_commission' => empty($month_commission) ? 0 : $month_commission,//本月佣金
                //vip用户粉丝信息
                'team_count' => $user->teamCount,//粉丝总人数
                'team_active_count' => $user->teamActiveCount,//已激活
                'team_not_active_count' => $user->teamNotActiveCount,//未激活
                //vip用户店铺信息
                'is_sale' => ($user->status == User::STATUS_OK) ? 1 : 0 , //($user->prepare_count + $user->saleCount) > 0 ? 1 : 0,
                'prepare_count' => $user->prepare_count + $user->hand_count,//剩余大礼包预购数量
                'sale_count' => $user->saleCount,//已售大礼包数量
                'all_count' => $user->prepare_count + $user->hand_count + $user->saleCount,//大礼包数量总数量
                //vip用户积分信息
                'score' => $user->account->score,
                'used_score' => $used_score,//已使用积分
                'all_score' => $user->account->score + $used_score,//累计积分
            ],
            'ground_push' => [
                'switch' => System::getConfig('ground_push_active_switch', 0),
                'name' => System::getConfig('ground_push_active_name'),
                'url' => System::getConfig('ground_push_active_url'),
            ],
        ];
    }

    /**
     * 获取本月用户签到列表
     * GET
     */
    public function actionSignList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $date = date('Y-m-d', time());
        if (Yii::$app->cache->exists('sing_list_' . $user->id . '_date_' . $date)) {
            $list = Yii::$app->cache->get('sing_list_' . $user->id . '_date_' . $date);
        } else {
            $list = $user->signList;
            Yii::$app->cache->set('sing_list_' . $user->id . '_date_' . $date, $user->signList, 3600);
        }
        $count = 0;
        foreach ($list as $item){
            if ($item['is_sign'] == 1) $count += 1;
        };
        return ['list' => $list, 'count' => $count];
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
        $json['client_type'] = 'h5';
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
            ->andWhere(['mobile' => $mobile])
            ->andWhere(['IN', 'status', [User::STATUS_WAIT, User::STATUS_OK]])
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
            ->andWhere(['mobile' => $json['mobile']])
            ->andWhere(['IN', 'status', [User::STATUS_OK, User::STATUS_WAIT]])
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
     * 用户通知消息列表
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
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset)->limit($pagination->limit);
        $message_list = [];
        /** @var UserMessage $message */
        foreach ($query->each() as $message) {
            $message_list[] = [
                'id' => $message->id,
                'title' => $message->title,
                'content' => $message->content,
                'status' => $message->status,
                'url' => $message->url,
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
     * 用户通知消息详情
     * @return array
     */
    public function actionUserMessageDetail()
    {
        $id = $this->get('id');
        $message = UserMessage::findOne($id);
        if (empty($message)) {
            return [
                'error' => 'PARAM',
                'message' => '参数错误。',
            ];
        }
        $detail = [
            'id' => $message->id,
            'title' => $message->title,
            'url' => $message->url,
            'status'=>$message->status,
            'content' => $message->content,
            'create_time' => $message->create_time,
        ];
        return [
            'detail' => $detail,
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
     * 检查商学院是否有新消息
     */
    public function actionCheckNewHandMessage()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $newHand = NewHand::find()->select('id')->where(['status' => NewHand::STATUS_OK])->all();
        $count = UserNewHand::find()->where(['in','nid', array_column($newHand, 'id')])->andWhere(['uid' => $user->id])->count();
        if((count($newHand) - $count) > 0 )
        {
            return [
                'have_new_msg' =>true,
            ];
        }

        $cat_list=$this->actionSchoolCat();
        if(is_array($cat_list))
        {
            $res=in_array('1',array_column( $cat_list['list'], 'status'));
            if($res)
            {
                return [
                    'have_new_msg' =>true,
                ];
            }
        }
        return [
            'have_new_msg' =>false,
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
        $commission_list = [];
        $pagination = new Pagination(['totalCount' => 0, 'validatePage' => false]);
        if ($user->status == User::STATUS_OK || $user->status == User::STATUS_WAIT) {
            if ($this->client_api_version < '1.0.6') {
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
                        $title = $model->type == 1 ? '分享商品返佣' : '月结佣金';
                        $from_user->real_name = '云淘帮会员';
                        $commission_list[] = [
                            'id' => $model->id,
                            'logo' => $from_user->getRealAvatar(true),
                            'nickname' => !empty($from_user->nickname) ? $from_user->nickname . $title : $from_user->real_name . $title,
                            'commission' => $model->commission,
                            'time' => $model->time,
                            'remark' => $model->remark,
                        ];
                    }
                } else {

                    $queryUser = User::find();
                    $userList = $queryUser->andWhere(['pid' => $user->id])->asArray()->all();
                    $userList = array_column($userList, 'id');
                    $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
                    $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
                    $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;
                    $queryOrder = Order::find();
                    //$queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
                    $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
                        //->andWhere(["<", 'status', Order::STATUS_COMPLETE]);
                        ->andWhere(["BETWEEN", 'status', Order::STATUS_PAID, Order::STATUS_COMPLETE ]);
                    //->andWhere(['>=', 'create_time', $monthStartTime])->andWhere(['<=', 'create_time', $monthEndTime]);

                    $pagination = new Pagination(['totalCount' => $queryOrder->count(), 'validatePage' => false]);
                    $commission_list = [];
                    $queryOrder->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
                    /** @var Order $model */
                    foreach ($queryOrder->each() as $model) {
                        $from_user = User::findOne($model->uid);
                        $commission = 0 ;
                        $share_commission_ratio_1 = $user->childBuyRatio;
                        $share_commission_ratio_2 = 0;
                        if ($model->user->status == User::STATUS_OK) {
                            $share_commission_ratio_2 = $model->user->buyRatio;
                        }
                        if ($model->user->status == User::STATUS_OK) {
                            $share_commission_ratio_1 = 30;
                        }
                        if (MerchantFinancialSettlement::find()->where(['oid' => $model->id])->exists()) {
                            continue;
                        }

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
                            $sku=$item->goodsSku;//多规格佣金设置
                            if ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_MONEY) { // 固定金额
                                //$item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                                if ($share_commission_ratio_2 != 0) {
                                    if (empty($sku) || $sku->commission == '') {
                                        $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                                    } else {
                                        $item_commission_1 = round(($sku->commission * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                                    }
                                } else {
                                    if (empty($sku) || $sku->commission == '') {
                                        $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100), 2);
                                    } else {
                                        $item_commission_1 = round(($sku->commission * $share_commission_ratio_1 * $item->amount / 100), 2);
                                    }
                                }
                            } elseif ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_RATIO) { // 百分比
                                if (empty($sku) || $sku->commission == '') {
                                    $item_commission_1 = round($item->price * $item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 10000, 2);
                                } else {
                                    $item_commission_1 = round($item->price * $sku->commission * $share_commission_ratio_1 * $item->amount / 10000, 2);
                                }
                            }
                            if (Util::comp($item_commission_1, 0, 2) > 0) {
                                $commission += $item_commission_1;
                            }
                        }

                        if ($item->goods->is_pack != 1) {
                            $from_user->real_name = '云淘帮会员';
                            $commission_list[] = [
                                'id' => $model->id,
                                'logo' => $from_user->getRealAvatar(true),
                                'nickname' => !empty($from_user->nickname) ? $from_user->nickname . '购买分享商品' : $from_user->real_name . '购买分享商品',
                                'commission' => sprintf('%.2f', $commission),
                                'time' => $model->create_time,
                                'remark' => '',
                            ];
                        }
                    }
                }
            } else {
                $type = $this->get('type', 1);
                $commission_list = [];
                if ($type == 1) {
                    $query = UserCommission::find();
                    $query->andWhere(['uid' => $user->id]);
                    $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
                    $query->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
                    $user_level = $user->userLevel;
                    /** @var UserCommission $model */
                    foreach ($query->each() as $model) {
                        $from_user = User::findOne($model->from_uid);
                        $title = $model->type == 1 ? '分享商品返佣' : '月结佣金';
                        if ($model->type == 1) {
                            $order = [
                                'no' => $model->order->no,
                                'main_pic' => empty($model->oiid) ? Util::fileUrl($model->order->itemList[0]->goods->main_pic) : Util::fileUrl($model->orderItem->goods->main_pic),
                                'title' => $model->order->itemList[0]->goods->title,
                                'amount' => $model->order->itemList[0]->amount,
                                'price' => $model->order->itemList[0]->price,
                                'commission' => sprintf('%.2f', $model->commission)
                            ];
                        } else {
                            $order = [
                                'no' => '',
                                'main_pic' => '',
                                'title' => '',
                                'amount' => '0',
                                'price' => '0',
                                'commission' => '0'
                            ];
                        }

                        $commission_list[] = [
                            'id' => $model->id,
                            'type' => $model->type,
                            'logo' => $from_user->getRealAvatar(true),
                            'nickname' => !empty($from_user->nickname) ? $from_user->nickname : $from_user->real_name,
                            'level_logo' => ($user->status == User::STATUS_OK) ? (!empty($user_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$user_level->logo : '') : '',
                            'title' => $title,
                            'commission' => $model->commission,
                            'time' => $model->time,
                            'status_str' => '已分佣',
                            'remark' => ($model->type == 1) ? $model->remark : '月结佣金',
                            'order' => $order,
                        ];
                    }
                } elseif($type == 2) {
                    $queryUser = User::find();
                    $userList = $queryUser->andWhere(['pid' => $user->id])->asArray()->all();
                    $userList = array_column($userList, 'id');
                    $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
                    $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
                    $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;
                    $queryOrder = Order::find();
                    //$queryOrder->joinWith('merchantFinancialSettlement', true, 'RIGHT JOIN');
                    $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', '{{order}}.status', 0])
                        ->andWhere(["BETWEEN", '{{order}}.status', Order::STATUS_PAID, Order::STATUS_COMPLETE ]);
                    $queryOrder->andWhere(['<>', 'is_coupon', 1]);


                    $pagination = new Pagination(['totalCount' => $queryOrder->count(), 'validatePage' => false, 'pageSize' => 100]);
                    $queryOrder->orderBy('{{order}}.id desc')->offset($pagination->offset)->limit($pagination->limit);
                    $com = new UserCommission();
                    $commission_list = $com->compute($queryOrder->all(), $user, $type);
                } elseif ($type == 3) {
                    $queryUser = User::find();
                    $userList = $queryUser->andWhere(['pid' => $user->id])->asArray()->all();
                    $userList = array_column($userList, 'id');
                    $queryOrder = Order::find();
                    $queryOrder->where(['in', 'uid', $userList])->andWhere(['<>', 'status', 0])
                        ->andWhere(["IN", 'status', [Order::STATUS_COMPLETE, Order::STATUS_AFTER_SALE, Order::STATUS_CANCEL, Order::STATUS_CANCEL_WAIT_MANAGER, Order::STATUS_CANCEL_WAIT_MERCHANT ]]);
                    $queryOrder->andWhere(['<>', 'is_coupon', 1]);

                    $pagination = new Pagination(['totalCount' => $queryOrder->count(), 'validatePage' => false, 'pageSize' => 100]);
                    $queryOrder->orderBy('id desc')->offset($pagination->offset)->limit($pagination->limit);
                    $com = new UserCommission();
                    $commission_list = $com->compute($queryOrder->all(), $user, $type);
                }
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
            $from_user->real_name = '云淘帮会员';
            $subsidy_list[] = [
                'id' => $model->id,
                'logo' => $from_user->getRealAvatar(true),
                'nickname' => !empty($from_user->nickname) ? $from_user->nickname : $from_user->real_name,
                'real_name' => $from_user->real_name,
                'money' => $model->money,
                'create_time' => $model->create_time,
                'remark' => KeyMap::getValue('user_subsidy_type', $model->type),
                'type' => $model->type,
                'type_str' => KeyMap::getValue('user_subsidy_type', $model->type),
                'active_name' => empty($model->active_name) ? "" : $model->active_name . '活动奖励',
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
            if ($this->client_api_version == '1.0.0') {
                if ($withdraw_bank->status == 1 || $withdraw_bank->status == 2) {
                    $withdraw_bank->status = 1;
                }
                if ($withdraw_bank->status == 3) {
                    $withdraw_bank->status = 2;
                }
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

            if (Yii::$app->cache->exists('account_no_' . $json['account_no'])) {
                return [
                    'error_code' => ErrorCode::REQUEST_TO_MANY,
                    'message' => '请求太频繁了，请稍后重试。',
                ];
            } else {
                Yii::$app->cache->set('account_no_' . $json['account_no'], $json['account_no'], 2);
            }
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
            if (Yii::$app->cache->exists('account_no_' . $user_bank->account_no)) {
                return [
                    'error_code' => ErrorCode::REQUEST_TO_MANY,
                    'message' => '请求太频繁了，请稍后重试。',
                ];
            } else {
                Yii::$app->cache->set('account_no_' . $user_bank->account_no, $user_bank->account_no, 2);
            }
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
     * 获取账户积分支出明细
     * GET
     */
    public function actionAccountScoreUseList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserAccountLog::find();
        $query->select(['{{%order}}.score', 'time', 'remark']);
        $query->joinWith('order');
        $query->andWhere(['<>','{{%order}}.status',Order::STATUS_CANCEL]);
        $query->andWhere(['{{%user_account_log}}.uid' => $user->id]);
        $query->orderBy('time DESC');
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
        $query->select(['score', 'time', 'remark']);
        $query->union('SELECT score,create_time,remark FROM `user_score_log`  where uid='.$user->id,true);
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere('score IS NOT NULL');
        $query->andWhere(['<>', 'score', 0]);
        $query_all= $query->orderBy('time DESC');
        $query2=(new Query())->from([$query_all])->orderBy(['time'=>SORT_DESC]);
        $pagination = new Pagination(['totalCount' => $query2->count(), 'validatePage' => false]);
        $list = $query2->offset($pagination->offset)->limit($pagination->limit)->all();
       // $aa=$query2->createCommand()->getRawSql();
        /** @var UserAccountLog  $item  */
        $list = array_map(function($item) {
            if($item['score']>0)
            {
                $item['score']='+'.$item['score'];
            }
            return $item;
        }, $list);

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
            $user1->real_name = '云淘帮会员';
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
                    $user2->real_name = '云淘帮会员';
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
                            $user3->real_name = '云淘帮会员';
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
     * 我的团队列表 编辑微信号
     * get
     * {
     *      wx
     * }
     */
    public function actionEditWx()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $wx_no = $this->get('wx');
        $pattern='/^$|^[-_a-zA-Z0-9]{1}[-_a-zA-Z0-9]{0,19}$/';
        if (!preg_match($pattern,$wx_no)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '微信号格式不正确。',
            ];
        }
        $user->wx_no = $wx_no;
        if ($user->save(false)) {
            return [
                'result' => 'success'
            ];
        }
    }
    /**
     * 我的团队列表
     * get
     * {
     *      status 1激活 2未激活
     * }
     */
    public function actionTeamList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $status = $this->get('status');
        $query = User::find();
        $query->where(['pid' => $user->id]);
        $query->andFilterWhere(['status' => $status]);
        $query->orderBy('create_time desc');
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = [];
        /** @var User $user1 */
        foreach ($query->offset($pagination->offset)->limit($pagination->limit)->all() as $user1) {
            $active_str = '';
            switch ($user1) {
                case $user1->is_package_coupon_active == 1:
                    $active_str = '购买激活';
                    break;
                case $user1->is_self_active == 1:
                    $active_str = '购买激活';
                    break;
                case $user1->is_per_handle == 1:
                    $active_str = '售卖激活';
                    break;
                case $user1->is_invite_active == 1:
                    $active_str = '邀请激活';
                    break;
                case $user1->is_handle == 1:
                    $active_str = '后台激活';
                    break;
            }
            $user1->real_name = '云淘帮会员';
            $list[] = [
                'real_name' => $user1->real_name,
                'nickname' => $user1->nickname ? $user1->nickname : $user1->real_name,
                'level_id' => $user1->level_id,
                'level_logo' => ($user1->status == User::STATUS_OK) ? (!empty($user1->userLevel) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$user1->userLevel->logo : '') : '',
                'level_str' => KeyMap::getValue('user_level_id', $user1->level_id), //['1' => '会员', '2'=> '店主', '3' => '服务商'][$user->level_id],
                'mobile' => $user1->mobile,
                'wx_no'=> $user1->wx_no,
                'avatar' => $user1->getRealAvatar(true),
                'create_time' => $user1->create_time,
                'status' => $user1->status,
                'status_str' => KeyMap::getValue('user_status', $user1->status),
                'active_str' => $active_str
            ];
        }
        return [
            'team_count' => $user->teamCount,
            'team_active_count' => $user->teamActiveCount,
            'team_not_active_count' => $user->teamNotActiveCount,
            'hand_count' => $user->hand_count,
            'prepare_count' => $user->prepare_count,
            'all_count' => $user->hand_count + $user->prepare_count,
            'wx_no' => $user->wx_no,
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ],
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
     * 升级卡确认接口
     * @return User|array
     */
    public function actionUpgrade()
    {
        /** @var User || array $user */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        if ($user->status == User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '请先购买礼包激活。',
            ];
        }

        if ($user->level_id == 3) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '您的等级是服务商无需再升级了。',
            ];
        }
        $all_count = 21;
        if ($user->level_id == 2) {
            $all_count = 100;
        }
        /** @var UserLevel $user_next_level */
        $user_next_level = UserLevel::find()->where(['id' => ($user->level_id + 1)])->one();
        $count = $all_count - $user->prepare_count - $user->teamActiveCount;
        if ($count <= 0 ) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '您的预购数量已满无需升级。',
            ];
        }
        $start = strtotime(System::getConfig('user_upgrade_start'));
        $end = strtotime(System::getConfig('user_upgrade_end'));
        $type = System::getConfig('user_upgrade_type');
        $pack_price = 399;
        $price = $pack_price * $count;
        $value = System::getConfig('user_upgrade_value');
        $is_active = 0;
        if ($start < time() && $end > time() && !empty($value)) {
            $is_active = 1;
            if ($type == 0) {
                $pack_price = sprintf("%.2f", ($pack_price * $value / 100));
            } else {
                $pack_price = $value;
            }
        }
        $upgrade_price = $pack_price * $count;
        $info = [
            'is_active' => $is_active,
            'count' => $count,
            'level_name' => $user_next_level->name,
            'upgrade_price' => $upgrade_price,
            'price' => $price,
            'remark' => System::getConfig('user_upgrade_remark'),
        ];
        return ['info' => $info];
    }

    /**
     * 用户购买升级卡
     */
    public function actionPayUpgrade()
    {
        /** @var User || array $user */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        if ($user->status == User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '请先购买礼包激活。',
            ];
        }

        if ($user->level_id == 3) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '您的等级是服务商无需再升级了。',
            ];
        }
        if (Yii::$app->cache->exists('user_upgrade_' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('user_upgrade_' . $user->id, $user->id, 2);
        }

        $all_count = 21;
        if ($user->level_id == 2) {
            $all_count = 100;
        }

        $count = $all_count - $user->prepare_count - $user->teamActiveCount;
        if ($count <= 0 ) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_PUBLIC,
                'message' => '您的预购数量已满无需升级。',
            ];
        }
        $start = strtotime(System::getConfig('user_upgrade_start'));
        $end = strtotime(System::getConfig('user_upgrade_end'));
        $type = System::getConfig('user_upgrade_type');
        $pack_price = 399;
        $value = System::getConfig('user_upgrade_value');
        if ($start < time() && $end > time() && !empty($value)) {
            if ($type == 0) {
                $pack_price = sprintf("%.2f", ($pack_price * $value / 100));
            } else {
                $pack_price = $value;
            }
        }
        $money = $pack_price * $count;

        $pay_method = $this->get('pay_method');
        $trans = Yii::$app->db->beginTransaction();
        //$money = 0.01;
        try {
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_USER_UPGRADE;
            $financeLog->money = $money;
            $financeLog->pay_method = $pay_method;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            $financeLog->update_time = time();
            $financeLog->remark = '购买升级卡';
            if (!$financeLog->save()) {
                throw new Exception('无法保存财务记录。');
            }
            $userBuy = new UserBuyPack();
            $userBuy->uid = $user->id;
            $userBuy->fid = $financeLog->id;
            $userBuy->money = $money;
            $userBuy->type = UserBuyPack::TYPE_UPGRADE;
            $userBuy->amount = $count;
            $userBuy->create_time = time();
            $userBuy->status = UserBuyPack::STATUS_CREATED;
            $userBuy->remark = '购买升级卡';
            if (!$userBuy->save()) {
                throw new Exception('无法保存购买升级卡记录。');
            }
            switch ($pay_method) {
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝App
                    if (System::getConfig('alipay_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_ZFB_APP;
                    if (empty($financeLog->trade_no)) {
                        $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $financeLog->save();
                    $alipay_api = new AlipayApi();
                    $alipay = $alipay_api->AlipayTradeAppPay(System::getConfig('site_name') . '-购买升级卡', System::getConfig('site_name') . '-购买升级卡', $financeLog->trade_no, $financeLog->money);
                    $result['alipay'] = $alipay;
                    break;
                case FinanceLog::PAY_METHOD_WX_APP:
                    if (System::getConfig('weixin_app_pay_open') != 1) {
                        throw new Exception('系统没有开通微信APP支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_APP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-购买升级卡', $financeLog->trade_no, $financeLog->money, 'APP');
                    $result['weixin'] = [
                        'appid' => System::getConfig('weixin_app_app_id'),
                        'partnerid' => System::getConfig('weixin_app_mch_id'),
                        'prepayid' => $prepay_id,
                        'package' => 'Sign=WXPay',
                        'noncestr' => Util::randomStr(32, 7),
                        'timestamp' => time(),
                    ];
                    $result['weixin']['sign'] = $weixin_api->makeSign($result['weixin']);
                    $result['recharge_id'] = $userBuy->id;
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    if (System::getConfig('weixin_mp_pay_open') != 1) {
                        throw new Exception('系统没有开通微信公众号支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_MP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinMpApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-购买升级卡', $financeLog->trade_no, $financeLog->money, 'JSAPI', $this->get('openid'));
                    $result['weixin'] = [
                        'timeStamp' => time(),
                        'nonceStr' => Util::randomStr(32, 7),
                        'package' => 'prepay_id=' . $prepay_id,
                        'signType' => 'MD5',
                    ];
                    $result['weixin']['paySign'] = $weixin_api->makeSign($result['weixin'], true);
                    $result['recharge_id'] = $userBuy->id;
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
            if (Yii::$app->cache->exists('account_no_' . $json['account_no'])) {
                return [
                    'error_code' => ErrorCode::REQUEST_TO_MANY,
                    'message' => '请求太频繁了，请稍后重试。',
                ];
            } else {
                Yii::$app->cache->set('account_no_' . $json['account_no'], $json['account_no'], 2);
            }
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
                    'message' => '银行卡信息错误。'
                ];
            }
            $withdraw_info = [
                'bank_name' => $user_bank->bank_name,
                'bank_address' => $user_bank->bank_address,
                'account_name' => $user_bank->account_name,
                'account_no' => $user_bank->account_no,
                'remark' => '提现到' .  $user_bank->bank_name,
            ];
            if (Yii::$app->cache->exists('account_no_' . $user_bank->account_no)) {
                return [
                    'error_code' => ErrorCode::REQUEST_TO_MANY,
                    'message' => '请求太频繁了，请稍后重试。',
                ];
            } else {
                Yii::$app->cache->set('account_no_' . $user_bank->account_no, $user_bank->account_no, 2);
            }
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

    /**
     * 查询上级的头像昵称邀请码
     */
    public function actionParent()
    {
        $json = $this->checkJson();
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        if (!$user->parent) {
            if (empty($json['vip'])) {
                return [
                    'error_code' => ErrorCode::NO_RESULT,
                    'message' => '没有上级',
                ];

            } else {
                return [
                    'user' => [
                        'real_name' => System::getConfig('ytb_nick_name'),//$parent->real_name,
                        'nick_name' => System::getConfig('ytb_nick_name'),//
                        'mobile' => System::getConfig('ytb_phone'),
                        'avatar' => System::getConfig('sduty_avatar'),
                        'time' => '2019-01-18',
                    ]
                ];
            }
        }
        $parent = $user->parent;
//        if ($parent->status == User::STATUS_WAIT) {
//            return [
//                'error_code' => ErrorCode::NO_RESULT,
//                'message' => '没有上级',
//            ];
//        }

        if ($this->client_api_version >= '1.0.3') {
            $real_name = $parent->real_name;
            $nick_name = $parent->nickname;
        } else {
            $real_name = $parent->nickname;
            $nick_name = '';
        }
        $parent_level = $parent->userLevel;
        return [
            'user' => [
                'real_name' =>$real_name,//$parent->real_name,
                'nick_name'=>$nick_name,//
                'invite_code' => $parent->invite_code,
                'mobile'=>$parent->mobile,
                'wx_no'=>$parent->wx_no,
                'level_logo'=>($parent->status == User::STATUS_OK) ? (!empty($parent_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$parent_level->logo : '') : '',
                'avatar' => $parent->getRealAvatar(true),
                'time'=>date('Y-m-d',$parent->create_time),
            ]
        ];
    }

    /**
     * 邀请码验证上级
     * POST
     * {
     *      invite_code 邀请码
     * }
     */
    public function actionInviteCode()
    {
        $json = $this->checkJson([
            [['invite_code'], 'required', 'message' => '缺少必要参数invite_code。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        /** @var User $user */
        $user = User::find()->where(['invite_code' => $json['invite_code'], 'status' => User::STATUS_OK])->one();
        if (empty($user)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '邀请码错误。',
            ];
        }
        return ['real_name' => $user->real_name];
    }

    /**
     * 店主服务商预售大礼包 验证手机号
     * POST
     * {
     *      mobile 下级手机号码
     * }
     */
    public function actionCheckChildMobile()
    {
        if ($this->client_api_version < '1.0.5') {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '此功能需更新到新版本才可以使用哦。',
            ];
        }
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['mobile'], 'required', 'message' => '缺少必要参数手机号。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if ($user->prepare_count + $user->hand_count <= 0) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '预售名额已空请先购买套餐。',
            ];
        }
        /** @var User $childUser */
        $childUser = User::find()->where(['mobile' => $json['mobile']])->one();
        if (empty($childUser)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该手机号未注册。',
            ];
        }
        if ($childUser->status != User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '会员状态不是待激活状态。',
            ];
        }
        if ($childUser->parent->id != $user->id) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '用户不属于您直邀会员。',
            ];
        }
        $childUserAddressCount = UserAddress::find()->where(['uid' => $childUser->id, 'status' => UserAddress::STATUS_OK])->count();
        if ($childUserAddressCount <= 0) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该会员还没有设置收货地址。',
            ];
        }
        return [
            'error_code' => 0,
            'real_name' => $childUser->real_name,
            'nickname' => $childUser->nickname,
            'mobile' => $childUser->mobile,
            'message' => $childUser->real_name,
            'avatar' => $childUser->getRealAvatar(true),
            'hand_count' => $user->hand_count,
        ];
    }

    /**
     * 前台店主服务商自己售卖大礼包操作
     * POST
     * {
     *      mobile, 要激活的手机号
     *      gid， 礼包商品编号
     * }
     */
    public function actionActiveUser()
    {
        if ($this->client_api_version < '1.0.5') {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '此功能需更新到新版本才可以使用哦。',
            ];
        }
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            //[['mobile', 'gid'], 'required', 'message' => '缺少必要参数手机号或者商品编号。'],
            [['mobile'], 'required', 'message' => '缺少必要参数手机号或者商品编号。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        if ($user->prepare_count <=0 ) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '预购礼包数量已经不足。',
            ];
        }
        $json['gid'] = 2;
        /** @var User $childUser */
        $childUser = User::find()->where(['mobile' => $json['mobile']])->one();
        if (empty($childUser)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该手机号未注册。',
            ];
        }
        if ($childUser->status != User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '会员状态不是待激活状态。',
            ];
        }
        if ($childUser->parent->id != $user->id) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '用户不属于您直邀会员。',
            ];
        }
//        $childUserAddressCount = UserAddress::find()->where(['uid' => $childUser->id, 'status' => UserAddress::STATUS_OK])->count();
//        if ($childUserAddressCount <= 0) {
//            return [
//                'error_code' => ErrorCode::NO_RESULT,
//                'message' => '该会员还没有设置收货地址。',
//            ];
//        }
        //下单 激活  给上级返补贴
        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $json['gid'], 'status' => Goods::STATUS_ON])->one();
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有商品或者商品已经下架。',
            ];
        }
        //todo 只激活不生成订单  日后再开放
//        /** @var UserAddress $address */
//        $address = UserAddress::find()->andWhere(['uid' => $childUser->id, 'status' => UserAddress::STATUS_OK])->orderBy('is_default desc')->one();
//        if (empty($address)) {
//            return [
//                'error_code' => ErrorCode::ORDER_NO_ADDRESS,
//                'message' => '没有选择收货地址。',
//            ];
//        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            //todo 只激活不生成订单  日后再开放
//            $order = new Order();
//            $order->uid = $childUser->id;
//            $order->deliver_info = json_encode([
//                'area' => $address->area,
//                'address' => $address->address,
//                'name' => $address->name,
//                'mobile' => $address->mobile,
//            ]);
//            $order->create_time = time();
//            $order->status = Order::STATUS_CREATED;
//            $order->sid = $goods->sid;
//            $order->amount_money = 0; // 需要生成订单详情后再更新订单
//            $order->user_remark = ''; // 订单附言
//            if (!$order->save()) {
//                throw new Exception('无法保存订单信息。');
//            }
//
//            /** @var GoodsSku $sku */
//            $sku = null;
//            $order_item = new OrderItem();
//            $order_item->oid = $order->id;
//            $order_item->gid = $goods->id;
//            $order_item->title = $goods->title;
//            $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
//            $order_item->amount = 1;
//            $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
//            if (!$order_item->save()) {
//                throw new Exception('无法保存订单详情信息。');
//            }
//            $order->goods_money = $order_item->price * $order_item->amount;
//            $order->amount_money = $order_item->price * $order_item->amount;
//
//
//            if (!$order->save()) {
//                Yii::warning($order->getErrors());
//                throw new Exception('金额小于0， 无法更新订单金额。');
//            }
//            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '创建订单。', print_r($this->get(), true));
//            $order->status = Order::STATUS_PAID;
//            if (!$order->save()) {
//                Yii::warning($order->getErrors());
//                throw new Exception('金额小于0， 无法更新订单金额。');
//            }
//            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '店主售卖礼包商品。', print_r(['parent_uid' => $childUser->id], true));
            $sale = new UserSaleLog();
            $sale->uid = $user->id;
            $sale->to_uid = $childUser->id;
            $sale->gid = $goods->id;
            //$sale->oid = $order->id;
            $sale->create_time = time();
            if (!$sale->save()) {
                throw new Exception('礼包销售记录保存失败。');
            }

            //返补贴 给成长值并且自动升级
            $user->all_no_next_sub($childUser);
            $childUser->updateScore(); // 激活给400积分

            $r = User::updateAllCounters(['prepare_count' => -1], ['id' => $user->id]);
            if ($r <=0) {
                return ['message' => '该会员上级预购数量更新失败'];
            }
            $childUser->status = User::STATUS_OK;
            $childUser->level_id = 1;
            $childUser->is_per_handle = 1;
            $childUser->handle_time = time();
            if (!$childUser->save()) {
                throw new Exception('用户激活状态保存失败。');
            }
            /** 激活新会员发送给上级消息通知 */
            $user_message = new UserMessage();
            $nick_name='';
            if(!empty($childUser->nickname))
            {
                $nick_name='('.$childUser->nickname.')';
            }
            $user_message->MessageSend($user->id,'您名下新人('.$nick_name.')已激活成会员!',Yii::$app->params['site_host'].'/h5/user/team-list?status=1' ,'激活新会员');
            /** 激活新会员发送给用户自身消息通知 */
            $active_user_message= System::getConfig('active_user_message');
            $id=$user_message->MessageSend( $childUser->id,'您已经激活成为会员,点击查看您的权益!',Yii::$app->params['site_host'].'/h5/notice/umview' ,$active_user_message);
            if($id)
            {
                $message = UserMessage::findOne($id);
                $message->url= Yii::$app->params['site_host'].'/h5/notice/umview?id=' . $id .'&app=1';
                $message->save(false);

            }
            $trans->commit();
            return [
                //'order' => ['no' => $order->no],
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                //'message' => '生成订单错误：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 前台店主服务商自己售卖大礼包操作
     * POST
     * {
     *      mobile, 要激活的手机号
     *      gid， 礼包商品编号
     *      sku_id, 规格编号
     * }
     */
    public function actionActiveUserNew()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        if ($user->hand_count <= 0) {
            $json = $this->checkJson([
                [['mobile', 'gid'], 'required', 'message' => '缺少必要参数手机号或者商品编号。'],
            ]);
        } else {
            $json = $this->checkJson([
                [['mobile'], 'required', 'message' => '缺少必要参数手机号。'],
            ]);
            $json['gid'] = 2;
        }

        if (isset($json['error_code'])) {
            return $json;
        }

        if (Yii::$app->cache->exists('active_user_' . $json['mobile'])) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('active_user_' . $json['mobile'], $json['mobile'], 2);
        }

        if ($user->prepare_count + $user->hand_count <=0 ) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '预购礼包数量已经不足。',
            ];
        }
        /** @var User $childUser */
        $childUser = User::find()->where(['mobile' => $json['mobile']])->andWhere(['<>', 'status', User::STATUS_DELETE])->one();
        if (empty($childUser)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该手机号未注册。',
            ];
        }
        if ($childUser->status != User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '会员状态不是待激活状态。',
            ];
        }
        if ($childUser->parent->id != $user->id) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '用户不属于您直邀会员。',
            ];
        }
        $childUserAddressCount = UserAddress::find()->where(['uid' => $childUser->id, 'status' => UserAddress::STATUS_OK])->count();
        if ($childUserAddressCount <= 0) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '该会员还没有设置收货地址。',
            ];
        }
        //下单 激活  给上级返补贴
        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $json['gid'], 'status' => Goods::STATUS_ON])->one();
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有商品或者商品已经下架。',
            ];
        }
        if (GoodsSku::find()->where(['gid' => $json['gid']])->exists() && empty($json['sku_id'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '该商品必须选择规格请检查。',
            ];
        }
        //todo 只激活不生成订单  日后再开放
        /** @var UserAddress $address */
        $address = UserAddress::find()->andWhere(['uid' => $childUser->id, 'status' => UserAddress::STATUS_OK])->orderBy('is_default desc')->one();
        if (empty($address)) {
            return [
                'error_code' => ErrorCode::ORDER_NO_ADDRESS,
                'message' => '没有选择收货地址。',
            ];
        }
        $deliver_to_city = City::findByCode($address->area);
        if (empty($deliver_to_city)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '没有找到编号为[' . $address->area . ']的城市信息。',
            ];
        }
        $code_list = $deliver_to_city->address(true);
        $result = GoodsExpress::getGoodsExpress($json['gid'], 1, $code_list[0], count($code_list) > 1 ? $code_list[1] : '');
        if (!empty($result['message'])) {
            return [
                'error_code' => ErrorCode::GOODS_EXPRESS_NOT_FOUND,
                'message' => $result['message'],
            ];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($user->hand_count <= 0) {
                //todo 只激活不生成订单  日后再开放
                $order = new Order();
                $order->uid = $childUser->id;
                $order->deliver_info = json_encode([
                    'area' => $address->area,
                    'address' => $address->address,
                    'name' => $address->name,
                    'mobile' => $address->mobile,
                ]);
                $order->create_time = time();
                $order->status = Order::STATUS_PACKED;
                $order->sid = $goods->sid;
                $order->amount_money = 0; // 需要生成订单详情后再更新订单
                $order->user_remark = ''; // 订单附言
                if (!$order->save()) {
                    throw new Exception('无法保存订单信息。');
                }

                /** @var GoodsSku $sku */
                $sku = null;
                if (!empty($json['sku_id'])) {
                    $sku = GoodsSku::findOne($json['sku_id']);
                }
                $order_item = new OrderItem();
                $order_item->oid = $order->id;
                $order_item->gid = $goods->id;
                $order_item->title = $goods->title;
                $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
                $order_item->amount = 1;
                $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
                if (!$order_item->save()) {
                    throw new Exception('无法保存订单详情信息。');
                }
                $order->goods_money = $order_item->price * $order_item->amount;
                $order->amount_money = $order_item->price * $order_item->amount;


                if (!$order->save()) {
                    Yii::warning($order->getErrors());
                    throw new Exception('金额小于0， 无法更新订单金额。');
                }
                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '创建订单。', print_r($this->get(), true));

                /** @var OrderDeliver $order_deliver */
                $order_deliver = new OrderDeliver();
                $order_deliver->oid = $order->id;
                $order_deliver->create_time = time();
                $order_deliver->status = OrderDeliver::STATUS_WAIT;
                if (!empty($goods->supplier_id) && $goods->sale_type == Goods::TYPE_SUPPLIER) {
                    $order_deliver->supplier_id = $goods->supplier_id;
                }
                if (!$order_deliver->save()) {
                    Yii::warning($order_deliver->getErrors());
                    throw new Exception('发货单生成失败。');
                }
                /** @var OrderDeliverItem $order_deliver_item */
                $order_deliver_item = new OrderDeliverItem();
                $order_deliver_item->did = $order_deliver->id;
                $order_deliver_item->oiid = $order_item->id;
                $order_deliver_item->amount = 1;
                if (!$order_deliver_item->save()) {
                    Yii::warning($order_deliver_item->getErrors());
                    throw new Exception('发货单生成失败。');
                }

                $order->status = Order::STATUS_PACKED;
                if (!$order->save()) {
                    Yii::warning($order->getErrors());
                    throw new Exception('金额小于0， 无法更新订单金额。');
                }
                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '店主售卖礼包商品。', print_r(['parent_uid' => $childUser->id], true));
            }
            $sale = new UserSaleLog();
            $sale->uid = $user->id;
            $sale->to_uid = $childUser->id;
            $sale->gid = $goods->id;
            $sale->oid = isset($order) ? $order->id : '';
            $sale->create_time = time();
            if (!$sale->save()) {
                throw new Exception('礼包销售记录保存失败。');
            }

            //返补贴 给成长值并且自动升级
            $user->all_no_next_sub($childUser);
            $childUser->updateScore(); // 激活给400积分

            if ($user->hand_count > 0) {
                User::updateAllCounters(['hand_count' => -1], ['id' => $user->id]);
            } else {
                if ($user->prepare_count > 0) {
                    $r = User::updateAllCounters(['prepare_count' => -1], ['id' => $user->id]);
                    if ($r <= 0) {
                        return ['message' => '该会员上级预购数量更新失败'];
                    }
                }
            }
            $childUser->status = User::STATUS_OK;
            $childUser->level_id = 1;
            $childUser->is_per_handle = 1;
            $childUser->handle_time = time();
            if (!$childUser->save()) {
                throw new Exception('用户激活状态保存失败。');
            }
            /** 激活新会员发送给上级消息通知 */
            $user_message = new UserMessage();
            $nick_name='';
            if(!empty($childUser->nickname))
            {
                $nick_name='('.$childUser->nickname.')';
            }
            $user_message->MessageSend($user->id,'您名下新人'.$nick_name.'已激活成会员!',Yii::$app->params['site_host'].'/h5/user/team-list?status=1' ,'激活新会员');
            /** 激活新会员发送给用户自身消息通知 */
            $active_user_message= System::getConfig('active_user_message');
            $id=$user_message->MessageSend( $childUser->id,'您已经激活成为会员,点击查看您的权益!',Yii::$app->params['site_host'].'/h5/notice/umview' ,$active_user_message);
            if($id)
            {
                $message = UserMessage::findOne($id);
                $message->url= Yii::$app->params['site_host'].'/h5/notice/umview?id=' . $id .'&app=1';
                $message->save(false);

            }
            $trans->commit();
            return [
                //'order' => ['no' => $order->no],
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '生成订单错误：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 我的店铺
     */
    public function actionMyShop()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        /** @var AdLocation $ad_list */
        $ad_list = [];
        foreach (AdLocation::findOne(35)->getActiveAdList()->each() as $ad) {/** @var Ad $ad */
            $ad_list[] = [
                'id' => $ad->id,
                'name' => $ad->name,
                'txt' => $ad->txt,
                'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                'url' => $ad->url,
                'location' => $ad->location->getAttributes(['id', 'height', 'width']),
            ];
        }
        return [
            'user' => [
                'id' => $user->id,
                'prepare_count' => $user->prepare_count,
                'sale_count' => $user->saleCount,
                'hand_count' => $user->hand_count,
            ],
            'ad_list' => $ad_list,
        ];
    }

    /**
     * 店主服务商销售记录
     */
    public function actionSaleLog()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserSaleLog::find();
        $query->andWhere(['uid' => $user->id]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = [];
        foreach ($query
                     ->orderBy('create_time DESC')
                     ->offset($pagination->offset)
                     ->limit($pagination->limit)
                     ->each() as $sale) {
            /** @var UserSaleLog $sale */
            $list[] = [
                'id' => $sale->id,
                'avatar' => $sale->toUser->getRealAvatar(true),
                'goods_title' => $sale->goods->title,
                'real_name' => $sale->toUser->real_name,
                'mobile' => $sale->toUser->mobile,
                'create_time' => $sale->create_time,
            ];
        }

        return [
            'user' => [
                'id' => $user->id,
                'prepare_count' => $user->prepare_count,
                'sale_count' => $user->saleCount,
                'hand_count' => $user->hand_count,
            ],
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 已购礼包列表
     */
    public function actionBuyPackList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = UserBuyPack::find();
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere(['status' => UserBuyPack::STATUS_PAID]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = [];
        foreach ($query
                     ->orderBy('create_time DESC')
                     ->offset($pagination->offset)
                     ->limit($pagination->limit)
                     ->each() as $sale) {
            /** @var UserBuyPack $sale */
            if ($sale->type == UserBuyPack::TYPE_SET_MEAL) {
                $title = '事件（套餐卡购买的礼包）';
            } else {
                $title = '事件（升级卡购买的礼包）';
            }

            $list[] = [
                'id' => $sale->id,
                'title' => $title,
                'type' => $sale->type,
                'no' => $sale->no,
                'money' => $sale->money,
                'amount' => $sale->amount,
                'pack_name' => $sale->pack_name,
                'create_time' => $sale->create_time,
            ];
        }

        return [
            'user' => [
                'id' => $user->id,
                'prepare_count' => $user->prepare_count,
                'sale_count' => $user->saleCount,
                'hand_count' => $user->hand_count,
            ],
            'list' => $list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 用户购买套餐卡
     */
    public function actionPayPackage()
    {
        /** @var User || array $user */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $package = Package::findOne($id);
        if (empty($package) || $package->status != Package::STATUS_SHOW) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到套餐卡信息。',
            ];
        }
        if (Yii::$app->cache->exists('user_package_' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('user_package_' . $user->id, $user->id, 2);
        }

        $money = empty($package->package_price) ? $package->price : $package->package_price;
        //$money = 0.01;
        $pay_method = $this->get('pay_method');
        $trans = Yii::$app->db->beginTransaction();
        try {
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_USER_PACKAGE;
            $financeLog->money = $money;
            $financeLog->pay_method = $pay_method;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            $financeLog->update_time = time();
            $financeLog->remark = '购买套餐卡';
            if (!$financeLog->save()) {
                throw new Exception('无法保存财务记录。');
            }
            $userBuy = new UserBuyPack();
            $userBuy->uid = $user->id;
            $userBuy->fid = $financeLog->id;
            $userBuy->money = $money;
            $userBuy->type = UserBuyPack::TYPE_SET_MEAL;
            $userBuy->pack_name = $package->name;
            $userBuy->amount = $package->count;
            $userBuy->create_time = time();
            $userBuy->status = UserBuyPack::STATUS_CREATED;
            $userBuy->remark = '购买套餐卡' . $package->name;
            if (!$userBuy->save()) {
                throw new Exception('无法保存购买升级卡记录。');
            }
            switch ($pay_method) {
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝App
                    if (System::getConfig('alipay_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_ZFB_APP;
                    if (empty($financeLog->trade_no)) {
                        $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $financeLog->save();
                    $alipay_api = new AlipayApi();
                    $alipay = $alipay_api->AlipayTradeAppPay(System::getConfig('site_name') . '-购买套餐卡', System::getConfig('site_name') . '-购买套餐卡', $financeLog->trade_no, $financeLog->money);
                    $result['alipay'] = $alipay;
                    break;
                case FinanceLog::PAY_METHOD_WX_APP:
                    if (System::getConfig('weixin_app_pay_open') != 1) {
                        throw new Exception('系统没有开通微信APP支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_APP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-购买套餐卡', $financeLog->trade_no, $financeLog->money, 'APP');
                    $result['weixin'] = [
                        'appid' => System::getConfig('weixin_app_app_id'),
                        'partnerid' => System::getConfig('weixin_app_mch_id'),
                        'prepayid' => $prepay_id,
                        'package' => 'Sign=WXPay',
                        'noncestr' => Util::randomStr(32, 7),
                        'timestamp' => time(),
                    ];
                    $result['weixin']['sign'] = $weixin_api->makeSign($result['weixin']);
                    $result['recharge_id'] = $userBuy->id;
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    if (System::getConfig('weixin_mp_pay_open') != 1) {
                        throw new Exception('系统没有开通微信公众号支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_MP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinMpApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-购买套餐卡', $financeLog->trade_no, $financeLog->money, 'JSAPI', $this->get('openid'));
                    $result['weixin'] = [
                        'timeStamp' => time(),
                        'nonceStr' => Util::randomStr(32, 7),
                        'package' => 'prepay_id=' . $prepay_id,
                        'signType' => 'MD5',
                    ];
                    $result['weixin']['paySign'] = $weixin_api->makeSign($result['weixin'], true);
                    $result['recharge_id'] = $userBuy->id;
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
     * 用户购买礼包卡券
     */
    public function actionPayPackageCoupon()
    {
        /** @var User || array $user */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $gid = $this->get('id');
        $pack_coupon = UserPackageCoupon::find()->where(['status'=>UserPackageCoupon::STATUS_OK,'uid' => $user->id])->one();
        if (!empty($pack_coupon) || $user->status != User::STATUS_WAIT) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '仅限未激活会员购买。',
            ];
        }
        $goods=Goods::findOne($gid);
        if(empty($goods) || $goods->is_pack != 1 || $goods->is_pack_redeem!=1)
        {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '礼券商品错误。',
            ];
        }
        //$money = 0.01;
        $pay_method = $this->get('pay_method');
        if(empty($pay_method))
        {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '请选择支付方式。',
            ];
        }
        if (Yii::$app->cache->exists('user_package_coupon_' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('user_package_coupon_' . $user->id, $user->id, 2);
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_ORDER_PAY;
            $financeLog->money = $goods->price;
            $financeLog->pay_method = $pay_method;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            //$financeLog->update_time = time();
            $financeLog->remark = '购买礼包卡券';
            if (!$financeLog->save()) {
                throw new Exception('无法保存财务记录。');
            }

            $order = new Order();
            $order->uid = $user->id;
            $order->fid = $financeLog->id;
            $order->create_time = time();
            $order->status = Order::STATUS_CREATED;
            $order->sid = $goods->shop->id;
            $order->is_pack= 1;
            $order->pack_coupon_status= 1;
            $order->goods_money = $goods->price;
            $order->amount_money = $goods->price;
            if (!$order->save()) {
                throw new Exception('无法保存订单信息。');
            }
            $order_item = new OrderItem();
            $order_item->oid = $order->id;
            $order_item->gid = $goods->id;
            $order_item->title = $goods->title;
            $order_item->amount = 1;
            $order_item->price = $goods->price;
            if (!$order_item->save()) {
                throw new Exception('无法保存订单详情信息。');
            }

            switch ($pay_method) {
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝App
                    if (System::getConfig('alipay_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_ZFB_APP;
                    if (empty($financeLog->trade_no)) {
                        $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $financeLog->save();
                    $alipay_api = new AlipayApi();
                    $alipay = $alipay_api->AlipayTradeAppPay(System::getConfig('site_name') . '-订单', System::getConfig('site_name') . '-订单', $financeLog->trade_no, $financeLog->money);
                    $result['alipay'] = $alipay;
                    $result['no'] = $order->no;
                    break;
                case FinanceLog::PAY_METHOD_WX_APP:
                    if (System::getConfig('weixin_app_pay_open') != 1) {
                        throw new Exception('系统没有开通微信APP支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_APP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $financeLog->trade_no, $financeLog->money, 'APP');
                    $result['weixin'] = [
                        'appid' => System::getConfig('weixin_app_app_id'),
                        'partnerid' => System::getConfig('weixin_app_mch_id'),
                        'prepayid' => $prepay_id,
                        'package' => 'Sign=WXPay',
                        'noncestr' => Util::randomStr(32, 7),
                        'timestamp' => time(),
                    ];
                    $result['weixin']['sign'] = $weixin_api->makeSign($result['weixin']);
                    $result['no'] = $order->no;
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    if (System::getConfig('weixin_mp_pay_open') != 1) {
                        throw new Exception('系统没有开通微信公众号支付。', ErrorCode::SERVER);
                    }
                    $financeLog->pay_method = FinanceLog::PAY_METHOD_WX_MP;
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $financeLog->save();
                    $weixin_api = new WeixinMpApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $financeLog->trade_no, $financeLog->money, 'JSAPI', $this->get('openid'));
                    $result['weixin'] = [
                        'timeStamp' => time(),
                        'nonceStr' => Util::randomStr(32, 7),
                        'package' => 'prepay_id=' . $prepay_id,
                        'signType' => 'MD5',
                    ];
                    $result['weixin']['paySign'] = $weixin_api->makeSign($result['weixin'], true);
                    $result['no'] = $order->no;
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
     * 礼包卡券列表
     */
    public function actionPackageCoupon()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $status=$this->get('status');
        if(empty($status) && $status !=0)
        {
            $status=UserPackageCoupon::STATUS_OK;
        }

        $query = UserPackageCoupon::find();
        $query->andWhere(['uid' => $user->id]);
        $query->andWhere(['status' => $status]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $list = [];
        foreach ($query
                     ->orderBy('create_time DESC')
                     ->offset($pagination->offset)
                     ->limit($pagination->limit)
                     ->each() as $coupon) {
            /** @var UserPackageCoupon $coupon */
            $list[] = [
                'id' => $coupon->id,
                'status' =>$coupon->status,
                'name' => System::getConfig('pack_redeem_name'),
                'desc' => System::getConfig('pack_redeem_desc'),
                'over_time' => date('Y-m-d',$coupon->over_time),
            ];
        }

        return [
            'list' => $list,
            'url' => Yii::$app->params['site_host'].'/h5/goods/pack-coupon-view',
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }
    /**
     * 用户选择礼包卡券兑换大礼包操作
     * POST
     * {
     *      gid， 礼包商品编号
     *      sku_id, 规格编号
     *      address_id, 地址编号
     * }
     */
    public function actionRedeemPackage()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $json = $this->checkJson([
            [['gid'], 'required', 'message' => '缺少必要参数商品编号。'],
        ]);

        if (isset($json['error_code'])) {
            return $json;
        }
        $pack_coupon=UserPackageCoupon::find()->where(['status' => UserPackageCoupon::STATUS_OK,'uid' => $user->id])->one();
        /** @var $pack_coupon UserPackageCoupon*/
        if (empty($pack_coupon)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有可用的礼包兑换券。',
            ];
        }
        $UserAddressCount = UserAddress::find()->where(['uid' => $user->id, 'status' => UserAddress::STATUS_OK])->count();
        if ($UserAddressCount <= 0) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '还没有设置收货地址。',
            ];
        }

        if (isset($json['address_id'])) {
            /** @var UserAddress $address */
            $address = UserAddress::findOne($json['address_id']);
        } else {
            /** @var UserAddress $address */
            $address = UserAddress::find()
                ->andWhere(['uid' => $user->id, 'status' => UserAddress::STATUS_OK])
                ->orderBy('is_default desc')
                ->one();
        }

        if (empty($address)) {
            return [
                'error_code' => ErrorCode::ORDER_NO_ADDRESS,
                'message' => '没有选择收货地址。',
            ];
        }
        //下单
        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $json['gid'], 'status' => Goods::STATUS_ON])->one();
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有商品或者商品已经下架。',
            ];
        }
        if (GoodsSku::find()->where(['gid' => $json['gid']])->exists() && empty($json['sku_id'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '该商品必须选择规格请检查。',
            ];
        }

        if (Yii::$app->cache->exists('user_redeem_package_' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('user_redeem_package_' . $user->id, $user->id, 2);
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
                //todo 生成订单
                $order = new Order();
                $order->uid = $user->id;
                $order->deliver_info = json_encode([
                    'area' => $address->area,
                    'address' => $address->address,
                    'name' => $address->name,
                    'mobile' => $address->mobile,
                ]);
                $order->create_time = time();
                $order->status = Order::STATUS_PAID;
                $order->sid = $goods->sid;
                $order->is_pack=1;
                $order->pack_coupon_status=2;//礼包兑换订单
                $order->amount_money = 0; // 需要生成订单详情后再更新订单
                $order->user_remark = ''; // 订单附言
                if (!$order->save()) {
                    throw new Exception('无法保存订单信息。');
                }


                /** @var GoodsSku $sku */
                $sku = null;
                if (!empty($json['sku_id'])) {
                    $sku = GoodsSku::findOne($json['sku_id']);
                }
                $order_item = new OrderItem();
                $order_item->oid = $order->id;
                $order_item->gid = $goods->id;
                $order_item->title = $goods->title;
                $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
                $order_item->amount = 1;
                $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
                if (!$order_item->save()) {
                    throw new Exception('无法保存订单详情信息。');
                }
                /** @var OrderDeliver $order_deliver */
                $order_deliver = new OrderDeliver();
                $order_deliver->oid = $order->id;
                $order_deliver->create_time = time();
                $order_deliver->status = OrderDeliver::STATUS_WAIT;
                if (!empty($goods->supplier_id) && $goods->sale_type == Goods::TYPE_SUPPLIER) {
                    $order_deliver->supplier_id = $goods->supplier_id;
                }
                if (!$order_deliver->save()) {
                    Yii::warning($order_deliver->getErrors());
                    throw new Exception('发货单生成失败。');
                }
                /** @var OrderDeliverItem $order_deliver_item */
                $order_deliver_item = new OrderDeliverItem();
                $order_deliver_item->did = $order_deliver->id;
                $order_deliver_item->oiid = $order_item->id;
                $order_deliver_item->amount = 1;
                if (!$order_deliver_item->save()) {
                    Yii::warning($order_deliver_item->getErrors());
                    throw new Exception('发货单生成失败。');
                }
                $order->status = Order::STATUS_PACKED;
                $order->goods_money = $order_item->price * $order_item->amount;
                $order->amount_money = $order_item->price * $order_item->amount;


                if (!$order->save()) {
                    Yii::warning($order->getErrors());
                    throw new Exception('金额小于0， 无法更新订单金额。');
                }
                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '创建订单。', print_r($this->get(), true));
                //礼包卡券状态更新
                $pack_coupon->status=UserPackageCoupon::STATUS_USED;
                $pack_coupon->use_oid=$order->id;
                if (!$pack_coupon->save()) {
                    Yii::warning($pack_coupon->getErrors());
                    throw new Exception('无法更新礼包卡券状态');
                }

                OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '用户兑换礼包商品。');

            $trans->commit();
            return [
                'order' => ['no' => $order->no],
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '生成订单错误：' . $e->getMessage(),
            ];
        }
    }
    /**
     * 地推活动详情
     */
    public function actionGroundPush()
    {
        /** @var User || array $user */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        if (System::getConfig('ground_push_active_switch') == 0) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有活动。',
            ];
        }
        $id = $this->get('id');
        /** @var Goods $goods_gid */
        $goods_gid = Goods::find()->where(['is_coupon' => 1, 'status' => Goods::STATUS_ON])->one();
        if (empty($goods_gid)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有活动。',
            ];
        }

        /** @var GoodsCouponRule $coupon */
        //$coupon = GoodsCouponRule::findOne($id);
        $coupon = GoodsCouponRule::find()->where(['gid' => $goods_gid->id])->one();
        if (empty($coupon)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有活动。',
            ];
        }
        $coupon_list = [];

        $count = GoodsCouponGiftUser::find()->where(['and',['uid' => $user->id],['gid' =>  $goods_gid->id]])
            ->andWhere(['status' => GoodsCouponGiftUser::STATUS_USED])
            ->orderBy('create_time desc')
            ->limit($coupon->count)->count();
        $no_use_count = GoodsCouponGiftUser::find()->where(['uid' => $user->id])
            ->andWhere(['IN', 'status' , [GoodsCouponGiftUser::STATUS_WAIT, GoodsCouponGiftUser::STATUS_LOCK]])
            ->orderBy('create_time desc')
            ->limit($coupon->count)->count();
        if ($count != 0 && ($count % $coupon->count) != 0 || $no_use_count > 0) {
            /** @var GoodsCouponGiftUser $user_coupon */
            foreach (GoodsCouponGiftUser::find()->where(['and',['uid' => $user->id],['gid' =>  $goods_gid->id]])
                         ->orderBy('create_time desc')
                         ->limit($coupon->count)->each() as $user_coupon) {
                $coupon_list[] = [
                    'name' => $coupon->name,
                    'price' => Util::convertPrice($coupon->price),
                    'create_time' => $user_coupon->create_time,
                    'status' => $user_coupon->status,
                    'status_str' => KeyMap::getValue('goods_coupon_gift_user_status', $user_coupon->status),
                ];
            }
        }
        /** @var Goods $goods */
        $goods = Goods::find()->where(['is_coupon' => 1, 'status' => Goods::STATUS_ON, 'id' => $coupon->gid])->one();
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '商品信息不存在。',
            ];
        }
        $coupon_gift_list = [];
        if ($coupon->status == 1 && $no_use_count<=1) {
            foreach (GoodsCouponGift::find()->where(['status' => GoodsCouponGift::STATUS_OK])->each() as $gift) {
                $coupon_gift_list[] = [
                    'id' => $gift->id,
                    'name' => $gift->name,
                    'pic' => Util::fileUrl($gift->pic),
                ];
            }
        }
        return [
            'title' => System::getConfig('ground_push_active_name'),
            'rule' => ['name' => $coupon->name, 'price' => Util::convertPrice($coupon->price), 'count' => $coupon->count],
            'coupon_list' => $coupon_list,
            'main_pic' => Util::fileUrl($goods->main_pic),
            'gid' => $goods->id,
            'gif_is_show' => empty($coupon_gift_list) ? 0 : 1,
            'coupon_gift_list' => $coupon_gift_list,
            'web_view_url' => System::getConfig('ground_push_active_webview_url'),
        ];
    }

    /**
     * 绑定微信union_id
     * POST
     * {
     *      union_id,
     *      open_id,
     * }
     */
    public function actionBindUnionId()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['open_id', 'union_id'], 'required', 'message' => '缺少必要参数union_id。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $exist = UserWeixin::find()->where(['open_id' => $json['open_id'], 'union_id' => $json['union_id']])->count();
        if ($exist > 0) {
            return [
                'error_code' => ErrorCode::USER_UNION_ID_EXIST,
                'message' => '微信union_id已经存在。',
            ];
        }
        $exist2 = UserWeixin::find()->where(['union_id' => $json['union_id']])->count();
        if ($exist2 > 0) {
            return [
                'error_code' => ErrorCode::USER_UNION_ID_EXIST,
                'message' => '微信union_id已经存在。',
            ];
        }
        /** @var UserWeixin $userWeiXin */
        $userWeiXin = UserWeixin::find()->where(['uid' => $user->id])->one();
        if (!empty($userWeiXin->union_id)) {
            return [
                'error_code' => ErrorCode::USER_UNION_ID_EXIST,
                'message' => '微信union_id已经存在。',
            ];
        }
        if (!empty($userWeiXin) && empty($userWeiXin->union_id)) {
            $userWeiXin->union_id = $json['union_id'];
            $userWeiXin->save();
        }
        if (empty($userWeiXin)) {
            $userWeiNew = new UserWeixin();
            $userWeiNew->uid = $user->id;
            $userWeiNew->union_id = $json['union_id'];
            $userWeiNew->open_id = $json['open_id'];
            $userWeiNew->create_time = time();
            $userWeiNew->app_id = System::getConfig('weixin_mp_app_id');
            if (!$userWeiNew->save()) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '绑定微信失败。',
                ];
            }

        }
        return [];
    }
    /**
     *商学院分类列表 针对用户判断是否有新消息
     * GET
     *   cid // 分类
     * }
     */
    public function actionSchoolCat()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $school_cat_list[]=[
            'cid'=>0,
            'name'=>'新手入门',
            'type'=>'news',
            'status'=>0,
        ];
        $video_cat_sort=[];
        $source_cat=KeyMap::getValues('goods_source_type');
        krsort($source_cat); //按键名降序 排序
        $video_cat=KeyMap::getValues('goods_trace_video_type');
       // $sort=[3,2,4,1];//自定义
        $sort=[3,2,4];//自定义
        foreach ($sort as $v)
        {
            $video_cat_sort[$v]= $video_cat[$v];
        }
        $video_cat=$video_cat_sort;

        foreach ($source_cat as  $k=>$v)
        {
            $user_cat_status=0;
            $new_source=GoodsSource::find()->where(['status'=>1,'cid'=>$k])->andWhere(['<=','start_time',time()])->orderBy('id desc')->one();//查找最新一条
            $source_res=UserNewschCat::find()->where(['uid'=>$user->id,'cid'=>$k,'type'=>'img'])->one();
            /** @var $new_source  GoodsSource */
            /** @var $source_res  UserNewschCat */
            if(!empty($source_res) && !empty($new_source))
            {
                if($new_source->create_time>$source_res->read_time)
                {
                    $user_cat_status=1;
                }
            }
            if(empty($source_res) && !empty($new_source))
            {
                $user_cat_status=1;
            }

            $school_cat_list[]=[
                'cid'=>$k,
                'name'=>$v,
                'type'=>'img',
                'status'=> $user_cat_status,
            ];
        }
        foreach ($video_cat as  $k=>$v)
        {
            $user_cat_status=0;
            $query=GoodsTraceVideo::find();
            $query->where(['status'=>1,'cid'=>$k]);
            $query->andWhere(['<=','start_time',time()]);
            $query->orderBy('id desc');
            $new_video=$query->one();//查找最新一条
            $video_res=UserNewschCat::find()->where(['uid'=>$user->id,'cid'=>$k,'type'=>'video'])->one();

            /** @var $new_video  GoodsTraceVideo */
            /** @var $video_res  UserNewschCat */
            if(!empty($video_res) && !empty($new_video))
            {
                if($new_video->create_time>$video_res->read_time)
                {
                    $user_cat_status=1;
                }
            }
            if(empty($video_res) && !empty($new_video))
            {
                $user_cat_status=1;
            }

            $school_cat_list[]=[
                'cid'=>$k,
                'name'=>$v,
                'type'=>'video',
                'status'=> $user_cat_status,
            ];
        }

        /** 新字图广告 */
        $new_pic_list = [];
        $AdLocation=AdLocation::findOne(37);
        if(!empty($AdLocation)){
            foreach ($AdLocation->getActiveAdList()->each() as $ad) {
                /** @var Ad $ad */
                $new_pic_list[] = [
                        'id' => $ad->id,
                        'name' => $ad->name,
                        'txt' => $ad->txt,
                        'img' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $ad->img,
                        'url' => $ad->url,
                        'location' => $ad->location->getAttributes(['id', 'height', 'width']),
                    ];

            }
        }

        return[
            'new_pic_list' => $new_pic_list,
            'list'=>$school_cat_list,
        ];
    }
    /**
     * 检查用户是否签到 0未签到 1已签到
     */
    public function actionCheckSign()
    {
        $is_sign = 0;
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        Yii::$app->cache->delete('sign_' . $user->id);
        if (Yii::$app->cache->exists('sign_' . $user->id)) {
            $now = time();
            $set_time = Yii::$app->cache->get('sign_' . $user->id);
            if ($now > $set_time) {
                $is_sign = 0;
            } else {
                $is_sign = 1;
            }
        } else {
            $log_count_day = UserScoreLog::find()
                ->where(['uid' => $user->id , 'code' => UserScoreLog::SIGN])
                ->andWhere(['>', 'create_time', strtotime(date("Y-m-d"), time())])
                ->andWhere(['<=', 'create_time', time()])
                ->count();
            if ($log_count_day > 0) {
                $is_sign = 1;
            }
        }
        //$is_sign = 0;
        return [
            'is_sign' => $is_sign
        ];
    }

    /**
     * 用户签到
     */
    public function actionSign()
    {
        /** @var $user User */
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        if (Yii::$app->cache->exists('check_user_sign_' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('check_user_sign_' . $user->id, $user->id, 2);
        }

        $prize_arr = array(1 => 80, 2 => 12, 3 => 5, 4 => 2, 5 => 1);//积分概率设置 1积分=>80%

        //概率数组的总概率精度
        $proSum = array_sum($prize_arr);

        //概率数组循环
        $score = 1;
        foreach ($prize_arr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);//抽取随机数
            if ($randNum <= $proCur) {
                $score = $key;//得出结果
                break;
            } else {
                $proSum -= $proCur;
            }
        }

        $today_log = UserScoreLog::find()->where(['>', 'create_time', strtotime(date('Y-m-d'))])->andWhere(['uid' => $user->id , 'code' => UserScoreLog::SIGN])->exists();
        if (Yii::$app->params['site_host'] == 'http://yuntaobang.ysjjmall.com') {
            $test_user = [2781, 2783, 2784, 2785, 2786, 2787, 2788, 2789, 4543,2458,2464,2553,2613,2732,2733,2750,2879,
                2998,3147,3188,3207,3320,3364,3372,3410,3421,3487,3491,3597,3683,3723,3770,3783,3792,3823,3850,3851,3949,
                3979,4106,4492,4493,4494,4495,4496,4497,4498,4499,4500,4501,4502,4503,4504,4505,4506,4507,4508,4509,4510,
                4511,4512,4513,4514,4515,4516,4517,4518,4519,4520,4521,4522,4523,4524,4525,4527,4528,4529,4530,4531,4532,4533];
            if (in_array($user->id, $test_user)) {
                $today_log = false;
            }
        }
        if ($today_log) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '无法再次签到咯',
            ];
        } else {

            $result = $user->addScore($score, UserScoreLog::SIGN, '用户签到');
            if (!$result) {
                return [
                    'error_code' => ErrorCode::NO_RESULT,
                    'message' => '签到失败',
                ];
            } else {
                //判断是否 当月最后一天 并且全月签到 如果是 发放签到奖励
                $check = UserScoreLog::checkSignReword($user->id);
                if ($check) {
                    $sign_reword_score = System::getConfig('sign_reword_score');
                    $result = $user->addScore($sign_reword_score, UserScoreLog::SIGN_REWORD, '用户连续签到奖励');
                    if (!$result) {
                        return [
                            'error_code' => ErrorCode::NO_RESULT,
                            'message' => '签到奖励发放失败',
                        ];
                    }
                    $message = new UserMessage();
                    $id = $message->MessageSend($user->id, '恭喜您本月您连续签到达到满勤，奖励您' . $sign_reword_score . '积分，感谢您的支持。', Yii::$app->params['site_host'] . '/h5/notice/umview', '恭喜您本月您连续签到达到满勤，奖励您' . $sign_reword_score . '积分，感谢您的支持。');
                    if ($id) {
                        $message = UserMessage::findOne($id);
                        $message->url = Yii::$app->params['site_host'] . '/h5/notice/umview?id=' . $id . '&app=1';
                        if (!$message->save(false)) {
                            Yii::warning('更新发放连续奖励通知记录失败。');
                        }
                    }
                }
            }

            Yii::$app->cache->set('sign_' . $user->id, strtotime(date("Y-m-d 23:59:59", time())));//写入签到当天最大日期

        }
        return [
            'score' => $score,
            'is_sign_reword' => $check ? 1 : 0,
            'sign_reword_score' => System::getConfig('sign_reword_score')
        ];

    }

    /**
     * 订单数字统计
     * @return array|object
     */
    public function actionOrderCount()
    {
        /** @var $user User*/
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $count = [];
        $query = Order::find();
        $query->andWhere(['uid' => $user->id]);
        $query_wait = clone $query;
        $count['wait_send'] = $query_wait->andWhere(['status' => Order::STATUS_PACKED])->count();
        $query_wait_receive= clone $query;
        $count['wait_receive'] = $query_wait_receive->andWhere(['status' => Order::STATUS_DELIVERED])->count();
        $query_receive = clone $query;
        $count['receive'] = $query_receive->andWhere(['or',['status' => Order::STATUS_RECEIVED],['status' => Order::STATUS_COMPLETE]])->count();
//        $count['refund'] = OrderRefund::find()->joinWith('order_item as order')
//                            ->andWhere(['order.status' => Order::STATUS_AFTER_SALE])
//                            ->andWhere(['order.uid' => $user->id])
//                            ->count();
        $query_order = Order::find();
        $item = $query_order->joinWith('itemList')
            ->where(['uid' => $user->id])
            ->andWhere(['<>', 'status', Order::STATUS_DELETE])
            ->select('{{%order_item}}.id')
            ->all();
        $oiid = ArrayHelper::getColumn($item, 'id');
        $query_refund = OrderRefund::find();
        $query_refund->where(['in', 'oiid' , $oiid]);
        $query_refund->andWhere(['<>', 'status', OrderRefund::STATUS_DELETE]);
        /** @var OrderRefund[] $list */
        $count['refund'] = $query_refund->count();
        return [
            'count' => [$count]
        ];
    }

    /**
     * 检测用户是否有兑换券
     * @return object | array
     */
    public function actionCheckPackageCoupon()
    {
        /** @var $user User*/
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        if (UserPackageCoupon::find()->where(['uid' => $user->id, 'status' => UserPackageCoupon::STATUS_OK])->exists()) {
            $is_have = 1;
        } else {
            $is_have = 0;
        }
        return [
            'is_have' => $is_have
        ];
    }

    /**
     * 前台兑换大礼包操作
     * POST
     * {
     *      gid， 礼包商品编号
     *      sku_id, 规格编号
     *      address_id, 收货地址编号
     * }
     */
    public function actionPackageCouponExchange()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        /** @var UserPackageCoupon $package */
        $package = UserPackageCoupon::find()->where(['uid' => $user->id, 'status' => UserPackageCoupon::STATUS_OK])->one();
        if (empty($package)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到礼包礼券。',
            ];
        }
        $json = $this->checkJson([
            [['address_id', 'gid'], 'required', 'message' => '缺少必要参数收货地址或者商品编号。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        if (Yii::$app->cache->exists('user_package' . $user->id)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了。',
            ];
        } else {
            Yii::$app->cache->set('user_package' . $user->id, $user->id, 2);
        }
        /** @var UserAddress $address */
        $address = UserAddress::find()->where(['id' => $json['address_id'], 'uid' => $user->id, 'status' => UserAddress::STATUS_OK])->one();
        if (empty($address)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到收货地址。',
            ];
        }
        /** @var Goods $goods */
        $goods = Goods::find()->where(['id' => $json['gid'], 'status' => Goods::STATUS_ON])->one();
        if (empty($goods)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '没有商品或者商品已经下架。',
            ];
        }
        if (GoodsSku::find()->where(['gid' => $json['gid']])->exists() && empty($json['sku_id'])) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '该商品必须选择规格请检查。',
            ];
        }
        if (!GoodsSku::find()->where(['gid' => $json['gid'], 'id' => $json['sku_id']])->exists()) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '该商品选择规格不存在请检查。',
            ];
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            $order = new Order();
            $order->uid = $user->id;
            $order->deliver_info = json_encode([
                'area' => $address->area,
                'address' => $address->address,
                'name' => $address->name,
                'mobile' => $address->mobile,
            ]);
            $order->create_time = time();
            $order->status = Order::STATUS_PACKED;
            $order->sid = $goods->sid;
            $order->amount_money = 0; // 需要生成订单详情后再更新订单
            $order->user_remark = ''; // 订单附言
            if (!$order->save()) {
                throw new Exception('无法保存订单信息。');
            }

            /** @var GoodsSku $sku */
            $sku = null;
            if (!empty($json['sku_id'])) {
                $sku = GoodsSku::findOne($json['sku_id']);
            }
            $order_item = new OrderItem();
            $order_item->oid = $order->id;
            $order_item->gid = $goods->id;
            $order_item->title = $goods->title;
            $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
            $order_item->amount = 1;
            $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
            if (!$order_item->save()) {
                throw new Exception('无法保存订单详情信息。');
            }
            $order->goods_money = $order_item->price * $order_item->amount;
            $order->amount_money = $order_item->price * $order_item->amount;
            if (!$order->save()) {
                Yii::warning($order->getErrors());
                throw new Exception('金额小于0， 无法更新订单金额。');
            }
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '创建订单。', print_r($this->get(), true));

            /** @var OrderDeliver $order_deliver */
            $order_deliver = new OrderDeliver();
            $order_deliver->oid = $order->id;
            $order_deliver->create_time = time();
            $order_deliver->status = OrderDeliver::STATUS_WAIT;
            if (!empty($goods->supplier_id) && $goods->sale_type == Goods::TYPE_SUPPLIER) {
                $order_deliver->supplier_id = $goods->supplier_id;
            }
            if (!$order_deliver->save()) {
                Yii::warning($order_deliver->getErrors());
                throw new Exception('发货单生成失败。');
            }
            /** @var OrderDeliverItem $order_deliver_item */
            $order_deliver_item = new OrderDeliverItem();
            $order_deliver_item->did = $order_deliver->id;
            $order_deliver_item->oiid = $order_item->id;
            $order_deliver_item->amount = 1;
            if (!$order_deliver_item->save()) {
                Yii::warning($order_deliver_item->getErrors());
                throw new Exception('发货单生成失败。');
            }

            $order->status = Order::STATUS_PACKED;
            if (!$order->save()) {
                Yii::warning($order->getErrors());
                throw new Exception('金额小于0， 无法更新订单金额。');
            }
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '礼包券兑换礼包商品。', print_r(['content' => 'gid' . $json['gid']. ',收货地址'. $json['address_id']], true));

            $package->status = UserPackageCoupon::STATUS_USED;
            $package->use_oid = $order->id;
            if (!$package->save()) {
                throw new Exception('礼包券保存失败。');
            }
            $trans->commit();
            return [
                //'order' => ['no' => $order->no],
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '生成订单错误：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 阅读公告
     */
    public function actionReadNotice()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $notice = Notice::findOne($id);
        if (empty($notice)) {
            return [
                'error_code' => ErrorCode::GOODS_NOT_FOUND,
                'message' => '公告不存在。',
            ];
        }
        $user_notice = UserNotice::find()->where(['uid' => $user->id, 'nid' => $id])->one();
        if (!empty($user_notice)) {
            return [
                'error_code' => ErrorCode::USER_FAV_EXIST,
                'message' => '公告已阅读。',
            ];
        }
        $model = new UserNotice();
        $model->uid = $user->id;
        $model->nid = $id;
        $model->create_time = time();
        if (!$model->save()) {
            return [
                'error_code' => ErrorCode::USER_FAV_SAVE,
                'message' => '保存阅读失败。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }
}
