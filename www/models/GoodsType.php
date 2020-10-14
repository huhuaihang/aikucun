<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品类型
 * Class GoodsType
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 */
class GoodsType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
        ];
    }

    /**
     * 关联属性数量
     * @return int
     */
    public function getAttrCount()
    {
        return $this->hasMany(GoodsAttr::className(), ['tid' => 'id'])->count();
    }
}
