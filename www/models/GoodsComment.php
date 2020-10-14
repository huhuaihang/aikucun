<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商品评论
 * Class GoodsComment
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $pid 上级编号，追加评论填写追加的上级评论编号
 * @property integer $gid 商品编号
 * @property integer $uid 用户编号
 * @property integer $oid 订单编号
 * @property string $sku_key_name 商品SKU属性值中文
 * @property integer $score 评分值
 * @property string $img_list 图片列表JSON
 * @property string $content 评论内容
 * @property integer $is_anonymous 是否匿名
 * @property integer $status 状态
 * @property integer $is_reply 是否已回复
 * @property integer $create_time 创建时间
 *
 * @property User $user 关联用户
 * @property Goods $goods 关联商品
 * @property Order $order 关联订单
 */
class GoodsComment extends ActiveRecord
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
            [['is_anonymous', 'is_reply', 'score'], 'default', 'value' => 0],
            [['gid', 'uid', 'oid', 'score', 'status', 'is_reply', 'create_time'], 'required'],
            [['img_list', 'content'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'gid' => '商品编号',
            'uid' => '用户编号',
            'oid' => '订单编号',
            'sku_key_name' => '商品SKU属性值中文',
            'score' => '评分值',
            'img_list' => '图片列表JSON',
            'content' => '评论内容',
            'is_anonymous' => '是否匿名',
            'status' => '状态',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
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
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }

    /**
     * 返回图片列表
     * @return array
     */
    public function getImgList()
    {
        if (empty($this->img_list)) {
            return [];
        }
        return json_decode($this->img_list, true);
    }
}
