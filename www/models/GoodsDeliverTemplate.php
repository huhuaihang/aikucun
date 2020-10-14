<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品运费模板
 * Class GoodsDeliverTemplate
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property integer $did 运费模板编号
 */
class GoodsDeliverTemplate extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'did'], 'required'],
        ];
    }
}
