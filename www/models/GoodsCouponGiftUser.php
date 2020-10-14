<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品优惠券活动
 * Class GoodsCouponGiftUser
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 优惠券活动编号
 * @property integer $gid 商品编号
 * @property integer $uid 用户编号
 * @property string $remark 备注
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property integer $use_time 使用时间
 *
 * @property GoodsCouponRule $rule 关联优惠券活动
 * @property Goods $goods 关联商品
 * @property User $user 关联用户
 */
class GoodsCouponGiftUser extends ActiveRecord
{
    const STATUS_WAIT = 1; // 待使用
    const STATUS_LOCK = 2; // 已锁定
    const STATUS_USED = 3; // 已使用
    const STATUS_DELETE = 0; // 已删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'gid', 'uid'], 'required'],
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
            'uid' => '用户编号',
            'remark' => '备注',
            'status' => '状态',
        ];
    }

    /**
     * 关联优惠券
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

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }
}
