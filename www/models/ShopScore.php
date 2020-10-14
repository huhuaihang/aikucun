<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 店铺评分
 * Class ShopScore
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $sid 店铺编号
 * @property integer $uid 用户编号
 * @property integer $oid 订单编号
 * @property integer $score 评分值
 * @property integer $create_time 创建时间
 */
class ShopScore extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['score'], 'safe'],
        ];
    }

}
