<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户微信绑定
 * Class UserWeixin
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $app_id 微信AppId
 * @property string $open_id 微信OpenId
 * @property string $union_id 微信unionId
 * @property integer $create_time 创建时间
 *
 * @property User $user 关联用户
 */
class UserWeixin extends ActiveRecord
{
    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'uid']);
    }
}
