<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品溯源视频
 * Class GoodsTraceVideo
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 分类编号
 * @property string $desc 商品描述
 * @property integer $gid 商品编号
 * @property string $name 名称
 * @property string $cover_image 封面图片地址
 * @property string $video 视频地址
 * @property string $status 状态
 * @property integer $sid 视频所属店铺id
 * @property integer $read_count 阅读次数
 * @property integer $start_time  开始时间
 * @property integer $create_time 创建时间
 */
class GoodsTraceVideo extends ActiveRecord
{
    const STATUS_OK = 1; // 正常
    const STATUS_HIDE = 9; // 隐藏
    const STATUS_DEL = 0; // 删除

    const TYPE_BUY  = 1; // 带货视频
    const TYPE_MARKETING = 2; //  营销课堂
    const TYPE_SHORT = 3; // 宣传短片
    const TYPE_IMAGE = 4; // 形象短片

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
            [['name', 'cover_image', 'video'], 'required'],
            ['name', 'string', 'max' => 32],
            [['cover_image', 'video'], 'string', 'max' => 128],
            [['desc'], 'string', 'max' => 512],
            [['start_time'], 'default', 'value' => 0],
            [['gid','read_count','start_time','cid', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '名称',
            'cid' => '类型',
            'desc' => '描述',
            'gid' => '关联商品',
            'cover_image' => '封面',
            'video' => '视频',
            'status' => '状态',
            'start_time' => '开始时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (isset($changedAttributes['video'])) {

            (new AliyunOssApi())->deleteFile($changedAttributes['video']);
        }
        parent::afterSave($insert, $changedAttributes);
    }
}
