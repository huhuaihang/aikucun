<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class DiscountGoods
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $did 减折价编号
 * @property integer $gid 商品编号
 * @property integer $amount 限制数量
 * @property integer $sold_amount 已售数量
 * @property integer $type 类型：减价、折价
 * @property float $price 减价：0.50表示在原价基础上减0.5元
 * @property integer $ratio 折扣：1-99，85表示打8.5折
 * @property integer $hour 展示小时数
 *
 * @property Discount $discount 关联减折价
 * @property Goods $goods 关联商品

 */
class DiscountGoods extends ActiveRecord
{
    const TYPE_PRICE = 1;
    const TYPE_RATIO = 2;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'type','amount'], 'required'],
            [['price'], 'required','when'=>function () {
               return $this->type == DiscountGoods::TYPE_PRICE && empty($this->price);

            }],
            [['ratio'], 'required','when'=>function () {
                return $this->type == DiscountGoods::TYPE_RATIO && empty($this->ratio);

            }],
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => '类型',
            'gid' => '商品id',
            'amount' => '限制数量',
            'price' => '减价设置',
            'ratio' => '折扣设置',
            'hour' => '展示小时数',
            ];
    }
    /**
     * 关联减折价
     * @return \yii\db\ActiveQuery
     */
    public function getDiscount()
    {
        return $this->hasOne(Discount::class, ['id' => 'did']);
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'gid']);
    }

    /**
     * 限时商品已售数量
     * @param  $type null 真实支付销售数量 1 判断限制数量 增加订单创建状态
     * @return int
     */
    public function getSaleAmount($type=null)
    {
        if($this->discount->status != Discount::STATUS_RUNNING)
        {
            return $amount=0;
        }
         $query= OrderItem::find()->alias('order_item');
         $query->joinWith('order order');
         $query->andWhere(['order_item.gid' => $this->gid]);
         $query->andWhere(['order.discount_ids' => $this->discount->id]);
         $query->andWhere(['>=', 'order.create_time', $this->discount->start_time]);
         $query->andWhere(['<=', 'order.create_time', $this->discount->end_time]);
         if($type == 1)
         {
         $query->andWhere(['not in', 'order.status', [Order::STATUS_CANCEL, Order::STATUS_DELETE]]);
         }else{
         $query->andWhere(['not in', 'order.status', [Order::STATUS_CANCEL,Order::STATUS_CREATED, Order::STATUS_DELETE]]);
         }
         $query->andWhere(['>', 'order.discount_money', 0]);
         $amount=$query->sum('order_item.amount');
        if(empty($amount))
        {
         $amount=0;
        }
        return $amount;
    }

}
