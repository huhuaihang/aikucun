<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 用户佣金
 * Class UserCommission
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $from_uid 来源用户编号
 * @property integer $level 级别
 * @property integer $type 类型
 * @property integer $oid 订单号
 * @property integer $oiid 订单商品信息
 * @property float $commission 佣金
 * @property integer $time 时间
 * @property string $remark 备注
 *
 * @property User $user 关联用户
 * @property User $fromUser 关联用户
 * @property Order $order 关联订单
 * @property OrderItem $orderItem 关联订单商品信息
 */
class UserCommission extends ActiveRecord
{
    const TYPE_FIRST = 1; // 直接一级下单购买
    const TYPE_MONTH = 2; // 直接一级或者育成店主或者育成服务商或者会员团队 月佣金

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_uid']);
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
     * 关联订单商品信息
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::className(), ['id' => 'oiid']);
    }

    /**
     * 根据订单列表 计算佣金
     * @param Order[] $order_list  订单列表
     * @param User $user
     * @param $type integer
     * @return array $commission_list
     */
    public function compute($order_list, $user, $type)
    {
        $commission_list =[];
        foreach ($order_list as $model) {
            $from_user = User::findOne($model->uid);
            $commission = 0 ;
            $share_commission_ratio_1 = $user->childBuyRatio;
            $share_commission_ratio_2 = 0;
            if ($model->user->status == User::STATUS_OK) {
                $share_commission_ratio_2 = $model->user->buyRatio;
            }
            if ($model->user->status == User::STATUS_OK) {
                $share_commission_ratio_1 = 30;
            }
            if ($model->user->status == User::STATUS_OK && $user->status == User::STATUS_WAIT) {
                continue;
            }
            if (MerchantFinancialSettlement::find()->where(['oid' => $model->id])->exists()) {
                continue;
            }
            /** @var OrderItem $item */
            $is_refund = 0;

            foreach ($model->itemList as $item) {
                /** @var OrderRefund $refund */
                $refund = OrderRefund::find()->where(['oid' => $model->id, 'oiid' => $item->id])->one();
                if (!empty($refund)) {
                    $is_refund = 1;
                }
                if (!empty($refund) && $refund->status != 9 && $type == 2) {
                    // 已售后不再给佣金
                    $is_refund = 1;
                    continue;
                }
                if (!empty($refund) && $refund->status > 5 && $type == 3) {
                    $is_refund = 1;
                    continue;
                }
                if (!in_array($item->goods->share_commission_type, [Goods::SHARE_COMMISSION_TYPE_MONEY, Goods::SHARE_COMMISSION_TYPE_RATIO])) {
                    // 此商品不参与分享佣金
                    continue;
                }
                if ($item->goods->is_pack == 1) {
                    continue;
                }
                // 一级分享
                if (empty($share_commission_ratio_1) || Util::comp($share_commission_ratio_1, 0, 2) <= 0) {
                    // 店铺没有设置一级分享佣金比例
                    continue;
                }
                $item_commission_1 = 0;
                $sku=$item->goodsSku;//多规格佣金设置
                if ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_MONEY) { // 固定金额
                    //$item_commission_1 = round($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100, 2);
                    if ($share_commission_ratio_2 != 0) {
                        if (empty($sku) || $sku->commission == '') {
                            $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                        } else {
                            $item_commission_1 = round(($sku->commission * $share_commission_ratio_2 * $item->amount / 100) * $share_commission_ratio_1 / 100, 2);
                        }
                    } else {
                        if (empty($sku) || $sku->commission == '') {
                            $item_commission_1 = round(($item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 100), 2);
                        } else {
                            $item_commission_1 = round(($sku->commission * $share_commission_ratio_1 * $item->amount / 100), 2);
                        }
                    }
                } elseif ($item->goods->share_commission_type == Goods::SHARE_COMMISSION_TYPE_RATIO) { // 百分比
                    if (empty($sku) || $sku->commission == '') {
                        $item_commission_1 = round($item->price * $item->goods->share_commission_value * $share_commission_ratio_1 * $item->amount / 10000, 2);
                    } else {
                        $item_commission_1 = round($item->price * $sku->commission * $share_commission_ratio_1 * $item->amount / 10000, 2);
                    }
                }
                if (Util::comp($item_commission_1, 0, 2) > 0) {
                    $commission += $item_commission_1;
                }
                //echo $commission .chr(10);
            }
            $status_str = '';
            switch ($type) {
                case $type == 1:
                    $status_str = '已结算';
                    break;
                case $type == 2:
                    $status_str = '未结算';
                    break;
                case $type == 3:
                    $status_str = '已售后';
                    break;
            }
            if ($item->goods->is_pack != 1 && (($is_refund == 0 && $type == 2) || ($is_refund == 1 && $type == 3))) {
                $user_level = $user->userLevel;
                $from_user_level = $from_user->userLevel;
                $commission_list[] = [
                    'id' => $model->id,
                    'logo' => $from_user->getRealAvatar(true),
                    'nickname' => !empty($from_user->nickname) ? $from_user->nickname  : $from_user->real_name,
                    'level_logo' => ($from_user->status == User::STATUS_OK) ? (!empty($user_level) ? Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$from_user_level->logo : '') : '',
                    'commission' => sprintf('%.2f', $commission),
                    'time' => $model->create_time,
                    'status_str' => $status_str,
                    'remark' => '',
                    'order' => [
                        'no' => $item->order->no,
                        'main_pic' => Util::fileUrl($item->goods->main_pic),
                        'title' => $item->goods->title,
                        'amount' => $item->amount,
                        'price' => $item->price * $item->amount,
                        'commission' => sprintf('%.2f', $commission),
                    ],
                ];
            }
        }

        return $commission_list;
    }
}
