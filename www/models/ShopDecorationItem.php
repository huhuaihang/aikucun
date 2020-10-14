<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺装修块
 * Class ShopDecorationItem
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $type 类型
 * @property integer $sort 排序
 * @property string $data 数据
 *
 * @property Shop $shop 关联店铺
 */
class ShopDecorationItem extends ActiveRecord
{
    const TYPE_HOT = 1; // 热点
    const TYPE_SLIDE = 2; // 轮播
    const TYPE_GOODS = 3; // 商品
    const TYPE_PAGE = 4; // 页面

    /**
     * 返回店铺装修块列表
     * @param $sid integer 店铺编号
     * @return ShopDecorationItem[]
     */
    public static function listBySid($sid)
    {
        $model_list = ShopDecorationItem::find()
            ->andWhere(['sid' => $sid])
            ->orderBy('sort DESC')
            ->all();
        return $model_list;
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
