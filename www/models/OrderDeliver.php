<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 订单发货单
 * Class OrderDeliver
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $oid 订单编号
 * @property integer $supplier_id 供应商编号
 * @property integer $eid 物流编号
 * @property string $no 快递单号
 * @property integer $create_time 创建时间
 * @property integer $send_time 发货时间
 * @property integer $status 状态
 * @property string $trace 物流跟踪信息
 *
 * @property Express $express 关联物流公司
 * @property Order $order 关联订单
 * @property OrderDeliverItem[] $itemList 关联发货单内容列表
 * @property Supplier $supplier 关联供货商
 */
class OrderDeliver extends ActiveRecord
{
    const STATUS_WAIT = 1; // 等待发货
    const STATUS_SENT = 2; // 已发货

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oid', 'eid', 'create_time', 'send_time', 'status'], 'integer'],
            [['trace'], 'string'],
            [['no'], 'string', 'max' => 32],
            [['eid'], 'exist', 'skipOnError' => true, 'targetClass' => Express::className(), 'targetAttribute' => ['eid' => 'id']],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['oid' => 'id']],
        ];
    }

    /**
     * 关联物流公司
     * @return \yii\db\ActiveQuery
     */
    public function getExpress()
    {
        return $this->hasOne(Express::className(), ['id' => 'eid']);
    }

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }

    /**
     * 关联发货单内容列表
     * @return \yii\db\ActiveQuery
     */
    public function getItemList()
    {
        return $this->hasMany(OrderDeliverItem::className(), ['did' => 'id']);
    }

    /**
     * 关联供货商
     * @return \yii\db\ActiveQuery
     */
    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }
}
