<?php

namespace app\modules\api\controllers;

use app\models\FinanceLog;
use app\models\System;
use Yii;

/**
 * 支付宝
 * Class AlipayControllet
 * @package app\modules\api\controllers
 */
class AlipayController extends \app\controllers\BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * 回调地址
     * @throws \yii\base\Exception
     */
    public function actionNotify()
    {
        $raw = file_get_contents('php://input');

        Yii::warning($raw, 'alipay');

        parse_str($raw, $post);
        $sign = $post['sign'];
        $signType = $post['sign_type'];
        unset($post['sign']);
        unset($post['sign_type']);

        include_once Yii::getAlias('@app/components/alipay/AopSdk.php');
        $aop = new \AopClient();
        $aop->appId = System::getConfig('alipay_app_id');
        $aop->rsaPrivateKey = System::getConfig('alipay_private_key');
        $aop->alipayrsaPublicKey = System::getConfig('alipay_public_key');
        $checkSign = $aop->verify($aop->getSignContent($post), $sign, System::getConfig('alipay_public_key'), $signType);
//        if (!$checkSign) {
//            Yii::error('支付宝异步通知签名验证失败：' . $raw);
//            return 'sign_error';
//        }
        $trade_status = $post['trade_status'];
        if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED') {
            $r = FinanceLog::payNotify($post['out_trade_no'], $post['total_amount'], FinanceLog::STATUS_SUCCESS, $raw);
        } elseif ($trade_status == 'TRADE_CLOSED') {
            /** @var FinanceLog $financeLog */
            $financeLog = FinanceLog::find()->where(['trade_no' => $post['out_trade_no']])->one();
            if (!empty($financeLog) && $financeLog->pay_method == FinanceLog::PAY_METHOD_ZFB_APP) {
                $r = FinanceLog::payNotify($post['out_trade_no'], $post['total_amount'], FinanceLog::STATUS_CLOSED, $raw);
            }
        } else {
            Yii::warning('支付宝回调通知无法确定支付状态。');
            $r = false;
        }
        if (!$r) {
            return 'server_error';
        }
        return 'success';
    }
}
