<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 供货商结算
 * Class SupplierFinancialSettlement
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 供货商编号
 * @property integer $oid 订单编号
 * @property integer $oiid 订单详情编号
 * @property integer $gid 商品编号
 * @property float $price 结算单价
 * @property integer $amount 数量
 * @property float $money 结算金额
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property integer $lid 结算记录编号
 * @property integer $settle_time 结算时间
 * @property string $remark 备注
 *
 * @property Supplier $supplier 关联供货商
 * @property Order $order 关联订单
 * @property OrderItem $orderItem 关联订单内容
 */
class SupplierFinancialSettlement extends ActiveRecord
{
    const STATUS_WAIT_DAY = 1; // 未到结算期
    const STATUS_MONEY_FIXED = 2; // 金额确定
    const STATUS_WAIT_PAY = 3; // 等待结算
    const STATUS_SETTLE = 4; // 已结算
    const STATUS_REFUND = 5; // 已经售后退款不再结算

    /**
     * 关联供货商
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'sid']);
    }

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'oid']);
    }

    /**
     * 关联订单内容
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::class, ['id' => 'oiid']);
    }
}
