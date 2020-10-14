<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品优惠券活动
 * Class GoodsCouponRule
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property string $name 优惠券活动名称
 * @property integer $count 优惠券数量
 * @property float $price 优惠券金额
 * @property string $remark 备注
 * @property integer $status 是否带赠品
 * @property integer $create_time 创建时间
 */
class GoodsCouponRule extends ActiveRecord
{
    const STATUS_OK = 1; // 是
    const STATUS_NO = 0; // 否


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','price','count'], 'required'],
            ['name', 'string', 'max' => 128],
            ['status','default', 'value' => 0],
            ['count', 'integer'],
            ['price', 'double'],
            [['remark','create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => '优惠券说明',
            'gid' => '商品编号',
            'count' => '优惠券数量',
            'price' => '优惠券金额',
            'remark' => '备注',
            'status' => '是否附带赠品',
        ];
    }
}
