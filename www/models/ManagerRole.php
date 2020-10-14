<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 管理员角色
 * Class ManagerRole
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 角色名称
 * @property integer $status 状态
 * @property string $remark 备注
 */
class ManagerRole extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 32],
            ['remark', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '角色名称',
            'remark' => '备注',
        ];
    }
}
