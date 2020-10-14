<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户卡等级
 * Class UserCardLevel
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 等级名称
 * @property string $remark 等级说明
 * @property integer $create_time 创建时间
 * @property integer $status 状态
 */
class UserCardLevel extends ActiveRecord
{
    const STATUS_OK = 1; //正常
    const STATUS_STOP = 9; //弃用
    const STATUS_DELETE = 0; //删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['remark'], 'string', 'max' => 128],
            [['status'], 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '等级名称',
            'remark' => '权益说明',
            'status' => '状态',
        ];
    }
}
