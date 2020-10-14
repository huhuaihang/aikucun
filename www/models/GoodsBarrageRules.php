<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品弹幕
 * Class GoodsBarrage
 * @package app\models
 *
 * @property integer $id PK
 * @property string $title 标题
 * @property int $create_time 创建时间
 * @property int $status 状态
 */
class GoodsBarrageRules extends ActiveRecord
{

    const STATUS_OK = 1; // 正常
    const STATUS_HIDE = 9; // 隐藏
    const STATUS_DELETE = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 32],
            [['status', 'create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'status' => '状态',
        ];
    }
}
