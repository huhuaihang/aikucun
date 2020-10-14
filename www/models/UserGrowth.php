<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_subsidy".
 *
 * @property int $id
 * @property int $to_uid
 * @property int $to_1_uid
 * @property int $to_2_uid
 * @property int $to_3_uid
 * @property int $from_uid
 * @property string $money
 * @property int $type
 * @property string $no
 * @property int $create_time
 *
 * @property User $fromUser
 * @property User $to1U
 * @property User $to2U
 * @property User $to3U
 * @property User $toUser
 */
class UserGrowth extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_uid', 'from_uid', 'type', 'create_time'], 'integer'],
            [['money'], 'number'],
            [['from_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['from_uid' => 'id']],
            [['to_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_uid' => 'To Uid',
            'to_1_uid' => 'To 1 Uid',
            'to_2_uid' => 'To 2 Uid',
            'to_3_uid' => 'To 3 Uid',
            'from_uid' => 'From Uid',
            'money' => 'Money',
            'to_user_level' => 'To User Level',
            'type' => 'Type',
            'no' => 'No',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo1U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_1_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo2U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_2_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTo3U()
    {
        return $this->hasOne(User::className(), ['id' => 'to_3_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_uid']);
    }
}
