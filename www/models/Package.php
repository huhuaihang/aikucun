<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 系统消息
 * Class Notice
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 礼包名称
 * @property integer $count 数量
 * @property float $price 原价
 * @property float $package_price 活动价
 * @property integer $status 状态
 * @property integer $create_time 时间
 * @property string $remark 备注
 */
class Package extends ActiveRecord
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
            [['name', 'count', 'price', 'package_price'], 'required'],
            ['name', 'string', 'max' => 128],
            [['price', 'package_price'], 'double'],
            [['remark', 'status', 'create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '套餐名称',
            'count' => '套餐礼包数量',
            'price' => '价格',
            'package_price' => '活动价',
            'create_time' => '创建时间',
            'remark' => '备注',
            'status' => '状态',
        ];
    }
}
