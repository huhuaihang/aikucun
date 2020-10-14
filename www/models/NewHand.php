<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 新手入门
 * Class NewHand
 * @package app\models
 *
 * @property integer $id PK
 * @property string $title 标题
 * @property string $main_pic 缩略图
 * @property string $content 内容
 * @property string $read_count 阅读人数
 * @property string $share_count 分享次数
 * @property string $status 状态
 * @property integer $start_time 开始时间
 * @property integer $create_time 创建时间
 */
class NewHand extends ActiveRecord
{
    const STATUS_OK = 1; // 正常
    const STATUS_HIDE = 9; // 隐藏
    const STATUS_DEL = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time'], function ($attribute) {
                if (!is_int($this[$attribute]) && preg_match('/^[\d- :]+$/', $this[$attribute])) {
                    $this[$attribute] = strtotime($this[$attribute]);
                }
            }],
            [['title', 'main_pic'], 'required'],
            ['title', 'string', 'max' => 32],
            [['main_pic'], 'string', 'max' => 128],
            [['read_count','start_time','share_count'], 'default', 'value' => 0],
            [['content','start_time','status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'title' => '标题',
            'main_pic' => '缩略图',
            'content' => '内容',
            'status' => '状态',
            'start_time' => '开始时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @inheritdoc
     */
//    public function afterSave($insert, $changedAttributes)
//    {
//        if (isset($changedAttributes['video'])) {
//
//            (new AliyunOssApi())->deleteFile($changedAttributes['video']);
//        }
//        parent::afterSave($insert, $changedAttributes);
//    }
}
