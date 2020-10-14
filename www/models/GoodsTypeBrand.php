<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品类型品牌关联
 * Class GoodsTypeBrand
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $tid 商品类型编号
 * @property integer $bid 商品品牌编号
 *
 * @property GoodsBrand $brand 关联品牌
 * @property GoodsType $type 关联类型
 */
class GoodsTypeBrand extends ActiveRecord
{
    /**
     * 关联品牌
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(GoodsBrand::className(), ['id' => 'bid']);
    }

    /**
     * 关联类型
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(GoodsType::className(), ['id' => 'tid']);
    }
}
