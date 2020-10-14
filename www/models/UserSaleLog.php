<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_sale_log".
 *
 * @property int $id
 * @property int $uid
 * @property int $to_uid
 * @property int $oid
 * @property int $gid
 * @property string $remark
 * @property int $create_time
 *
 * @property User $user // 关联卖礼包的卖家店主服务商
 * @property User $toUser // 关联卖给的用户信息
 * @property Goods $goods // 关联礼包商品
 * @property Order $order // 关联订单
 */
class UserSaleLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_uid', 'uid', 'oid', 'gid', 'create_time'], 'integer'],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['to_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_uid' => 'id']],
            [['gid'], 'exist', 'skipOnError' => true, 'targetClass' => Goods::className(), 'targetAttribute' => ['gid' => 'id']],
            [['oid'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['oid' => 'id']],
            [['oid', 'gid', 'remark'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_uid' => 'To Uid',
            'uid' => 'From Uid',
            'gid' => '商品',
            'oid' => '商品',
            'remark' => '备注',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::className(), ['id' => 'gid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'oid']);
    }
}
