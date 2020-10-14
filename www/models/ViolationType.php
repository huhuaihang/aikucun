<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 违规类型
 * Class ViolationType
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property string $remark 备注
 */
class ViolationType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 128],
            [['remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'remark' => '备注',
        ];
    }
}
