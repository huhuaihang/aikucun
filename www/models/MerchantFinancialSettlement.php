<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户结算
 * Class MerchantFinancialSettlement
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property integer $oid 订单编号
 * @property float $order_money 订单金额
 * @property float $refund_money 退款金额
 * @property float $merchant_receive_money 商户实收金额
 * @property float $charge 服务费
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property integer $lid 结算记录编号
 * @property integer $settle_time 结算时间
 * @property string $remark 备注
 *
 * @property Merchant $merchant 关联商户
 * @property Order $order 关联订单
 */
class MerchantFinancialSettlement extends ActiveRecord
{
    const STATUS_WAIT = 1; // 未结算
    const STATUS_WAIT_SETTLE = 2; // 等待结算
    const STATUS_SETTLE = 3; // 已结算

    /**
     * 关联商户
     * @return \yii\db\ActiveQuery
     */
    public function getMerchant()
    {
        return $this->hasOne(Merchant::className(), ['id' => 'mid']);
    }

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }
}
