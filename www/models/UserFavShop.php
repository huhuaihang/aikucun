<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户收藏店铺
 * Class UserFavShop
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $sid 店铺编号
 * @property integer $create_time 创建时间
 *
 * @property Shop $shop 关联店铺
 */
class UserFavShop extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'sid', 'create_time'], 'required'],
        ];
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
    }
}
