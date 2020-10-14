<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品设置
 * Class GoodsConfig
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property string $k 键
 * @property string $v 值
 */
class GoodsConfig extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'k'], 'required'],
            ['gid', 'integer'],
            ['k', 'string', 'max' => 128],
            ['v', 'safe'],
        ];
    }
}
