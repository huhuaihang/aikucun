<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_subsidy".
 *
 * @property int $id
 * @property int $uid
 * @property string $level_id
 * @property string $remark
 * @property int $create_time
 *
 * @property User $user
 * @property User $toUser
 */
class UserLevelLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'level_id', 'create_time'], 'integer'],
            [['remark'], 'string', 'max' => 256],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户编号',
            'level_id' => '升级后的等级',
            'remark' => '备注',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
}
