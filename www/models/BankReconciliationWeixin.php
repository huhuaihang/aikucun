<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 微信对账
 * Class BankReconciliationWeixin
 * @package app\models
 *
 * @property integer $id PK
 * @property string $trade_time 交易时间
 * @property string $app_id 公众账号ID
 * @property string $mch_id 商户号
 * @property string $sub_mch_id 子商户号
 * @property string $client_no 设备号
 * @property string $weixin_trade_id 微信订单号
 * @property string $out_trade_no 商户订单号
 * @property string $user_open_id 用户标识
 * @property string $trade_type 交易类型
 * @property string $trade_status 交易状态
 * @property string $pay_bank 付款银行
 * @property string $currency 货币种类
 * @property float $order_amount 总金额
 * @property float $merchant_red 企业红包金额
 * @property string $subject 商品名称
 * @property string $merchant_data 商户数据包
 * @property float $charge 手续费
 * @property string $charge_ratio 费率
 * @property string $refund_trade_id 微信退款单号
 * @property string $refund_out_trade_no 商户退款单号
 * @property float $refund_amount 退款金额
 * @property float $refund_merchant_red 企业红包退款金额
 * @property string $refund_type 退款类型
 * @property string $refund_status 退款状态
 */
class BankReconciliationWeixin extends ActiveRecord
{}
