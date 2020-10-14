<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品优惠券活动
 * Class GoodsCouponGift
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $gid 商品编号
 * @property integer $cid 优惠活动编号
 * @property string $name 附赠品名字
 * @property string $pic 附赠品图片
* @property  float $price 附赠品价格
 * @property string $thumb_pic 附赠品缩略图片
 * @property string $remark 备注
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property GoodsCouponRule $rule 关联优惠券活动
 * @property Goods $goods 关联商品
 */
class GoodsCouponGift extends ActiveRecord
{
    const STATUS_OK = 1; // 正常
    const STATUS_HIDE = 9; // 暂停
    const STATUS_DEL = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'pic','price','thumb_pic'], 'required'],
            ['name', 'string', 'max' => 128],
            ['pic', 'string', 'max' => 256],
            [['remark', 'status'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gid' => '商品编号',
            'cid' => '优惠活动编号',
            'name' => '附赠品名字',
            'pic' => '附赠品图片',
            'thumb_pic' => '缩略图',
            'remark' => '备注',
            'price' => '展示价格',
            'status' => '状态',
        ];
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getRule()
    {
        return $this->hasOne(GoodsCouponRule::className(), ['id' => 'cid']);
    }

    /**
     * 关联商品
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }
}
