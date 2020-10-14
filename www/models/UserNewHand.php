<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户阅读新手入门文章
 * Class UserNewHand
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $nid 文章编号
 * @property integer $create_time 创建时间
 *
 * @property NewHand $newHand 关联店铺
 */
class UserNewHand extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'nid', 'create_time'], 'required'],
        ];
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getNewHand()
    {
        return $this->hasOne(NewHand::className(), ['id' => 'nid']);
    }
}
