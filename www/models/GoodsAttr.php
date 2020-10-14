<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品属性
 * Class GoodsAttr
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $tid 商品类型编号
 * @property string $name 属性名称
 * @property string $values 可选值列表
 * @property integer $is_sku 是否SKU属性
 *
 * @property GoodsType $goodsType 关联商品类型
 */
class GoodsAttr extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tid', 'name'], 'required'],
            ['name', 'string', 'max' => 32],
            ['is_sku', 'default', 'value' => 0],
            ['values', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '属性名称',
            'values' => '可选值',
            'is_sku' => '是否为规格属性',
        ];
    }

    /**
     * 关联商品类型
     * @return \yii\db\ActiveQuery
     */
    public function getGoodsType()
    {
        return $this->hasOne(GoodsType::className(), ['id' => 'tid']);
    }

    /**
     * 返回可选值Map
     * @return array
     */
    public function getValuesList()
    {
        if (empty($this->values)) {
            return [];
        }
        return preg_split("/\r|\n/", $this->values, -1, PREG_SPLIT_NO_EMPTY);
    }
}
