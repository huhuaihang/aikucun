<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品SKU
 * Class GoodsSku
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property string $key 属性值编号
 * @property string $key_name 属性值中文
 * @property float $market_price 市场价
 * @property float $price 单价
 * @property float $supplier_price 结算单价
 * @property integer $stock 库存
 * @property float $commission 分佣设置
 * @property integer $img 缩略图
 *
 * @property Goods $goods 关联商品
 */
class GoodsSku extends ActiveRecord
{
    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }

    /**
     * 获取sku库存总数
     * @return int
     */
    public function getStock()
    {
        $sell = intval(OrderItem::find()
            ->alias('order_item')
            ->joinWith('order order')
            ->andWhere(['order.status' => Order::STATUS_CREATED])
            ->andWhere(['order_item.gid' => $this->gid])
            ->andWhere(['order_item.sku_key_name' => $this->key_name])
            ->sum('amount')
        );
        $amount = $this->stock - $sell;
        if ($amount < 0) {
            $amount = 0;
        }
        return $amount;
    }
}
