<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品服务
 * Class GoodsService
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property string $desc 描述
 */
class GoodsService extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 32],
            ['desc', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'desc' => '描述',
        ];
    }
}
