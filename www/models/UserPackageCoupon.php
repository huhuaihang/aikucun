<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户礼包券
 * Class UserPackageCoupon
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $oid 获取订单编号
 * @property integer $use_oid 兑换订单编号
 * @property integer $create_time 创建时间
 * @property integer $over_time 过期时间
 * @property integer $status 状态
 *
 * @property User $user 关联用户
 * @property Order $order 关联订单
 */
class UserPackageCoupon extends ActiveRecord
{
    const STATUS_OK = 1; // 正常
    const STATUS_USED = 2; // 已使用
    const STATUS_HIDE = 9; // 已取消
    const STATUS_DELETE = 0; // 已过期

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
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
     * 定时任务：卡券自动过期
     */
    public static function task_coupon_expired()
    {
        $current_time = time();
        foreach (UserPackageCoupon::find()
                     ->andWhere(['status' => UserPackageCoupon::STATUS_OK])
                     ->each() as $coupon) {/** @var UserPackageCoupon $coupon */
            if ($coupon->over_time < $current_time ) {
                $coupon->status = UserPackageCoupon::STATUS_DELETE;
                $coupon->save(false);

                \Yii::warning('卡券[' . $coupon->id . ']，创建时间[' . date('Y-m-d H:i:s', $coupon->create_time) . ']，自动设置卡券过期。');
            }
        }
        return '订单自动取消任务执行完成。';
    }

}
