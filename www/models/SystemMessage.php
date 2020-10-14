<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 系统消息
 * Class SystemMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $target_type 目标类型
 * @property string $title 标题
 * @property string $content 内容
 * @property integer $time 时间
 */
class SystemMessage extends ActiveRecord
{
    const TARGET_MERCHANT = 2;
    const TARGET_SUPPLIER = 4;

    const STATUS_UNREAD = 1;
    const STATUS_READ = 9;
    const STATUS_DEL = 0;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            ['title', 'string', 'max' => 128],
            ['target_type', function () {
                if (is_array($this->target_type)) {
                    $this->target_type = array_sum($this->target_type);
                }
            }],
            [['target_type'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title' => '标题',
            'target_type' => '目标类型',
            'content' => '内容',
        ];
    }
}
