<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 通联支付对账
 * Class BankReconciliationAllinpay
 * @package app\models
 *
 * @property integer $id PK
 * @property string $mch_id 商户号
 * @property string $store_name 门店名称
 * @property string $term_no 终端号
 * @property string $trade_time 交易时间
 * @property string $trade_type 交易类型
 * @property string $trade_batch_no 交易批次号
 * @property string $proof_no 凭证号
 * @property string $ref_no 参考号
 * @property string $card_no 卡号
 * @property string $card_type 卡类别
 * @property string $card_org_no 发卡行机构代码
 * @property string $card_org_name 发卡行名称
 * @property float $trade_money 交易金额
 * @property float $tax_money 手续费
 * @property string $trade_date 交易日期
 * @property string $trade_no 交易单号
 * @property string $order_no 订单号
 * @property string $mch_remark 商户备注
 * @property string $app_no 对接应用号
 */
class BankReconciliationAllinpayAli extends ActiveRecord
{}
