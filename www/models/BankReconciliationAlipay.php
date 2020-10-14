<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 支付宝对账
 * Class BankReconciliationAlipay
 * @package app\models
 *
 * @property integer $id PK
 * @property string $alipay_trade_id 支付宝交易号
 * @property string $out_trade_no 商户订单号
 * @property string $biz_type 业务类型
 * @property string $subject 商品名称
 * @property string $create_time 创建时间
 * @property string $finish_time 完成时间
 * @property string $shop_no 门店编号
 * @property string $shop_name 门店名称
 * @property string $shop_user 操作员
 * @property string $shop_term_no 终端号
 * @property string $user_account 对方账户
 * @property float $total_amount 订单金额
 * @property float $merchant_receive_amount 商家实收
 * @property float $alipay_red 支付宝红包（元）
 * @property float $alipay_score 集分宝（元）
 * @property float $alipay_preference 支付宝优惠（元）
 * @property float $merchant_preference 商家优惠（元）
 * @property float $coupon_amount 券核销金额（元）
 * @property string $coupon_name 券名称
 * @property float $merchant_red 商家红包
 * @property float $card_amount 卡消费金额
 * @property string $refund_trade_no 退款单号
 * @property float $charge 服务费（元）
 * @property float $commission 分润（元）
 * @property string $remark 备注
 */
class BankReconciliationAlipay extends ActiveRecord
{}
