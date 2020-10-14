<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 订单内容
 * Class OrderItem
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $oid 订单编号
 * @property integer $gid 商品编号
 * @property string $title 商品标题
 * @property string $sku_key_name 商品SKU信息
 * @property integer $amount 数量
 * @property float $price 单价
 * @property integer $mark 活动商品标记
 * @property integer $mark_money 活动商品优惠金额
 * @property float $self_money 自购省金额
 * @property float $supplier_price 即时结算单价
 *
 * @property Goods $goods 关联商品
 * @property GoodsSku $goodsSku 商品SKU
 * @property Order $order 关联订单
 * @property OrderRefund $orderRefund 关联退货单
 */
class OrderItem extends ActiveRecord
{
    const DISCOUNT = 1;    // 限时抢购商品标识
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oid', 'gid', 'amount'], 'required'],
            [['oid', 'gid', 'amount','mark'], 'integer'],
            [['title'], 'string', 'max' => 128],
            [['sku_key_name'], 'string', 'max' => 256],
            [['price','mark_money','self_money', 'supplier_price'], 'number'],
            [['gid'], 'exist', 'skipOnError' => true, 'targetClass' => Goods::className(), 'targetAttribute' => ['gid' => 'id']],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['oid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'oid' => '订单编号',
            'gid' => '商品编号',
            'amount' => '数量',
            'price' => '单价',
        ];
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }

    /**
     * 返回商品SKU
     * @return GoodsSku|null
     */
    public function getGoodsSku()
    {
        if (empty($this->sku_key_name)) {
            return null;
        }
        /** @var GoodsSku $sku */
        $sku = GoodsSku::find()->where(['gid' => $this->gid, 'key_name' => $this->sku_key_name])->one();
        return $sku;
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
     * 关联退货单
     * @return \yii\db\ActiveQuery
     */
    public function getOrderRefund()
    {
        return $this->hasOne(OrderRefund::className(), ['oiid' => 'id'])->andWhere(['<>', '{{%order_refund}}.status', OrderRefund::STATUS_DELETE]);
    }

    /**
     * 返回已出发货单数量
     * @return int
     */
    public function getDeliverAmount()
    {
        $amount = 0;
        foreach (OrderDeliver::find()->andWhere(['oid' => $this->oid])->each() as $deliver) {
            /** @var OrderDeliver $deliver */
            foreach ($deliver->itemList as $item) {
                if ($item->oiid == $this->id) {
                    $amount += $item->amount;
                }
            }
        }
        return $amount;
    }

    /**
     * 返回当前发货单数量
     * @param $did int 发货单ID
     * @return int
     */
    public function getDeliverListAmount($did)
    {
        $amount = 0;
        foreach (OrderDeliver::find()->andWhere(['oid' => $this->oid])->each() as $deliver) {
            /** @var OrderDeliver $deliver */
            foreach ($deliver->itemList as $item) {
                if ($item->oiid == $this->id && $item->did == $did) {
                    $amount += $item->amount;
                }
            }
        }
        return $amount;
    }

    /**
     * 返回已出发货单数量
     * @return int
     */
    public function getSendDeliverAmount()
    {
        $amount = 0;
        foreach (OrderDeliver::find()->andWhere(['oid' => $this->oid, 'status' => OrderDeliver::STATUS_SENT])->each() as $deliver) {
            /** @var OrderDeliver $deliver */
            foreach ($deliver->itemList as $item) {
                if ($item->oiid == $this->id) {
                    $amount += $item->amount;
                }
            }
        }
        return $amount;
    }


    /**
     * 返回当前售后应退款金额
     * @return float
     */
    public function getRefundMoney()
    {

        $money = $this->order->is_score == 0 ? ($this->price - $this->self_money) * $this->amount : $this->price * $this->amount - $this->order->score_money;
        $money = sprintf("%.2f", $money);

        return $money;
    }

}
