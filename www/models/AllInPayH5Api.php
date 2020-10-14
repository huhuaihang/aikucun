<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

/**
 * 通联支付H5接口
 * Class AllInPayH5Api
 * @package app\models
 */
class AllInPayH5Api extends Model
{
    /**
     * @var string 接口地址
     */
    private $gateway = 'https://cashier.allinpay.com';
    /**
     * @var string 商户号
     */
    private $merchantId;
    /**
     * @var string 签名秘钥
     */
    private $md5Key;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (YII_DEBUG) {
            $this->gateway = 'http://test.allinpay.com';
        }
        $this->merchantId = System::getConfig('allinpay_h5_merchant_id');
        $this->md5Key = System::getConfig('allinpay_h5_md5_key');
        parent::init();
    }

    /**
     * 用户注册请求接口
     * @param string $uid 会员编号
     * @return string 返回通联userId
     * @throws Exception
     */
    public function userReg($uid)
    {
        $url = $this->gateway . '/usercenter/merchant/UserInfo/reg.do';
        $data = [
            'signType' => '0', // MD5
            'merchantId' => $this->merchantId,
            'partnerUserId' => $uid,
        ];
        $data['signMsg'] = $this->makeSign($data, true);
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay_h5');
        Yii::warning(json_encode($data), 'allinpay_h5');
        Yii::warning($response, 'allinpay_h5');
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new Exception('返回内容无法解析。');
        }
        if ($json['resultCode'] != '0000') {
            throw new Exception('[' . $json['resultCode'] . ']' . $json['describe']);
        }
        return $json['userId'];
        /*
         * merchantId 商户号
         * signType 签名类型
         * userId 通联用户编号
         * resultCode 结果代码
         * returnDatetime 结果返回时间
         * signMsg 签名字符串
         *
         * 0000  注册成功
         * 0006  该用户已存在，无法重复绑定
         * 0021  真实姓名不正确
         * 0023  证件类型不正确
         * 0025  证件号码不正确
         * 9999  未知的系统异常
         * 9901  参数签名类型不正确
         * 9902  参数用户编号不正确
         * 9903  验证签名失败
         * 9904  签名失败
         * 9905  用户身份证号解密失败
         * 9906  用户姓名解密失败
         * 9907  参数商户编号不正确
         */
    }

    /**
     * 获取订单提交表单，表单需要在页面中提交
     * @param $trade_no string 交易流水号
     * @param $money float 金额
     * @param $orderTime integer 交易创建时间
     * @param $userId string userCenterMerchantUserInfoReg接口返回的userId
     * @param $redirectUrl string 支付完成跳转地址
     * @return array
     */
    public function getSubmitForm($trade_no, $money, $orderTime, $userId, $redirectUrl)
    {
        $url = $this->gateway . '/mobilepayment/mobile/SaveMchtOrderServlet.action';
        $data = [
            'inputCharset' => '1', // UTF-8
            'pickupUrl' => $redirectUrl, // 取货URL地址
            'receiveUrl' => Url::to(['/api/all-in-pay-h5/notify'], true), // 服务器接受支付结果的后台地址
            'version' => 'v1.0', // 网关接受支付请求接口版本
            'language' => '1', // 简体中文
            'signType' => '0', // MD5
            'merchantId' => $this->merchantId, // 商户号
            'payerName' => '', // 付款人姓名
            'payerEmail' => '', // 付款人邮件联系方式
            'payerTelephone' => '', // 付款人电话联系方式
            'payerIDCard' => '', // 付款人类型及证件号
            'pid' => '', // 合作伙伴的商户号
            'orderNo' => $trade_no, // 商户订单号
            'orderAmount' => $money * 100, // 商户订单金额
            'orderCurrency' => '0', // 人民币
            'orderDatetime' => date('YmdHis', $orderTime), // 商户订单提交时间
            'orderExpireDatetime' => '', // 订单过期时间
            'productName' => '', // 商品名称
            'productPrice' => '', // 商品价格：整形数字
            'productNum' => '', // 商品数量
            'productId' => '', // 商品代码
            'productDesc' => '', // 商品描述
            'ext1' => '<USER>' . $userId . '</USER>', // 扩展字段1，会员模式：<USER>userId</USER>
            'ext2' => '', // 扩展字段2，支付完成后原样返回
            'extTL' => '', // 业务扩展字段
            'payType' => '0', // 未指定支付方式，即显示该商户开通的所有支付方式
            'issuerId' => '', // 发卡方代码
            'pan' => '', // 付款人支付卡号
            'tradeNature' => '', // 贸易类型
        ];
        $data['signMsg'] = $this->makeSign($data);
        $data['customsExt'] = ''; // 海关扩展字段
        return [$url, $data];
        /*
         * merchantId 商户号
         * version 网关返回支付结果接口版本
         * language 网页显示语言种类
         * signType 签名类型
         * payType 支付方式
         * issuerId 发卡方机构代码
         * paymentOrderId 通联订单号
         * orderNo 商户订单号
         * orderDatetime 商户订单提交时间
         * orderAmount 商户订单金额
         * payDatetime 订单完成时间
         * payAmount 订单实际支付金额
         * ext1 扩展字段1
         * ext2 扩展字段2
         * payResult 处理结果
         * errorCode 错误代码
         * returnDatetime 结果返回时间
         * signMsg 签名字符串
         */
    }

    /**
     * 单笔订单查询结构
     * @param $trade_no string 商户交易号
     * @param $orderTime integer 订单提交时间
     * @return string
     */
    public function query($trade_no, $orderTime)
    {
        $url = $this->gateway . '/gateway/index.do';
        $data = [
            'merchantId' => $this->merchantId, // 商户号
            'version' => 'v1.5', // 网关查询接口版本
            'signType' => '0', // 签名类型，需要与提交订单填写的值保持一致
            'orderNo' => $trade_no, // 商户订单号
            'orderDatetime' => date('YmdHis', $orderTime), // 商户订单提交时间，仅支持查询-31天以内的订单
            'queryDatetime' => date('YmdHis'), // 此时间不能与系统当前时间相差15分钟
        ];
        $data['signMsg'] = $this->makeSign($data);
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay_h5');
        Yii::warning(json_encode($data), 'allinpay_h5');
        Yii::warning($response, 'allinpay_h5');
        return $response;
        /*
         * 只返回支付成功的订单
         * 结果会同时以异步方式通知到原订单的receiveUrl
         * 结果参考getSubmitForm
         */
    }

    /**
     * 批量查询
     * @param $date string 查询日期Ymd
     * @param int $page 页码
     * @return string
     */
    public function batchQuery($date, $page = 1)
    {
        $url = $this->gateway . '/mchtoq/index.do';
        $data = [
            'version' => 'v1.6', // 网关批量查询接口版本
            'merchantId' => $this->merchantId, // 商户号
            'beginDateTime' => $date . '00', // 查询订单的开始时间
            'endDateTime' => $date . '23', // 查询订单的结束时间
            'pageNo' => $page, // 查询页码，从1开始
            'signType' => '1',
        ];
        $data['signMsg'] = $this->makeSign($data);
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay_h5');
        Yii::warning(json_encode($data), 'allinpay_h5');
        Yii::warning($response, 'allinpay_h5');
        return $response;
        /*
         * 只返回支付成功的订单，以文本同步方式返回
         * 每页最多返回 500 笔订单记录，批量查询结果返回格式：汇总信息+交易明细+换行+签名信息，商户根据首行的
         * 【是否有下一页(Y/N)】来判断是否要做下一页查询，处理结果：1 代表支付成功；0 代表未支付。如果查询结果
         * 无记录，则只有汇总信息（商户号，笔数为 0，页码为 0，是否有下一页为 N）+换行+签名信息（签名信息与上
         * 行之间有一空行）
         * 汇总信息：
         * 商户号|当页笔数|当前页码|是否有下一页
         * 交易明细：
         * 商户号|通联订单号|商户订单号|商户订单提交时间|商户订单金额|支付完成时间|订单实际支付金额|扩展字段1|扩展字段 2|处理结果
         * 签名信息：
         * 对汇总信息+交易明细+换行用证书签名所得值
         */
    }

    /**
     * 订单退款
     * @param $trade_no string 原交易号
     * @param $trade_time integer 原交易创建时间
     * @param $refund_trade_no string 退款交易号
     * @param $refund_money float 退款金额
     * @return string
     */
    public function refund($trade_no, $trade_time, $refund_trade_no, $refund_money)
    {
        $url = $this->gateway . '/gateway/index.do';
        $data = [
            'version' => 'v2.3', // 网关联机退款接口版本
            'signType' => '0', // MD5
            'merchantId' => $this->merchantId, // 商户号
            'orderNo' => $trade_no, // 商户订单号
            'refundAmount' => $refund_money * 100, // 退款金额
            'mchtRefundOrderNo' => $refund_trade_no, // 商户退款订单号
            'orderDatetime' => date('YmdHis', $trade_time), // 商户订单提交时间
        ];
        $data['signMsg'] = $this->makeSign($data);
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay_h5');
        Yii::warning(json_encode($data), 'allinpay_h5');
        Yii::warning($response, 'allinpay_h5');
        return $response;
        /*
         * merchantId 商户号
         * version 网关联机退款接口版本v2.3
         * signType 签名类型
         * orderNo 商户订单号
         * orderAmount 商户订单金额
         * orderDatetime 商户订单提交时间
         * refundAmount 退款金额
         * refundDatetime 退款受理时间
         * refundResult 退款结果 申请成功：20
         * mchtRefundOrderNo 商户退款订单号
         * returnDatetime 结果返回时间
         * signMsg 签名字符串
         */
    }

    /**
     * 退款查询接口
     * @param $trade_no string 原交易号
     * @param $refund_trade_no string 退款交易号
     * @param $refund_money float 退款金额
     * @param $refund_time integer 退款受理时间
     * @return string
     */
    public function refundQuery($trade_no, $refund_trade_no, $refund_money, $refund_time)
    {
        $url = $this->gateway . '/mchtoq/refundQuery';
        $data = [
            'version' => 'v2.4', // 退款查询版本
            'signType' => '0', // 签名类型
            'merchantId' => $this->merchantId, // 商户号
            'orderNo' => $trade_no, // 商户订单号
            'refundAmount' => $refund_money * 100, // 退款金额
            'refundDatetime' => date('YmdHis', $refund_time), // 退款受理时间
            'mchtRefundOrderNo' => $refund_trade_no, // 商户退款订单号
        ];
        $data['signMsg'] = $this->makeSign($data);
        $response = Util::post($url, $data);
        Yii::warning($url, 'allinpay_h5');
        Yii::warning(json_encode($data), 'allinpay_h5');
        Yii::warning($response, 'allinpay_h5');
        return $response;
        /*
         * version 退款查询版本
         * signType 签名类型
         * merchantId 商户号
         * orderNo 商户订单号
         * refundAmount 退款金额
         * refundDatetime 退款受理时间
         * mchtRefundOrderNo 商户退款订单号
         * refundResult 退款结果，参考成功码描述
         * returnDatetime 结果返回时间
         * signMsg 签名字符串
         * 退款查询成功返回码
         * 成功 返回码 返回码说明
         * 1 TKSUCC0001 退款未受理
         * 2 TKSUCC0002 待通联审核
         * 3 TKSUCC0003 通联审核通过
         * 4 TKSUCC0004 退款冲销
         * 5 TKSUCC0005 处理中
         * 6 TKSUCC0006 退款成功
         * 7 TKSUCC0007 退款失败
         * 8 TKSUCC0008 通联审核不通过
         * 如果查询结果为一条记录，则返回一条，如果查询结果为多条记录，则返回多条
         * 错误码及描述
         * 失败 错误码 错误描述
         * 1 001 商户号不能为空
         * 2 002 商户号不能超过15位
         * 3 003 商户号不存在
         * 4 004 订单号不能为空
         * 5 005 订单号不存在
         * 6 006 退款金额不能为空
         * 7 007 退款金额必须大于0
         * 8 008 退款金额必须为整数
         * 9 009 退款受理时间格式不匹配
         * 10 010 摘要错误
         * 11 011 退款订单不存在
         */
    }

    /**
     * 签名
     * @param $data array 数据
     * @param $moreAnd boolean 是否需要在前后加“&”字符
     * @return string
     */
    public function makeSign($data, $moreAnd = false)
    {
        $str = '';
        foreach ($data as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . $this->md5Key;
        if ($moreAnd) {
            $str = '&' . $str . '&';
        }
        return strtoupper(md5($str));
    }
}
