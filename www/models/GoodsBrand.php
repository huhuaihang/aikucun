<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品品牌
 * Class GoodsBrand
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property string $owner 持有人
 * @property string $logo LOGO
 * @property string $tm_r TM或R
 * @property integer $sort 排序数字
 * @property integer $create_time 创建时间
 * @property string $valid_time 有效期至
 *
 * @property GoodsType[] $typeList 关联类型列表
 */
class GoodsBrand extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'owner', 'logo', 'tm_r', 'valid_time'], 'required'],
            ['tm_r', 'string', 'max' => 4],
            [['name', 'valid_time'], 'string', 'max' => 32],
            ['owner', 'string', 'max' => 128],
            ['sort', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'logo' => 'LOGO',
            'owner' => '持有人',
            'tm_r' => 'TM或R',
            'valid_time' => '有效期',
        ];
    }

    /**
     * 关联类型列表
     * @return \yii\db\ActiveQuery
     */
    public function getTypeList()
    {
        return $this->hasMany(GoodsType::className(), ['id' => 'tid'])->viaTable(GoodsTypeBrand::tableName(), ['bid' => 'id']);
    }
}
