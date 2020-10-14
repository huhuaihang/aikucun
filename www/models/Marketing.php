<?php

namespace app\models;

use app\modules\api\models\ApiException;
use app\modules\api\models\ErrorCode;
use yii\base\Model;
use yii\db\Exception;

/**
 * 营销
 * Class Marketing
 * @package app\models
 */
class Marketing extends Model
{

    /**
     * 计算减折价
     * @param $item_list array 购物列表
     * @param $uid integer 用户编号
     * @param $is_pay bool 判断是否是订单支付时
     * @return array
     */
    public static function calcDiscount(&$item_list, $uid=null,$is_pay=false)
    {
        $discountMoney=0;//限时购优惠总金额
        // 合并不同规格的商品数量
        $gidAmount = [];
        foreach ($item_list as $item) {
            if (!isset($gidAmount[$item['goods']['id']])) {
                $gidAmount[$item['goods']['id']] = 0;
            }
            $gidAmount[$item['goods']['id']] += $item['amount'];
        }
        foreach ($gidAmount as $gid => $amount) {
            /** @var DiscountGoods $discountGoods */
            $discountGoods = DiscountGoods::find()
                ->joinWith('discount')
                ->andWhere(['status' => Discount::STATUS_RUNNING])
                ->andWhere(['<=', 'start_time', time()])
                ->andWhere(['>=', 'end_time', time()])
                ->andWhere(['gid' => $gid])
                ->orderBy(['{{%discount}}.id' => SORT_ASC])
                ->one();
            if(!empty($discountGoods->hour))
            {
               if($discountGoods->discount->start_time + $discountGoods->hour * 3600 < time())
                {
                    continue;
                }
            }
            if (empty($discountGoods)) {
                continue;
            }
            if ($discountGoods->amount > 0) { // 总数限制
                if($is_pay == false)
                {
                $sale_amount=$discountGoods->getSaleAmount(1);// 已卖出的份数(包含未支付)
                }else{
                $sale_amount=$discountGoods->getSaleAmount();// 已卖出的份数
                }

                if ($amount + $sale_amount > $discountGoods->amount) {
                    $goods = Goods::findOne(['id' => $gid]);
                    $sale_amount_pd  = $discountGoods->getSaleAmount();// 判断是否存在未支付数量
                    if ($amount + $sale_amount_pd <= $discountGoods->amount) {
                        return [
                            'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                            'message' => '您购买的限时抢购商品[' . $goods->title . ']已被抢光，但是还存在未支付订单，还有机会哦。',
                        ];
                    }
                    return [
                        'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                        'message' => '您购买的限时抢购商品[' . $goods->title . ']已售罄。',
                    ];
                }
            }
            $buy_limit = $discountGoods->discount->buy_limit;
            if ($buy_limit > 0 && !empty($uid)) { // 个人限购
                $bought_amount = OrderItem::find()->alias('order_item')
                    ->joinWith('order order')
                    ->andWhere(['order.uid' => $uid])
                    ->andWhere(['order_item.gid' => $gid])
                    ->andWhere(['>=', 'order.create_time', $discountGoods->discount->start_time])
                    ->andWhere(['<=', 'order.create_time', $discountGoods->discount->end_time])
                    ->andWhere(['not in', 'order.status', [Order::STATUS_CANCEL,Order::STATUS_CREATED, Order::STATUS_DELETE]])
                    ->andWhere(['>', 'order.discount_money', 0])
                    ->sum('order_item.amount'); // 已经购买的份数
                if ($amount + $bought_amount > $buy_limit) {
                    $goods = Goods::findOne(['id' => $gid]);
                    return [
                        'error_code' => ErrorCode::PARAM,
                        'message' => '您购买的商品[' . $goods->title . ']每人限购[' . $buy_limit . ']件。',
                    ];
                }
            }
            //判断是否是订单支付
            if($is_pay==false)
            {
            foreach ($item_list as $index => $item) {
                if ($item_list[$index]['goods']['id'] != $gid) {
                    continue;
                }
                $item_list[$index]['did'] = $discountGoods->did;
                if ($discountGoods->type == DiscountGoods::TYPE_PRICE) {
                    $discountPrice = $discountGoods->price;
                    if (empty($item['sku'])) {
                        if (Util::comp(round($item_list[$index]['goods']['price']-$discountPrice-$item_list[$index]['goods']['self_price'], 2), 0, 2) <= 0) {
                            return [
                                'error_code' => ErrorCode::PARAM,
                                'message' => '选择商品总价低于0不能下单，请重新选择。',
                            ];
                        }
                        $item_list[$index]['discountMoney'] = Util::money($discountPrice * $item_list[$index]['amount']);
                        $item_list[$index]['goods']['price'] = Util::money($item_list[$index]['goods']['price'] - $discountPrice);
                    } else {
                        if (Util::comp(round($item_list[$index]['sku']['price']-$discountPrice-$item_list[$index]['sku']['self_price'], 2), 0, 2) <= 0) {
                            return [
                                'error_code' => ErrorCode::PARAM,
                                'message' => '选择商品总价低于0不能下单，请重新选择。',
                            ];
                        }
                        $item_list[$index]['discountMoney'] = Util::money($discountPrice * $item_list[$index]['amount']);
                        $item_list[$index]['sku']['price'] = Util::money($item_list[$index]['sku']['price'] - $discountPrice);
                    }
                } else {
                    if (empty($item['sku'])) {
                        if (Util::comp(round($item_list[$index]['goods']['price'] * ($discountGoods->ratio/10)-$item_list[$index]['goods']['self_price'], 2), 0, 2) <= 0) {
                            return [
                                'error_code' => ErrorCode::PARAM,
                                'message' => '选择商品总价低于0不能下单，请重新选择。',
                            ];
                        }
                        $item_list[$index]['discountMoney'] = Util::money(round(($item_list[$index]['goods']['price'] * (10-$discountGoods->ratio)/10),2) * $item_list[$index]['amount']);
                        $item_list[$index]['goods']['price'] = Util::money($item_list[$index]['goods']['price'] * ($discountGoods->ratio/10));
                    } else {
                        if (Util::comp(round($item_list[$index]['sku']['price'] * ($discountGoods->ratio/10)-$item_list[$index]['sku']['self_price'], 2), 0, 2) <= 0) {
                            return [
                                'error_code' => ErrorCode::PARAM,
                                'message' => '选择商品总价低于0不能下单，请重新选择。',
                            ];
                        }
                        $item_list[$index]['discountMoney'] = Util::money(round($item_list[$index]['sku']['price'] * ((10-$discountGoods->ratio)/10),2) * $item_list[$index]['amount']);
                        $item_list[$index]['sku']['price'] = Util::money($item_list[$index]['sku']['price'] * ($discountGoods->ratio/10));
                    }
                }
                $discountMoney+=$item_list[$index]['discountMoney'];
            }
            }
        }
        return [
            'discountMoney'=>$discountMoney,
        ];
    }

