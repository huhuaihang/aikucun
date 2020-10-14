<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

/**
 * 通联支付接口
 * Class AllInPayApi
 * @package app\models
 */
class AllInPayApi extends Model
{
    /**
     * @var string 接口地址
     */
    private $gateway;
    /**
     * @var string 商户号
     */
    private $merchantId;
    /**
     * @var string 密钥
     */
    private $key;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->gateway = YII_ENV === 'prod' ? 'https://service.allinpay.com' : 'http://ceshi.allinpay.com';
        $this->merchantId = System::getConfig('allinpay_merchant_id');
        $this->key = System::getConfig('allinpay_key');
        parent::init();
    }

    /**
     * 页面订单提交
     * @param $tradeNo string 商户交易号
     * @param $orderMoney float 订单金额（单位元）
     * @param $orderTime integer 订单提交时间
     * @param $productName string 商品名称
     * @param $returnUrlType string 支付返回地址类型 web 返回到订单详情页面 app 返回到APP通知页
     * @return array
     */
    public function submit($tradeNo, $orderMoney, $orderTime, $productName, $returnUrlType = 'web')
    {
        $returnUrl = Url::to(['/api/all-in-pay/return', 'trade_no' => $tradeNo, 'type' => $returnUrlType], true);
        $data = [
            'inputCharset' => 1, // 字符集 1 UTF-8 2 GBK 3 GB2312
            'pickupUrl' => $returnUrl, // 付款客户取货url地址
            'receiveUrl' => Url::to(['/api/all-in-pay/notify'], true), // 服务器接受支付结果的后台地址
            'version' => 'v1.0', // 网关接收支付请求接口版本
            'language' => '1', // 网关页面显示语言种类 1 简体中文 2 繁体中文 3 英文
            'signType' => '0', // 签名类型 0 上送和交易结果通知都使用MD5进行签名 1 上送使用MD5，通联使用证书
            'merchantId' => $this->merchantId, // 商户号
            'payerName' => '', // 付款人姓名
            'payerEmail' => '', // 付款人邮件联系方式
            'payerTelephone' => '', // 付款人电话联系方式
            'payerIDCard' => '', // 付款人类型及证件号
            'pid' => '', // 合作伙伴的商户号
            'orderNo' => $tradeNo, // 商户订单号
            'orderAmount' => $orderMoney * 100, // 商户订单金额（单位分）
            'orderCurrency' => '0', // 订单金额币种类型 0和156 人民币 840 美元 344 港币
            'orderDatetime' => date('YmdHis', $orderTime), // 商户订单提交时间
            'orderExpireDatetime' => '', // 订单过期时间 整形数字,单位为分钟。最大值为 9999 分钟。
            'productName' => $productName, // 商品名称
            'productPrice' => '', // 商品价格
            'productNum' => '', // 商品数量
            'productId' => '', // 商品代码
            'productDesc' => '', // 商品描述
            'ext1' => '', // 扩展字段1
            'ext2' => '', // 扩展字段2
            'extTL' => '', // 业务扩展字段
            'payType' => '0', // 支付方式
            // 0 未指定支付方式，显示该商户开通的所有支付方式
            // 1 个人储蓄卡网银支付
            // 4 企业网银支付
            // 11 个人信用卡网银支付
            // 23 外卡支付
            // 28 认证支付
            'issuerId' => '', // 发卡方代码
            'pan' => '', // 付款人支付卡号
            'tradeNature' => '', // 贸易类型
        ];
        $data['signMsg'] = $this->makeSign($data);
        $data['customsExt'] = ''; // 海关扩展字段
        return [
            $this->gateway . '/gateway/index.do',
            $data
        ];
    }

    /**
     * 单笔订单查询
     * @param $tradeNo string 交易号
     * @param $tradeTime integer 交易创建时间
     * @return array
     */
    public function queryOne($tradeNo, $tradeTime)
    {
        $url = $this->gateway . '/gateway/index.do';
        $data = [
            'merchantId' => $this->merchantId, // 商户号
            'version' => 'v1.5', // 网关查询接口版本
            'signType' => '0', // 签名类型 0 上送和交易结果通知都使用MD5进行签名 1 上送使用MD5，通联使用证书
            'orderNo' => $tradeNo, // 商户订单号
            'orderDatetime' => date('YmdHis', $tradeTime), // 商户订单提交时间，需要和提交订单时的时间一致
            'queryDatetime' => date('YmdHis'), // 商户提交查询的时间
        ];
        $sign = $this->makeSign($data);
        $data['signMsg'] = $sign;
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay');
        Yii::warning($data, 'allinpay');
        Yii::warning($response, 'allinpay');
        $data = [];
        foreach (explode('&', $response) as $item) {
            $item = explode('=', $item);
            if (count($item) > 1) {
                $data[$item[0]] = $item[1];
            }
        }
        return $data;
    }

    /**
     * 批量订单查询
     * @param $beginDateTime integer 开始时间
     * @param $endDateTime integer 结束时间
     * @param $pageNo integer 页码
     * @return string
     */
    public function queryAll($beginDateTime, $endDateTime, $pageNo = 1)
    {
        $data = [
            'version' => 'v1.6', // 网关批量查询接口版本
            'merchantId' => $this->merchantId, // 商户号
            'beginDateTime' => date('YmdH', $beginDateTime), // 查询订单的开始时间
            'endDateTime' => date('YmdH', $endDateTime), // 查询订单的结束时间
            'pageNo' => $pageNo, // 查询页码，从1开始
            'signType' => '1', // 签名类型
        ];
        $sign = $this->makeSign($data);
        $data['signMsg'] = $sign;
        $response = Util::post($this->gateway . '/mchtoq/index.do', $data);
        $response = base64_decode($response);
        return $response;
    }

    /**
     * 单笔订单退款
     * @param $tradeNo string 支付订单交易号
     * @param $refundMoney float 退款金额，单位元
     * @param $refundTradeNo string 退款交易号
     * @param $tradeTime integer 订单支付时间
     * @return array
     */
    public function refund($tradeNo, $refundMoney, $refundTradeNo, $tradeTime)
    {
        $data = [
            'version' => 'v2.3', // 网关联机退款接口版本
            'signType' => '0', // 签名类型
            'merchantId' => $this->merchantId, // 商户号
            'orderNo' => $tradeNo, // 商户订单号
            'refundAmount' => $refundMoney * 100, // 退款金额，单位分
            'mchtRefundOrderNo' => $refundTradeNo, // 商户退款订单号
            'orderDatetime' => date('YmdHis', $tradeTime), // 商户订单提交时间
        ];
        $sign = $this->makeSign($data);
        $data['signMsg'] = $sign;
        Yii::error($this->gateway . '/gateway/index.do', 'allinpay');
        Yii::error($data, 'allinpay');
        $response = Util::post($this->gateway . '/gateway/index.do', $data);
        Yii::error($response, 'allinpay');
        $data = [];
        foreach (explode('&', $response) as $item) {
            $item = explode('=', $item);
            if (count($item) > 1) {
                $data[$item[0]] = $item[1];
            }
        }
        return $data;
    }

    /**
     * @param $tradeNo string 支付交易号
     * @param $refundMoney float 退款金额
     * @param $refundDatetime integer 退款时间
     * @param $refundTradeNo string 退款交易号
     * @return string
     */
    public function queryRefund($tradeNo, $refundMoney, $refundDatetime = 0,  $refundTradeNo = '')
    {
        $data = [
            'version' => 'v2.4', // 退款查询版本
            'signType' => '0', // 签名类型
            'merchantId' => $this->merchantId, // 商户号
            'orderNo' => $tradeNo, // 商户订单号
            'refundAmount' => $refundMoney * 100, // 退款金额
            'refundDatetime' => empty($refundDatetime) ? '' : date('YmdHis', $refundDatetime), // 退款受理时间
            'mchtRefundOrderNo' => $refundTradeNo, // 商户退款订单号
        ];
        $sign = $this->makeSign($data);
        $data['signMsg'] = $sign;
        $response = Util::post($this->gateway . '/mchtoq/refundQuery', $data);
        return $response;
    }

    /**
     * 下载对账单
     * @param $date string 结算日期 Y-m-d
     * @return string
     */
    public function queryBill($date)
    {
        $data = [
            'mchtCd' => $this->merchantId,
            'settleDate' => $date,
        ];
        $data['signMsg'] = strtoupper(md5($data['mchtCd'] . $data['settleDate'] . $this->key));
        $response = Util::post($this->gateway . '/member/checkfiledown/CheckFileDownLoad/checkfileDownLoad.do', $data);
        return $response;
    }

    /**
     * 签名
     * @param $data array 待加密的数据
     * @return string
     */
    public function makeSign($data)
    {
        $data['key'] = $this->key;
        array_walk($data, function (&$v, $k) {
            $v = $v == '' ? null : ($k . '=' . $v);
        });
        $sign = strtoupper(md5(implode('&', array_filter($data))));
        return $sign;
    }

    /**
     * 定时任务获取对账信息
     * @param string $date = null 对账单日期YYYYMMDD
     * @return string
     */
    public static function task_bank_reconciliation($date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d', time() - 4 * 86400);
        }

        Yii::warning('通联支付对账：' . $date, 'allinpay');

        $api = new AllInPayApi();
        $result = $api->queryBill($date);
        Yii::warning(print_r($result, true), 'allinpay');

        if (strpos($result, 'ERRORCODE') !== false) {
            Yii::error('通联支付对账：' . $result);
            return '通联支付对账失败：' . $date . '：' . $result;
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            $data = preg_split('/\n/', $result, -1, PREG_SPLIT_NO_EMPTY);
            array_pop($data); // 最后一行为签名
            array_shift($data); // 第一行为统计
            foreach ($data as $item) {
                if (empty($item)) {
                    continue;
                }
                $item = explode('|', $item);
                if (count($item) < 10) {
                    continue;
                }
                $model = new BankReconciliationAllinpay();
                $model->setAttributes([
                    'type' => $item[0],
                    'date' => $item[1],
                    'mch_id' => $item[2],
                    'trade_time' => $item[3],
                    'trade_no' => $item[4],
                    'out_trade_no' => $item[5],
                    'money' => $item[6],
                    'fee' => $item[7],
                    'settle_money' => $item[8],
                    'currency' => $item[9],
                    'origin_money' => $item[10],
                ], false);
                if (!$model->save()) {
                    throw new Exception($model->errors);
                }
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
        }

        return '对账完成：' . $date;
    }

    /**
     * 错误码对应信息
     * @param $errorCode string 错误码
     * @return string
     * @throws Exception
     */
    public function errorCodeMessage($errorCode)
    {
        $map = [
            '00000' => '请联系客服',
            '00001' => '未知的报文参数验证错误',
            '00002' => '未知的交易处理参数配置错误',
            '10001' => '不支持的字符编码格式',
            '10002' => '参数取货地址不正确',
            '10004' => '参数签名类型不正确',
            '10005' => '参数语言类型不正确',
            '10006' => '参数支付方式不正确',
            '10007' => '参数扩展参数 2 不正确',
            '10008' => '参数扩展参数 1 不正确',
            '10009' => '参数产品描述信息部正确',
            '10010' => '参数产品代码不正确',
            '10011' => '参数产品数量不正确',
            '10012' => '参数产品名称不正确',
            '10013' => '参数订单提交时间不正确',
            '10014' => '参数订单金额不正确',
            '10015' => '参数订单号不正确',
            '10016' => '参数付款人的 email 地址不正确',
            '10017' => '参数付款人的电话不正确',
            '10018' => '参数付款人名称不正确',
            '10019' => '参数报文版本号不正确',
            '10020' => '参数签名信息不正确',
            '10021' => '参数发卡机构号不正确',
            '10022' => '参数网银直连支付参数不正确',
            '10023' => '参数查询时间不正确',
            '10024' => '参数查询开始时间不正确',
            '10025' => '参数查询结束时间不正确 ',
            '10026' => '参数异步接收查询结果的 URL 地址不正确 ',
            '10027' => '该笔订单不存在 ',
            '10028' => '该笔订单不符合退款条件 ',
            '20001' => '报文编码格式错误 ',
            '30001' => '系统参数配置错误 ',
            '30002' => '订单已经支付成功 ',
            '30003' => '不能重复提交订单 ',
            '30004' => '原订单不存在 ',
            '30005' => '商户支付配置错误或商户号不存在 ',
            '40001' => '商户未被授权接入系统 ',
            '50001' => '系统繁忙,请稍后再试 ',
            '50055' => '密码不正确 ',
            '50056' => '卡不存在 ',
            '50003' => '商户无效 ',
            '50014' => '无效卡号 ',
            '50075' => '密码输入次数超限',
        ];
        if (isset($map[$errorCode])) {
            return $map[$errorCode];
        }
        throw new Exception('未知错误，错误码：' . $errorCode);
    }
}
