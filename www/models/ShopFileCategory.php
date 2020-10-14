<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺文件分类
 * Class ShopFileCategory
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property string $name 名称
 */
class ShopFileCategory extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 32],
        ];
    }
}
