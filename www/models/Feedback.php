<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 反馈
 * Class Feedback
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property string $client 客户端信息
 * @property string $version 客户端版本号
 * @property string $content 内容
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property User $user 关联用户
 */
class Feedback extends ActiveRecord
{
    const STATUS_WAIT = 1;
    const STATUS_FINISH = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client'], 'string', 'max' => 512],
            [['version'], 'string', 'max' => 32],
            ['content', 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => '用户编号',
            'content' => '内容',
            'status' => '状态',
        ];
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
}
