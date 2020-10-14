<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 通联支付对账
 * Class BankReconciliationAllinpay
 * @package app\models
 *
 * @property integer $id PK
 * @property string $type 交易类型
 * @property string $date 结算日期
 * @property string $mch_id 商户号
 * @property string $trade_time 交易时间
 * @property string $trade_no 商户订单号
 * @property string $out_trade_no 通联流水号
 * @property float $money 交易金额
 * @property float $fee 手续费
 * @property float $settle_money 清算金额
 * @property string $currency 币种
 * @property integer $origin_money 商户原始订单金额（分）
 */
class BankReconciliationAllinpay extends ActiveRecord
{}
