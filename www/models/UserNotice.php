<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户阅读公告
 * Class UserNotice
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $nid 公告编号
 * @property integer $create_time 创建时间
 *
 * @property Notice $notice 关联公告
 */
class UserNotice extends ActiveRecord
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
     * 关联公告
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Notice::className(), ['id' => 'nid']);
    }
}
