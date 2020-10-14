<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户购物车
 * Class UserCart
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $sid 店铺编号
 * @property integer $gid 商品编号
 * @property string $sku_key_name 商品SKU信息
 * @property integer $amount 数量
 * @property float $price 最后更新的单价
 * @property integer $create_time 创建时间
 *
 * @property Shop $shop 关联店铺
 * @property Goods $goods 关联商品
 */
class UserCart extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['amount', 'default', 'value' => 1],
            [['sid', 'gid', 'amount', 'price', 'create_time'], 'required'],
            [['sku_key_name'], 'default', 'value' => ''],
            [['sku_key_name'], 'string', 'max' => 256],
        ];
    }

    /**
     * 根据唯一参数返回购物车
     * @param $uid integer 用户编号
     * @param $gid integer 商品编号
     * @param $sku_key_name string 规格
     * @return UserCart
     */
    public static function findByUGS($uid, $gid, $sku_key_name)
    {
        /** @var UserCart $cart */
        $cart = UserCart::find()
            ->andWhere(['uid' => $uid, 'gid' => $gid, 'sku_key_name' => $sku_key_name])
            ->one();
        return $cart;
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }
}
