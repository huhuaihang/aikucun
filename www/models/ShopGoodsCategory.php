<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺商品分类
 * Class ShopGoodsCategory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property string $name 名称
 * @property integer $sort 排序
 * @property integer $status 状态
 */
class ShopGoodsCategory extends ActiveRecord
{
    const STATUS_SHOW = 1;
    const STATUS_HIDE = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sid', 'name', 'sort', 'status'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['sid', 'sort', 'status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => '店铺编号',
            'name' => '名称',
            'sort' => '排序',
            'status' => '状态',
        ];
    }
}
