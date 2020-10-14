<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 平安银行对账单
 * Class BankReconciliationPingan
 * @package app\models
 *
 * @property integer $id PK
 * @property string $status 01标识成功，只返回成功
 * @property string $date 支付完成日期（结算日期）
 * @property float $charge 订单手续费金额，12位整数，2位小数
 * @property string $masterId 商户号
 * @property string $orderId ID+YYYYMMDD+8位流水
 * @property string $currency 目前只支持RMB
 * @property float $amount 订单金额，最大12位整数，2位小数
 * @property string $objectName 款项描述
 * @property string $paydate YYYYMMDDHHMMSS
 * @property integer $validtime 订单有效期（毫秒），0不生效
 * @property string $remark 备注字段（不可含分割符号）
 * @property string $settleflg 订单本金清算 1已清算，0待清算
 * @property string $settletime 本金清算时间YYYYMMDDHHMMSS
 * @property string $chargeflg 手续费清算标志 1已清算，0待清算
 * @property string $chargetime 手续费清算时间YYYYMMDDHHMMSS
 */
class BankReconciliationPingan extends ActiveRecord
{}
