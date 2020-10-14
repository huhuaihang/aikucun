<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 图文素材
 * Class GoodsTraceVideo
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 分类编号
 * @property string $desc 商品描述
 * @property integer $gid 商品编号
 * @property string $name 名称
 * @property string $img_list 九宫格图片json列表
 * @property integer $status 状态
 * @property integer $read_count 阅读次数
 * @property integer $start_time 开始时间
 * @property integer $create_time 创建时间
 */
class GoodsSource extends ActiveRecord
{
    const TYPE_GOODS  = 1; // 商品推广
    const TYPE_MARKETING = 2; //  营销素材

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
            [['name', 'desc','img_list'], 'required'],
            ['name', 'string', 'max' => 32],
            [['desc'], 'string', 'max' => 512],
            [['start_time'], 'default', 'value' => 0],
            [['gid','read_count','start_time','cid','status'], 'safe'],
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
            'img_list' => '图片列表',
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
