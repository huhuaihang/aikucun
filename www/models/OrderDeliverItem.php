<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 订单发货单内容
 * Class OrderDeliverItem
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $did 订单发货单编号
 * @property integer $oiid 订单内容编号
 * @property integer $amount 数量
 *
 * @property OrderItem $orderItem 关联订单内容
 */
class OrderDeliverItem extends ActiveRecord
{
    /**
     * 关联订单内容
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::className(), ['id' => 'oiid']);
    }
}
