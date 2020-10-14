<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户收藏商品
 * Class UserFavGoods
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $gid 商品编号
 * @property integer $create_time 创建时间
 *
 * @property Goods $goods 关联商品
 */
class UserFavGoods extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'gid', 'create_time'], 'required'],
        ];
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }
}
