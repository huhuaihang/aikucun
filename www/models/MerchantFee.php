<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户费用设置
 * Class MerchantFee
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 商品分类编号
 * @property float $earnest_money 保证金
 *
 * @property GoodsCategory $category 关联商品分类
 */
class MerchantFee extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid'], 'required'],
            [['earnest_money'], 'filter', 'filter' => 'floatval'],
            ['earnest_money', 'compare', 'compareValue' => 0, 'operator' => '>='],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cid' => '商品分类',
            'earnest_money' => '保证金',
        ];
    }

    /**
     * 关联商品分类
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(GoodsCategory::className(), ['id' => 'cid']);
    }
}