    /**
     * 计算活动优惠券
     * @param $order Order 订单
     * @param $order_item  OrderItem 订单商品
     * @param $gift_id  integer 赠品编号
     * @param $share_commission_value double 分佣金额
     * @param $user User 用户
     * @throws
     */
    public static function calcCoupon(&$share_commission_value,&$order,$order_item,$user,$gift_id)
    {
                $share_commission_value = 0;
                $coupon_rule = GoodsCouponRule::find()->where(['gid' => $order_item->goods->id])->one();
                //首次购买以及使用最后一张优惠券 送赠品
                $coupon_count= GoodsCouponGiftUser::find()
                    ->where(['status' => GoodsCouponGiftUser::STATUS_WAIT])
                    ->andWhere(['uid'=>$user->id])
                    ->andWhere(['gid'=>$order_item->goods->id])
                    ->count();
                /** @var $coupon_rule GoodsCouponRule */
                if (!empty($gift_id) && $coupon_rule->status == GoodsCouponRule::STATUS_OK && $coupon_count <=1) {
                    $order->gift_id = $gift_id;
                }
                $order->is_coupon = 1;
                /** @var $coupon GoodsCouponGiftUser */
                $coupon = GoodsCouponGiftUser::find()
                    ->andWhere(['and', ['uid' => $user->id], ['gid' => $order_item->goods->id], ['status' => GoodsCouponGiftUser::STATUS_WAIT]])
                    ->one();
                if (!empty($coupon)) {
                    $order->coupon_money = $coupon->rule->price;
                    $order->amount_money -= $coupon->rule->price;
                    $order->coupon_id = $coupon->id;
                    $coupon->status = GoodsCouponGiftUser::STATUS_LOCK;
                    $r = $coupon->save(false);
                    if (!$r) {
                        throw new Exception('优惠券记录更新失败。');
                    }
                }

    }




}
