<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 系统消息
 * Class Notice
 * @package app\models
 *
 * @property integer $id PK
 * @property string $title 标题
 * @property string $main_pic 主图
 * @property string $content 内容
 * @property string $desc 简介
 * @property integer $status 状态
 * @property integer $time 时间
 */
class Notice extends ActiveRecord
{
    const STATUS_SHOW = 1;
    const STATUS_HIDE = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'desc'], 'required'],
            ['title', 'string', 'max' => 128],
            [['main_pic', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'main_pic' => '主图',
            'title' => '标题',
            'desc' => '简介',
            'content' => '内容',
            'status' => '状态',
        ];
    }
}
