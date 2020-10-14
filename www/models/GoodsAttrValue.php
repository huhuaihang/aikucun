<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品属性值
 * Class GoodsAttrValue
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property integer $aid 属性编号
 * @property string $value 值
 * @property string $image 属性图片
 *
 * @property GoodsAttr $goods_attr 关联属性表
 * @property Goods $goods 关联商品表
 */
class GoodsAttrValue extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['value', 'required'],
            ['value', 'filter', 'filter' => 'trim'],
        ];
    }

    /**
     * 关联属性表
     * @return \yii\db\ActiveQuery
     */
    public function getGoods_attr()
    {
        return $this->hasOne(GoodsAttr::className(), ['id' => 'aid']);
    }

    /**
     * 关联商品表
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }
}
