<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 运费模板
 * Class DeliverTemplate
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $se_id 店铺物流编号
 * @property integer $gid 物理模板专属商品id
 * @property integer $name 名称
 * @property integer $is_default 是否为默认
 * @property integer $use_weight 是否启用重量计费
 * @property float $weight_start 首重量
 * @property float $weight_start_fee 首重量费
 * @property float $weight_extra 续重量
 * @property float $weight_extra_fee 续重量费
 * @property integer $use_bulk 是否启用体积计费
 * @property float $bulk_start 首体积
 * @property float $bulk_start_fee 首体积费
 * @property float $bulk_extra 续体积
 * @property float $bulk_extra_fee 续体积费
 * @property integer $use_count 是否启用件数计费
 * @property integer $count_start 首件数
 * @property float $count_start_fee 首件数费
 * @property integer $count_extra 续件数
 * @property float $count_extra_fee 续件数费
 * @property string $pid_list 省编号列表JSON
 * @property string $cid_list 市编号列表JSON
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property ShopExpress $shopExpress  关联商户物流公司关联关系
 */
class DeliverTemplate extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_STOP = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 32],
            [['is_default', 'se_id', 'use_weight', 'weight_start',
                'weight_start_fee', 'weight_extra', 'weight_extra_fee', 'use_bulk', 'bulk_start', 'bulk_start_fee',
                'bulk_extra', 'bulk_extra_fee', 'use_count', 'count_start', 'count_start_fee', 'count_extra', 'count_extra_fee',
                'pid_list', 'cid_list', 'status', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'se_id' => '物流公司',
            'name' => '模板名称',
            'is_default' => '是否默认',
            'use_weight' => '是否启用重量运费',
            'weight_start' => '首重(kg)',
            'weight_start_fee' => '首重运费(元)',
            'weight_extra' => '续重(kg)',
            'weight_extra_fee' => '续重运费(元)',
            'use_bulk' => '是否启用体积计算运费',
            'bulk_start' => '首体积量(m³)',
            'bulk_start_fee' => '首体积运费(元)',
            'bulk_extra' => '续体积量(m³)',
            'bulk_extra_fee' => '续体积运费(元)',
            'use_count' => '是否启用计件方式运费',
            'count_start' => '首件(件)',
            'count_start_fee' => '首件运费(元)',
            'count_extra' => '续件(件)',
            'count_extra_fee' => '续件运费(元)',
            'pid_list' => 'Pid List',
            'cid_list' => 'Cid List',
            'status' => '状态',
            'create_time' => '创建时间',
        ];
    }


    /**
     * 获取店铺物流公司关联
     * @return \yii\db\ActiveQuery
     */
    public function getShopExpress()
    {
        return $this->hasOne(ShopExpress::className(), ['id' => 'se_id']);
    }
}
