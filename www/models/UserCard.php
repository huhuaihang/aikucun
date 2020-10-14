<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户绑定卡
 * Class UserCard
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $card_no 卡号
 * @property integer $c_lid 卡等级编号
 * @property integer $bind_time 绑定时间
 * @property integer $unset_bind_time 解绑时间
 * @property integer $status 状态
 * @property integer $create_time 初次绑定时间
 *
 * @property UserCardLevel $level 获取自己的用户卡等级
 */
class UserCard extends ActiveRecord
{
    const STATUS_OK = 1; //正常
    const STATUS_STOP = 9; //解绑挂失
    const STATUS_DELETE = 0; //删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_no'], 'required'],
            [['card_no'], 'string', 'max' => 32],
        ];
    }

    /**
     * 关联用户账户
     * @return \yii\db\ActiveQuery
     */
    public function getLevel()
    {
        return $this->hasOne(UserCardLevel::className(), ['c_lid' => 'id']);
    }
}
