<?php

namespace app\modules\api\controllers;

use app\models\FinanceLog;
use app\models\WeixinAppApi;
use app\models\WeixinH5Api;
use app\models\WeixinMpApi;
use Yii;
use yii\base\Exception;

/**
 * 微信
 * Class WeixinController
 * @package app\modules\api\controllers
 */
class WeixinController extends \app\controllers\BaseController
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
     * 扫码和APP支付回调地址
     */
    public function actionAppNotify()
    {
        return $this->notify(new WeixinAppApi());
    }

    /**
     * 公众号支付回调地址
     */
    public function actionMpNotify()
    {
       return $this->notify(new WeixinMpApi());
    }

    /**
     * H5支付回调地址
     */
    public function actionH5Notify()
    {
       return $this->notify(new WeixinH5Api());
    }

    /**
     * 回调通知
     * @param WeixinAppApi|WeixinMpApi|WeixinH5Api $api
     * @return string
     */
    private function notify($api)
    {
        $raw = file_get_contents('php://input');

        Yii::warning($raw, 'weixin');

        if (empty($raw)) {
            return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[参数错误]]></return_msg></xml>';
        }
        libxml_disable_entity_loader(true);
        $xml = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml = (array)$xml;
        $sign = $xml['sign'];
        unset($xml['sign']);
        if ($api->makeSign($xml) != $sign) {
            return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
        }
        $out_trade_no = $xml['out_trade_no'];
        $money = $xml['cash_fee'] / 100;
        if ($xml['return_code'] == 'SUCCESS' && $xml['result_code'] == 'SUCCESS') {
            try {
                $r = FinanceLog::payNotify($out_trade_no, $money, FinanceLog::STATUS_SUCCESS, $raw);
            } catch (Exception $e) {
                return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[' . $e->getMessage() . ']]></return_msg></xml>';
            }
        } else {
            $r = false;
        }
        if (!$r) {
            return '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[无法更新业务信息]]></return_msg></xml>';
        }
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }
}
