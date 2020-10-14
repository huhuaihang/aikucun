<?php

namespace app\modules\h5\controllers;

use app\models\WeixinMpApi;
use Yii;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\Response;

/**
 * 用户中心
 * Class UserController
 * @package app\modules\h5\controllers
 */
class UserController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest && !$this->isAjax()) {
            $invite_code = $this->get('invite_code');
            if (!empty($invite_code)) {
                $invite_code_cookie = new Cookie();
                $invite_code_cookie->name = 'invite_code';
                $invite_code_cookie->value = $invite_code;
                Yii::$app->response->cookies->add($invite_code_cookie);
            }
            Yii::$app->user->loginRequired();
            return false;
        }
        return parent::beforeAction($action);
    }

    /**
     * 用户激活账号
     * @return string
     */
    public function actionActive()
    {
        return $this->render('active');
    }

    /**
     * 获取用户open_id
     * GET
     */
    public function actionGetOpen()
    {
        $code = $this->get('code');
        //通过code获得openid
        if (!isset($code)) {
            $invite_code = $this->get('invite_code');
            $url = Yii::$app->params['site_host'] . '/h5/login';
            if (isset($invite_code)) {
                $url .= '?invite_code=' . $invite_code;
            }
            //触发微信返回code码
            $api = new WeixinMpApi();
            $url = $api->codeUrl($url, 'snsapi_base');
            return ['url' => $url];
            Header("Location: $url"); // 跳转到微信授权页面 需要用户确认登录的页面
            exit();
        } else {
            //上面获取到code后这里跳转回来
            $api = new WeixinMpApi();
            $result = $api->code2Openid($code);
            return ['open_id' => $result];
        }
    }

    /**
     * 用户中心
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * 用户退出
     * @return \yii\web\Response | string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->render('logout');
        //return $this->redirect(['/h5/user']);
    }

    public function actionClear()
    {
        Yii::$app->cache->flush();
    }


    /**
     * 用户设置
     * @return string
     */
    public function actionProfile()
    {
        return $this->render('profile');
    }

    /**
     * 订单列表
     * @return string
     */
    public function actionOrder()
    {
        return $this->render('order');
    }

    /**
     * 用户收藏商品
     * @return string
     */
    public function actionFavGoods()
    {
        return $this->render('fav_goods');
    }

    /**
     * 用户收藏店铺
     * @return string
     */
    public function actionFavShop()
    {
        return $this->render('fav_shop');
    }

    /**
     * 用户积分
     * @return string
     */
    public function actionScore()
    {
        return $this->render('score');
    }

    /**
     * 用户佣金
     * @return string|Response
     */
    public function actionCommissionList()
    {
        return $this->render('commission_list');
    }

    /**
     * 用户佣金提现列表
     * @return string|Response
     */
    public function actionWithdrawList()
    {
        return $this->render('withdraw_list');
    }

    /**
     * 提现方式
     * @return string|Response
     */
    public function actionWithdrawMethod()
    {
        return $this->render('withdraw_method');
    }

    /**
     * 提现到支付宝
     */
    public function actionWithdrawToZfb()
    {
        return $this->render('withdraw_to_zfb');
    }

    /**
     * 提现到银行卡
     */
    public function actionWithdrawToBank()
    {
        return $this->render('withdraw_to_bank');
    }

    /**
     * 提现到已有的账户
     * GET
     * bank_id 用户绑定银行卡编号
     */
    public function actionWithdraw()
    {
        return $this->render('withdraw');
    }

    /**
     * 提现进度
     */
    public function actionWithdrawDetail()
    {
        return $this->render('withdraw_detail');
    }

    /**
     * 现金账户
     * @return string
     */
    public function actionMoneyAccount()
    {
        return $this->render('money_account');
    }

    /**
     * 用户地址列表
     * @return string
     */
    public function actionAddress()
    {
        return $this->render('address');
    }

    /**
     * 添加修改用户地址
     * @return string
     */
    public function actionEditAddress()
    {
        return $this->render('address_edit');
    }

    /**
     * 绑定手机
     * @return string|\yii\web\Response
     */
    public function actionMobile()
    {
        return $this->render('mobile');
    }

    /**
     * 绑定手机
     * @return string|\yii\web\Response
     */
    public function actionChangeMobile()
    {
        return $this->render('change_mobile');
    }

    /**
     * 设置新密码
     * @return string|\yii\web\Response
     */
    public function actionPassword()
    {
        return $this->render('password');

    }

    /**
     * 设置支付密码
     * @return string|\yii\web\Response
     */
    public function actionPaymentPassword()
    {
        return $this->render('payment_password');
    }

    /**
     * 客服中心
     * @return string
     */
    public function actionServiceCenter()
    {
        return $this->render('service_center');
    }

    /**
     * 我的代理
     * @return string
     */
    public function actionMyAgent()
    {
        return $this->render('my_agent');
    }

    /**
     * 推荐列表
     * @return string
     */
    public function actionRecommendList()
    {
        return $this->render('recommend_list');
    }

    /**
     * 会员等级
     * @return string
     */
    public function actionLevelInfo()
    {
        return $this->render('level_info');
    }
    /**
     * VIP页面
     * @return string
     */
    public function actionVip()
    {
        return $this->render('vip');
    }
    /**
     * 充值(进货)金额列表
     * @return string
     */
    public function actionRechargeValues()
    {
        return $this->render('recharge_values');
    }

    /**
     * 充值(进货)记录
     * @return string
     */
    public function actionRechargeList()
    {
        return $this->render('recharge_list');
    }

    /**
     * 充值(进货)状态
     * @return string
     */
    public function actionRechargeStatus()
    {
        return $this->render('recharge_status');
    }

    /**
     * 分享
     * @return string
     */
    public function actionShare()
    {
        return $this->render('share');
    }

    /**
     * 推荐二维码
     * @return string
     */
    public function actionRecommendQrCode()
    {
        return $this->render('recommend_qr_code');
    }

    /**
     * 商品分享海报
     * @return string
     */
    public function actionGoodsRecommendQrCode()
    {
        return $this->render('goods_recommend_qr_code');
    }


    /**
     * 会员礼包分享海报
     * @return string
     */
    public function actionPackRecommendQrCode()
    {
        return $this->render('pack_recommend_qr_code');
    }

    /**
     * 充值方式
     * @return string
     */
    public function actionRechargeMethod()
    {
        $money = $this->get('money');
        return $this->render('recharge_method', [
            'money' => $money,
        ]);
    }

    /**
     * 我的店铺
     */
    public function actionShop()
    {
        return $this->render('shop');
    }

    /**
     * 我要卖
     */
    public function actionSale()
    {
        return $this->render('sale');
    }
    /**
     * 套餐礼包
     */
    public function actionSalePack()
    {
        return $this->render('sale_pack');
    }
    /**
     * 套餐卡确认页面
     */
    public function actionPackageConfirm()
    {
        return $this->render('package_confirm');
    }

    /**
     * 套餐卡支付回调页面
     */
    public function actionPackageCallback()
    {
        return $this->render('package_callback');
    }
    /**
     * 升级卡支付确认页面
     */
    public function actionUpgradeConfirm()
    {
        return $this->render('upgrade_confirm');
    }

    /**
     * 升级卡支付回调页面
     */
    public function actionUpgradeCallback()
    {
        return $this->render('upgrade_callback');
    }

    /**
     * 我的团队列表
     */
    public function actionTeamList()
    {
        return $this->render('team_list');
    }

    /**
     * 我的礼包卡券
     */
    public function actionPackCoupon()
    {
        return $this->render('pack_coupon');
    }
    /**
     * 地推优惠券活动
     * @return string
     */
    public function actionGroudPush()
    {
        return $this->render('groud_push');
    }

}
