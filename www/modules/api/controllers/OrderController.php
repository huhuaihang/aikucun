<?php

namespace app\modules\api\controllers;

use app\models\AlipayApi;
use app\models\AllInPayAliApi;
use app\models\AllInPayApi;
use app\models\AllInPayH5Api;
use app\models\City;
use app\models\FinanceLog;
use app\models\Goods;
use app\models\GoodsComment;
use app\models\GoodsCouponGift;
use app\models\GoodsCouponGiftUser;
use app\models\GoodsCouponRule;
use app\models\GoodsExpress;
use app\models\GoodsSku;
use app\models\KeyMap;
use app\models\Marketing;
use app\models\MerchantMessage;
use app\models\Order;
use app\models\OrderDeliver;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\PinganApi;
use app\models\Shop;
use app\models\ShopConfig;
use app\models\ShopScore;
use app\models\SupplierConfig;
use app\models\System;
use app\models\SystemMessage;
use app\models\SystemVersion;
use app\models\User;
use app\models\UserAccount;
use app\models\UserAccountLog;
use app\models\UserAddress;
use app\models\UserCart;
use app\models\UserCommission;
use app\models\UserConfig;
use app\models\Util;
use app\models\WeixinAppApi;
use app\models\WeixinH5Api;
use app\models\WeixinMpApi;
use app\models\YOWeixinMpApi;
use app\models\YWeixinMpApi;
use app\modules\api\models\ErrorCode;
use stdClass;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * 订单控制器
 * Class OrderController
 * @package app\modules\api\controllers
 */
class OrderController extends BaseController
{
    /**
     * 订单列表
     * GET
     * search_status 状态码
     */
    public function actionList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = Order::find();
        $query->andWhere(['<>', 'status', Order::STATUS_DELETE]);
        $query->andWhere(['uid' => $user->id]);
        if ($this->get('search_status') == Order::STATUS_RECEIVED) {
            $query->andFilterWhere(['in', 'status', [$this->get('search_status'), Order::STATUS_COMPLETE]]);
        } else {
            $query->andFilterWhere(['status' => $this->get('search_status')]);
        }

        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset);
        $query->limit($pagination->limit);
        $order_list = [];
        $is_pack = 0;
        $gift_info = [];//活动赠品
        foreach ($query->each() as $order) {
            /** @var Order $order */
            $item_list = [];
            foreach ($order->itemList as $orderItem) {
                $item_list[] = [
                    'id' => $orderItem->id,
                    'goods' => [
                        'id' => $orderItem->goods->id,
                        'title' => $orderItem->goods->title,
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $orderItem->goods->main_pic,
                        'is_supplier' => ($orderItem->goods->supplier_id && $orderItem->goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                    ],
                    'amount' => $orderItem->amount,
                    'price' => $orderItem->price,
                    'sku_key_name' => $orderItem->sku_key_name,
                ];

                if ($orderItem->goods->is_pack == 1 && $user->is_self_active == 1 && $user->handle_time >= $order->create_time) {
                    $is_pack = 1;
                }
                if ($orderItem->goods->is_pack == 1 && $user->is_per_handle == 1 && $user->handle_time >= $order->create_time) {
                    $is_pack = 1;
                }

            }
            if (!empty($order->gift_id)) {
                $gift = GoodsCouponGift::findOne($order->gift_id);
                $gift_info = [
                    'title' => System::getConfig('ground_push_active_name'),
                    'name' => $gift->name,
                    'price' => $gift->price,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $gift->thumb_pic,
                    'amount' => 1,
                ];
            }
            $order_list[] = [
                'no' => $order->no,
                'shop' => [
                    'id' => $order->shop->id,
                    'name' => $order->shop->name,
                    'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($order->shop->id, 'logo'),
                ],
                'item_list' => $item_list,
                'status' => $order->status,
                'status_str' => KeyMap::getValue('order_status', $order->status),
                'amount_money' => $order->amount_money,
                'deliver_fee' => $order->deliver_fee,
                'payment_method' => !empty($order->fid) ? $order->financeLog->pay_method : 0,
                'cancel_fid' => $order->cancel_fid,
                'is_pack' => $is_pack,
                'is_score' => $order->is_score,
                'pack_coupon_status' => $order->pack_coupon_status,
                'is_coupon' => $order->is_coupon,
                'gift' => $gift_info,
            ];
        }
        return [
            'order_list' => $order_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 刷新订单支付状态
     * GET
     * order_no 订单号
     */
    public function actionRefreshFinanceStatus()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        if (empty($order->fid)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '订单没有关联财务信息。',
            ];
        }
        try {
            if ($order->financeLog->refreshStatus()) {
                return [];
            }
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '无法刷新财务状态。',
            ];
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 订单详情
     * GET
     * order_no 订单号
     */
    public function actionDetail()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        if ($this->get('refresh') == 1 && $order->status == Order::STATUS_CREATED) {
            // 刷新订单状态
            $r = $this->actionRefreshFinanceStatus();
            if (!empty($r)) {
                return $r;
            }
        }

        $item_list = [];
        $is_pack = 0;
        $gift_info = [];
        //活动赠品
        if (!empty($order->gift_id)) {
            $gift = GoodsCouponGift::findOne($order->gift_id);
            $gift_info = [
                'title' => System::getConfig('ground_push_active_name'),
                'name' => $gift->name,
                'price' => $gift->price,
                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $gift->thumb_pic,
                'amount' => 1,
            ];

        }

        foreach ($order->itemList as $orderItem) {
            $item_list[] = [
                'id' => $orderItem->id,
                'sku_key_name' => $orderItem->sku_key_name,
                'amount' => $orderItem->amount,
                'price' => $orderItem->price,
                'goods' => [
                    'id' => $orderItem->goods->id,
                    'title' => $orderItem->goods->title,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $orderItem->goods->main_pic,
                    'is_pack' => $orderItem->goods->is_pack,
                    'is_supplier' => ($orderItem->goods->supplier_id && $orderItem->goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                ],
            ];
            if ($orderItem->goods->is_pack == 1 && $user->is_self_active == 1 && $user->handle_time >= $order->create_time) {
                $is_pack = 1;
            }
            if ($orderItem->goods->is_pack == 1 && $user->is_per_handle == 1 && $user->handle_time >= $order->create_time) {
                $is_pack = 1;
            }
        }
        //判断是否卡券购买订单 不显示地址
        if ($order->pack_coupon_status != 1) {
            $address = $order->getDeliverInfoJson();
            $city = City::findByCode($address['area']);
            if (empty($city)) {
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '没有找到编号为[' . $address['area'] . ']的城市信息。',
                ];
            }
            $address['city'] = $city->address();
        }
        return [
            'order' => [
                'no' => $order->no,
                'status' => $order->status,
                'status_str' => KeyMap::getValue('order_status', $order->status),
                'amount_money' => $order->amount_money,
                'goods_money' => $order->goods_money,
                'create_time' => $order->create_time,
                'fid' => intval($order->fid),
                'user_remark' => $order->user_remark,
                'finance_log' => empty($order->fid) ? new stdClass() : [
                    'trade_no' => $order->financeLog->trade_no,
                    'money' => $order->financeLog->money,
                    'pay_method' => $order->financeLog->pay_method,
                    'status' => $order->financeLog->status,
                    'update_time' => $order->financeLog->update_time,
                ],
                'address' => empty($address) ? new stdClass() : $address,
                'deliver_fee' => $order->deliver_fee,
                'shop' => [
                    'id' => $order->shop->id,
                    'name' => $order->shop->name,
                    'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($order->shop->id, 'logo'),
                    'service_tel' => ShopConfig::getConfig($order->shop->id, 'service_tel'),
                ],
                'item_list' => $item_list,
                'is_pack' => $is_pack,
                'self_buy_money' => is_null($order->self_buy_money) ? '0.00' : $order->self_buy_money,
                'score' => $order->score,
                'is_score' => $order->is_score,
                'is_coupon' => $order->is_coupon,
                'coupon_money' => $order->coupon_money,
                'gift' => empty($gift_info) ? new stdClass() : $gift_info,
                'pack_coupon_status' => $order->pack_coupon_status,
            ],
        ];
    }

    /**
     * 删除订单
     * GET
     * order_no 订单号
     */
    public function actionDelete()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        /** @var Order $order */
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->status == Order::STATUS_DELETE || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        //if ($order->status != Order::STATUS_CANCEL && $order->status != Order::STATUS_CREATED) {
        if (!in_array($order->status, [Order::STATUS_CANCEL, Order::STATUS_CREATED, Order::STATUS_COMPLETE])) {
            return [
                'error_code' => ErrorCode::ORDER_DELETE_DENIED,
                'message' => '订单状态不允许删除。',
            ];
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($order->is_score == 1 && $order->status == Order::STATUS_CREATED) {
                $order->saveUserOrderScore($order->uid, $order->id, $order->score, $order->itemList[0]->title . '商品售后，积分返还');
            }
            // 优惠券订单  还原优惠券未使用状态
            if ($order->is_coupon == 1 && $order->status == Order::STATUS_CREATED && !empty($order->coupon_id)) {
                $order->saveUserGoodsCoupon($order->coupon_id);
            }

            $order->status = Order::STATUS_DELETE;
            $order->delete_time = time();
            $order->save(false);

            $trans->commit();
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '删除订单。', json_encode($order->attributes));
            return [];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 取消订单
     * GET
     * order_no 订单号
     */
    public function actionCancel()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'eror_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        foreach ($order->itemList as $item) {
            if ($item->goods->is_pack == 1 && $user->status == User::STATUS_WAIT) {
                return [
                    'error_code' => ErrorCode::ORDER_NOT_FOUND,
                    'message' => '大礼包订单不能取消。',
                ];
            }
        }
        if ($order->status > Order::STATUS_DELIVERED) {
            return [
                'error_code' => ErrorCode::ORDER_CANCEL_DENIED,
                'message' => '订单状态不允许取消。',
            ];
        }
        if (empty($order->fid)) {
            $order->status = Order::STATUS_CANCEL;
        } else {
            $order->status = Order::STATUS_CANCEL_WAIT_MERCHANT;
        }

        $order->save(false);
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '申请取消订单。');
        return [];
    }

    /**
     * 确认收货
     * GET
     * order_no 订单号
     */
    public function actionReceived()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        if ($order->status != Order::STATUS_DELIVERED) {
            return [
                'error_code' => ErrorCode::ORDER_RECEIVED_DENIED,
                'message' => '订单状态错误不能确认收货。',
            ];
        }
        $order->status = Order::STATUS_RECEIVED;
        $order->receive_time = time();
        $order->save(false);
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '确认收货。');
        return [];
    }

    /**
     * 催单
     * GET
     * order_no 订单号
     */
    public function actionHurry()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        /** @var  $merchant_message MerchantMessage */
        $merchant_message = new MerchantMessage();
        $merchant_message->mid = $order->shop->mid;
        $merchant_message->title = '买家催单';
        $merchant_message->content = '买家催单 订单编号:' . $order_no;
        $merchant_message->time = time();
        $merchant_message->status = SystemMessage::STATUS_UNREAD;
        if (!$merchant_message->save()) {
            return [
                'error_code' => ErrorCode::ORDER_HURRY_FAIL,
                'message' => '商户消息发送失败。',
            ];
        }
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '催单。');
        return [];
    }

    /**
     * 返回生成订单需要的商品列表
     * @param $uid integer 用户编号
     * @param $type string 来源类型 cart | goods | order
     * @param $sid integer 购物车店铺编号
     * @param $cart string 购物车，格式gid^^amount^^sku_key_name$$$
     * @param $gid integer 直接下单的商品编号
     * @param $sku_key_name string 直接下单的商品规格
     * @param $amount integer 直接下单的商品数量
     * @param $order_no string 再次购买的订单号
     * @param $is_use_score integer 是否积分兑换
     * @return array array($shop, array('cart' => $cart, 'goods' => $goods, 'sku' => $sku, 'amount' => $amount),
     *           $score_amount_money , $amount_score_money , $goods_amount_money)
     */
    private function makeOrderList($uid, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no, $is_use_score)
    {
        $item_list = [];
        $score_amount_money = $self_amount_price = $goods_amount_money = $total_score = 0;
        if ($type == 'cart') { // 从购物车过来
            if (empty($sid) || empty($cart)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $shop = Shop::findOne($sid);
            if (empty($shop)) {
                return [
                    'error_code' => ErrorCode::ORDER_SHOP_NOT_FOUND,
                    'message' => '没有找到店铺信息。',
                ];
            }
            $user = User::findOne($uid);
            //自购省钱
            $selfBuyRatio = $user->buyRatio;
            foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
                $item = explode('^^', $item);
                $gid = $item[0];
                $amount = $item[1];
                $goods_title = Goods::findOne($gid);
                if ($amount <= 0) {
                    return [
                        'error_code' => ErrorCode::PARAM,
                        'message' => '提交商品[' . $goods_title->title . ']数量必须大于0。',
                    ];
                }
                $sku_key_name = count($item) > 2 ? $item[2] : '';
                $cart = UserCart::findByUGS($uid, $gid, $sku_key_name);
                if (empty($cart)) {
                    $cart = new UserCart();
                    $cart->uid = $uid;
                    $cart->gid = $gid;
                    $cart->sku_key_name = $sku_key_name;
                    $cart->amount = $amount;
                    $cart->create_time = time();
                    $cart->save();
                } else {
                    $cart->amount = $amount;
                }
                $goods = $cart->goods;
                if ($sid != $goods->sid) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品不是同一家店铺不能下单。',
                    ];
                }
                if ($goods->status != Goods::STATUS_ON) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']已下架或删除。',
                    ];
                }
                //当日商品总量限购
                $checkTodayGoodsLimit = Goods::checkTodayGoodsLimit($gid, $amount);
                if (!$checkTodayGoodsLimit) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']每天限购件，请明天再来。',
                    ];
                }
                //限购商品判断
                list($checkLimitGoods, $limit_amount) = Order::checkLimitGoods($uid, $gid, $amount);
                if (!$checkLimitGoods) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的[' . $goods->title . ']商品为畅销货物，<br>只可限购'.$goods->limit_amount.'件，请调整购买数量再次购买！',
                    ];
                }
                if ($limit_amount < 0) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']商品为畅销货物，<br>每日只可限购'.$goods->limit_amount.'件，请调整购买数量再次购买！',
                    ];
                }
                /** @var null|GoodsSku $sku */
                $sku = null;
                if (!empty($cart->sku_key_name)) {
                    $sku = GoodsSku::find()
                        ->andWhere(['gid' => $goods->id, 'key_name' => $sku_key_name])
                        ->one();
                    if (empty($sku)) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']不存在。',
                        ];
                    }
                    if ($sku->getStock() < $amount) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']库存不足。',
                        ];
                    }
                }
                if (empty($sku) && !empty($goods->skuList)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                    ];
                }
                if ($goods->getAllStock() < $amount) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']库存不足。',
                    ];
                }
                $item_list[] = [
                    'cart_id' => $cart->id,
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'price' => $goods->price,
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                        'is_supplier' => ($cart->goods->supplier_id && $cart->goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                        'supplier_price' => $goods->supplier_price
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                        'self_price' => $sku->commission == '' ? round($goods->share_commission_value * $selfBuyRatio / 100, 2) : round($sku->commission * $selfBuyRatio / 100, 2),
                        'supplier_price' => $sku->supplier_price == '' ? $goods->supplier_price : $sku->supplier_price
                    ],
                    'amount' => $cart->amount,
                ];
                if (empty($sku) || $sku->commission == '') {
                    $self_amount_price += round($goods->share_commission_value * $selfBuyRatio / 100 * $amount, 2);
                } else {
                    $self_amount_price += round($sku->commission * $selfBuyRatio / 100 * $amount, 2);
                }
                $goods_amount_money += empty($sku) ? round($goods->price * $amount, 2) :
                    round($sku->price * $amount, 2);
                if (Util::comp(round($goods_amount_money - $self_amount_price, 2), 0, 2) < 0) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '选择商品总价低于0不能下单，请重新选择。',
                    ];
                }
            }
        } elseif ($type == 'goods') { // 直接下单
            $user = User::findOne($uid);
            if (empty($gid) || $amount <= 0) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $goods = Goods::findOne($gid);
            if (empty($goods)) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '没有找到商品信息。',
                ];
            }
            if ($goods->status != Goods::STATUS_ON) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品已下架或删除。',
                ];
            }
            //当日商品总量限购
            $checkTodayGoodsLimit = Goods::checkTodayGoodsLimit($gid, $amount);
            if (!$checkTodayGoodsLimit) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']每天限购500件，请明天再来。',
                ];
            }
            //限购商品判断
            list($checkLimitGoods, $limit_amount) = Order::checkLimitGoods($uid, $gid, $amount);
            if (!$checkLimitGoods) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']每人每天限购2件，<br>请选择正确数量或者明天再来。',
                ];
            }
            if ($limit_amount < 0) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']每人每天限购，<br>请选择正确数量或者明天再来。',
                ];
            }
            if ($is_use_score == 1 && $goods->is_score != 1) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品不是积分商品不能用积分兑换。',
                ];
            }
            /** @var null|GoodsSku $sku */
            $sku = null;
            if (!empty($sku_key_name)) {
                $sku = GoodsSku::find()
                    ->andWhere(['gid' => $goods->id, 'key_name' => $sku_key_name])
                    ->one();
                if (empty($sku)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']不存在。',
                    ];
                }

                if ($sku->getStock() < $amount) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']库存不足。',
                    ];
                }
            }
            if (empty($sku) && !empty($goods->skuList)) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                ];
            }
            if ($goods->getAllStock() < $amount) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']库存不足。',
                ];
            }
            $shop = $goods->shop;
            //自购省钱
            $selfBuyRatio = $user->buyRatio;
            if ($is_use_score == 1) {
                $selfBuyRatio = 0;
                if ($user->account->score < $goods->score * $amount) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_ACCOUNT_SCORE,
                        'message' => '积分不足，请原价购买。',
                    ];
                }
            }
            //优惠券商品
            if ($goods->is_coupon == 1) {
                $selfBuyRatio = 0;
            }
            $item_list[] = [
                'goods' => [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'price' => $goods->price,
                    //'price' => ($is_use_score == 1) ? $goods->price -= round((System::getConfig('score_ratio') * $goods->score) / 100 , 2): $goods->price,
                    'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                    'self_amount_price' => round($goods->share_commission_value * $selfBuyRatio / 100 * $amount, 2),
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    'is_score' => $goods->is_score,
                    'score' => $goods->score,
                    'amount_score' => $goods->score * $amount,
                    'is_supplier' => ($goods->supplier_id && $goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                    'supplier_price' => $goods->supplier_price,
                ],
                'sku' => empty($sku) ? null : [
                    'id' => $sku->id,
                    'key' => $sku->key,
                    'key_name' => $sku->key_name,
                    'market_price' => $sku->market_price,
                    'price' => $sku->price,
                    //'price' => ($is_use_score == 1) ? $sku->price -= round((System::getConfig('score_ratio') * $goods->score) / 100 , 2) :$sku->price,
                    'stock' => $sku->stock,
                    'self_price' => $sku->commission == '' ? round($goods->share_commission_value * $selfBuyRatio / 100, 2) : round($sku->commission * $selfBuyRatio / 100, 2),
                    'self_amount_price' => $sku->commission == '' ? round($goods->share_commission_value * $selfBuyRatio / 100 * $amount, 2) : round($sku->commission * $selfBuyRatio / 100 * $amount, 2),
                    'is_score' => $goods->is_score,
                    'score' => $goods->score,
                    'amount_score' => $goods->score * $amount,
                    'supplier_price' => $sku->supplier_price == '' ? $goods->supplier_price : $sku->supplier_price
                ],
                'amount' => $amount,
            ];
            if (empty($sku) || $sku->commission == '') {
                $self_amount_price = round($goods->share_commission_value * $selfBuyRatio / 100 * $amount, 2);
            } else {
                $self_amount_price = round($sku->commission * $selfBuyRatio / 100 * $amount, 2);
            }
            if ($is_use_score == 1) {
                $score_amount_money = round(($goods->score * $amount) * System::getConfig('score_ratio') / 100, 2);
            }
            $goods_amount_money += empty($sku) ? round($goods->price * $amount, 2) :
                round($sku->price * $amount, 2);
            $total_score = $goods->score * $amount;
            if (Util::comp(round($goods_amount_money - $self_amount_price, 2), 0, 2) < 0) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '选择商品总价低于0不能下单，请重新选择。',
                ];
            }
        } elseif ($type == 'order') { // 订单再次购买
            $self_amount_price = 0;
            if (empty($order_no)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $order = Order::findByNo($order_no);
            if (empty($order) || $order->uid != $uid) {
                return [
                    'error_code' => ErrorCode::ORDER_NOT_FOUND,
                    'message' => '没有找到订单信息。',
                ];
            }
            //自购省钱
            $selfBuyRatio = $order->user->buyRatio;
            foreach ($order->itemList as $item) {
                $goods = $item->goods;
                $amount = $item->amount;
                if ($goods->status != Goods::STATUS_ON) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']已下架或删除。',
                    ];
                }
                //当日商品总量限购
                $checkTodayGoodsLimit = Goods::checkTodayGoodsLimit($gid, $amount);
                if (!$checkTodayGoodsLimit) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']每天限购500件，请明天再来。',
                    ];
                }
                //限购商品判断
                list($checkLimitGoods, $limit_amount) = Order::checkLimitGoods($uid, $gid, $amount);
                if (!$checkLimitGoods) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']每人每天限购2件，<br>请选择正确数量或者明天再来。',
                    ];
                }
                if ($limit_amount < 0) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']每人每天限购，<br>请选择正确数量或者明天再来。',
                    ];
                }
                //优惠券商品
                if ($goods->is_coupon == 1) {
                    $selfBuyRatio = 0;
                }
                /** @var null|GoodsSku $sku */
                $sku = null;
                if (!empty($item->sku_key_name)) {
                    $sku = GoodsSku::find()
                        ->andWhere(['gid' => $goods->id, 'key_name' => $item->sku_key_name])
                        ->one();
                    if (empty($sku)) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $item->sku_key_name . ']不存在。',
                        ];
                    }
                    if ($sku->getStock() < $amount) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']库存不足。',
                        ];
                    }
                }
                if (empty($sku) && !empty($goods->skuList)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                    ];
                }
                if ($goods->getAllStock() < $amount) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']库存不足。',
                    ];
                }
                $item_list[] = [
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'price' => $goods->price,
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                        'is_supplier' => ($goods->supplier_id && $goods->sale_type == Goods::TYPE_SUPPLIER) ? 1 : 0, // 是否一件代发货商品
                        'supplier_price' =>  $goods->supplier_price
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                        'self_price' => $sku->commission == '' ? round($goods->share_commission_value * $selfBuyRatio / 100, 2) : round($sku->commission * $selfBuyRatio / 100, 2),
                        'supplier_price' => $sku->supplier_price == '' ? $goods->supplier_price : $sku->supplier_price
                    ],
                    'amount' => $item->amount,
                ];
                if (empty($sku) || $sku->commission == '') {
                    $self_amount_price += round($goods->share_commission_value * $selfBuyRatio / 100 * $item->amount, 2);
                } else {
                    $self_amount_price += round($sku->commission * $selfBuyRatio / 100 * $item->amount, 2);
                }
                $goods_amount_money += empty($sku) ? round($goods->price * $amount, 2) :
                    round($sku->price * $amount, 2);
            }
            $shop = $order->shop;
        } else {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }
        if (empty($item_list)) {
            return [
                'error_code' => ErrorCode::ORDER_NO_GOODS,
                'message' => '没有任何有效商品，无法生成订单。',
            ];
        }
        $shop = [
            'id' => $shop->id,
            'name' => $shop->name,
            'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($shop->id, 'logo'),
        ];
        return [$shop, $item_list, $self_amount_price, $score_amount_money, $goods_amount_money, $total_score];
    }

    /**
     * 返回生成订单需要的商品列表
     * @param $uid integer 用户编号
     * @param $type string 来源类型 cart | goods | order
     * @param $sid integer 购物车店铺编号
     * @param $cart string 购物车，格式gid^^amount^^sku_key_name$$$
     * @param $gid integer 直接下单的商品编号
     * @param $sku_key_name string 直接下单的商品规格
     * @param $amount integer 直接下单的商品数量
     * @param $order_no string 再次购买的订单号
     * @return array array($shop, array('cart' => $cart, 'goods' => $goods, 'sku' => $sku, 'amount' => $amount))
     */
    private function makeOrderListBak($uid, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no)
    {
        $item_list = [];
        if ($type == 'cart') { // 从购物车过来
            if (empty($sid) || empty($cart)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $shop = Shop::findOne($sid);
            if (empty($shop)) {
                return [
                    'error_code' => ErrorCode::ORDER_SHOP_NOT_FOUND,
                    'message' => '没有找到店铺信息。',
                ];
            }
            foreach (preg_split('/\$\$\$/', $cart, -1, PREG_SPLIT_NO_EMPTY) as $item) {
                $item = explode('^^', $item);
                $gid = $item[0];
                $amount = $item[1];
                $sku_key_name = count($item) > 2 ? $item[2] : '';
                $cart = UserCart::findByUGS($uid, $gid, $sku_key_name);
                if (empty($cart)) {
                    $cart = new UserCart();
                    $cart->uid = $uid;
                    $cart->gid = $gid;
                    $cart->sku_key_name = $sku_key_name;
                    $cart->amount = $amount;
                    $cart->create_time = time();
                    $cart->save();
                } else {
                    $cart->amount = $amount;
                }
                $goods = $cart->goods;
                if ($sid != $goods->sid) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品不是同一家店铺不能下单。',
                    ];
                }
                if ($goods->status != Goods::STATUS_ON) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']已下架或删除。',
                    ];
                }
                /** @var null|GoodsSku $sku */
                $sku = null;
                if (!empty($cart->sku_key_name)) {
                    $sku = GoodsSku::find()
                        ->andWhere(['gid' => $goods->id, 'key_name' => $sku_key_name])
                        ->one();
                    if (empty($sku)) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']不存在。',
                        ];
                    }
                }
                if (empty($sku) && !empty($goods->skuList)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                    ];
                }
                $item_list[] = [
                    'cart_id' => $cart->id,
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'price' => $goods->price,
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                    ],
                    'amount' => $cart->amount,
                ];
            }
        } elseif ($type == 'goods') { // 直接下单
            if (empty($gid) || $amount <= 0) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $goods = Goods::findOne($gid);
            if (empty($goods)) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '没有找到商品信息。',
                ];
            }
            if ($goods->status != Goods::STATUS_ON) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                    'message' => '您选择的商品已下架或删除。',
                ];
            }
            /** @var null|GoodsSku $sku */
            $sku = null;
            if (!empty($sku_key_name)) {
                $sku = GoodsSku::find()
                    ->andWhere(['gid' => $goods->id, 'key_name' => $sku_key_name])
                    ->one();
                if (empty($sku)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']规格[' . $sku_key_name . ']不存在。',
                    ];
                }
            }
            if (empty($sku) && !empty($goods->skuList)) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                ];
            }
            $shop = $goods->shop;
            $item_list[] = [
                'goods' => [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'price' => $goods->price,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                ],
                'sku' => empty($sku) ? null : [
                    'id' => $sku->id,
                    'key' => $sku->key,
                    'key_name' => $sku->key_name,
                    'market_price' => $sku->market_price,
                    'price' => $sku->price,
                    'stock' => $sku->stock,
                ],
                'amount' => $amount,
            ];
        } elseif ($type == 'order') { // 订单再次购买
            if (empty($order_no)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '参数错误。',
                ];
            }
            $order = Order::findByNo($order_no);
            if (empty($order) || $order->uid != $uid) {
                return [
                    'error_code' => ErrorCode::ORDER_NOT_FOUND,
                    'message' => '没有找到订单信息。',
                ];
            }
            foreach ($order->itemList as $item) {
                $goods = $item->goods;
                if ($goods->status != Goods::STATUS_ON) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']已下架或删除。',
                    ];
                }
                /** @var null|GoodsSku $sku */
                $sku = null;
                if (!empty($item->sku_key_name)) {
                    $sku = GoodsSku::find()
                        ->andWhere(['gid' => $goods->id, 'key_name' => $item->sku_key_name])
                        ->one();
                    if (empty($sku)) {
                        return [
                            'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                            'message' => '您选择的商品[' . $goods->title . ']规格[' . $item->sku_key_name . ']不存在。',
                        ];
                    }
                }
                if (empty($sku) && !empty($goods->skuList)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                    ];
                }
                $item_list[] = [
                    'goods' => [
                        'id' => $goods->id,
                        'title' => $goods->title,
                        'price' => $goods->price,
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                    ],
                    'amount' => $item->amount,
                ];
            }
            $shop = $order->shop;
        } else {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '参数错误。',
            ];
        }
        if (empty($item_list)) {
            return [
                'error_code' => ErrorCode::ORDER_NO_GOODS,
                'message' => '没有任何有效商品，无法生成订单。',
            ];
        }
        $shop = [
            'id' => $shop->id,
            'name' => $shop->name,
            'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($shop->id, 'logo'),
        ];
        return [$shop, $item_list];
    }

    /**
     * 确认订单
     */
    public function actionConfirm()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $type = $this->get('type'); // 来源

        $sid = $this->get('sid'); // 购物车店铺编号
        $cart = $this->get('cart'); // 购物车

        $gid = $this->get('gid'); // 商品编号
        $sku_key_name = $this->get('sku_key_name'); // 商品规格
        $amount = $this->get('amount', 1); // 商品数量
        $is_use_score = $this->get('is_use_score', 0);


        if (empty($amount)) {
            $amount == 1;
        }
        if (empty($is_use_score)) {
            $is_use_score == 0;
        }

        $order_no = $this->get('order_no'); // 订单号

        Yii::warning($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no);
        //礼包卡券商品购买判断条件
        $pack_goods_list = Goods::find()->select('id')->where(['is_pack' => 1, 'is_pack_redeem' => 1])->all();
        if ($user->status == User::STATUS_OK && in_array($gid, array_column($pack_goods_list, 'id'))) {
            return [
                'error_code' => ErrorCode::ORDER_NO_GOODS,
                'message' => '会员已经激活不能重复购买。',
            ];
        }
        //优惠券活动商品相关
        $gift_info = [];//赠品信息
        $coupon_money = 0;
        $is_coupon = 0;
        if (!empty($gid) || !empty($order_no)) {

            if (!empty($order_no)) {//再次购买
                $order = Order::findByNo($order_no);
                $is_coupon = $order->is_coupon;
                foreach ($order->itemList as $item) {
                    $gid = $item->gid;//优惠券活动订单商品仅一个
                }
            }
            if (!empty($gid)) {
                /** @var $coupon_goods Goods */
                $coupon_goods = Goods::find()->where(['id' => $gid])->one();
                $is_coupon = $coupon_goods->is_coupon;
            }
            if ($is_coupon == 1) {

                // 赠送赠品订单限制
                $count_gift = Order::find()
                    ->where(['and', ['status' => Order::STATUS_CREATED], ['is_coupon' => 1], ['uid' => $user->id]])
                    ->andWhere(['<>', 'gift_id', ''])
                    ->count();
                if ($count_gift > 0) {
                    return [
                        'error_code' => ErrorCode::COUPON_ORDER_FAIL,
                        'message' => '您还有未支付活动订单,请前往订单列表查看。',
                    ];
                }

                if ($user->status == User::STATUS_WAIT) {
                    return [
                        'error_code' => ErrorCode::COUPON_ORDER_FAIL,
                        'message' => '普通用户无法参与此活动。',
                    ];
                }
                $coupon_lock = GoodsCouponGiftUser::find()
                    ->andWhere(['and', ['uid' => $user->id], ['gid' => $gid], ['status' => GoodsCouponGiftUser::STATUS_LOCK]])
                    ->one();
                if (!empty($coupon_lock)) {
                    return [
                        'error_code' => ErrorCode::COUPON_ORDER_FAIL,
                        'message' => '您还有未支付活动订单,请前往订单列表查看。',
                    ];
                }
                /** @var $coupon GoodsCouponGiftUser */
                $coupon = GoodsCouponGiftUser::find()
                    ->andWhere(['and', ['uid' => $user->id], ['gid' => $gid], ['status' => GoodsCouponGiftUser::STATUS_WAIT]])
                    ->one();
                if (!empty($coupon)) {
                    $coupon_money = $coupon->rule->price;
                }
                $gift_id = $this->get('gift_id'); // 活动赠品编号
                if (!empty($gift_id)) {
                    $gift = GoodsCouponGift::findOne($gift_id);
                }
            }
        }

        $result = $this->makeOrderList($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no, $is_use_score);
        if (!empty($result['message'])) {
            return $result;
        } else {
            list($shop, $item_list, $self_amount_price, $score_amount_money, $goods_amount_money, $total_score) = $result;
        }
        //限时抢购
        $res = Marketing::calcDiscount($item_list, $user->id, false);
        if (!empty($res['message'])) {
            return $res;
        } else {
            if ($res['discountMoney']) {
                $goods_amount_money -= $res['discountMoney'];
            }
        }
        foreach ($item_list as $k => $item) {
            if (empty($item['sku'])) {
                $item_list[$k]['sku'] = new stdClass();
            }
            if ($item['goods']['price'] <= 0) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '商品' . $item['goods']['title'] . '价格低于0不能下单，请重新选择。',
                ];
            }
        }
//        array_walk($item_list, function (&$item) {
//            if (empty($item['sku'])) {
//                $item['sku'] = new stdClass();
//            }
//        });
        if (!empty($gift)) {
            $gift_info = [
                'id' => $gift->id,
                'name' => $gift->name,
                'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $gift->thumb_pic,
                'price' => $gift->price,
                'title' => System::getConfig('ground_push_active_name'),
            ];
        }

        return [
            'shop' => $shop,
            'item_list' => $item_list,
            'self_amount_money' => $self_amount_price,
            'score_amount_money' => $score_amount_money,
            'goods_amount_money' => $goods_amount_money,
            'total_score' => $total_score,
            'coupon_money' => $coupon_money,
            'gift_info' => empty($gift_info) ? new stdClass() : $gift_info,
        ];
    }

    /**
     * 保存订单
     */
    public function actionSave()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $type = $this->get('type'); // 来源

        $sid = $this->get('sid'); // 购物车店铺编号
        $cart = $this->get('cart'); // 购物车

        $gid = $this->get('gid'); // 商品编号
        $sku_key_name = $this->get('sku_key_name'); // 商品SKU编号
        $amount = $this->get('amount', 1); // 商品数量

        $order_no = $this->get('order_no'); // 订单号

        $remark = $this->get('remark'); // 买家留言
        $address_id = $this->get('address_id');
        $is_use_score = $this->get('is_use_score', 0);
        $gift_id = $this->get('gift_id');

        if (Yii::$app->cache->exists('save_order_no_' . $order_no)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('save_order_no_' . $order_no, $order_no, 2);
        }

        $result = $this->makeOrderList($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no, $is_use_score);
        if (!empty($result['message'])) {
            return $result;
        } else {
            list($shop, $item_list, $self_amount_price, $score_amount_money, $goods_amount_money, $total_score) = $result;
        }
        //限时抢购
        $res = Marketing::calcDiscount($item_list, $user->id, false);
        if (!empty($res['message'])) {
            return $res;
        }
        /** @var UserAddress $address */
        $address = UserAddress::find()->andWhere(['uid' => $user->id, 'id' => $address_id])->one();
        if (empty($address)) {
            return [
                'error_code' => ErrorCode::ORDER_NO_ADDRESS,
                'message' => '没有选择收货地址。',
            ];
        }

        $trans = Yii::$app->db->beginTransaction();
        try {
            $order = new Order();
            $order->uid = $user->id;
            $order->deliver_info = json_encode([
                'area' => $address->area,
                'address' => $address->address,
                'name' => $address->name,
                'mobile' => $address->mobile,
            ]);
            $order->create_time = time();
            $order->status = Order::STATUS_CREATED;
            $order->sid = $shop['id'];
            $order->amount_money = 0; // 需要生成订单详情后再更新订单
            $order->user_remark = $remark; // 订单附言
            if (!$order->save()) {
                throw new Exception('无法保存订单信息。');
            }
            $fee_goods_list = [];
            $share_commission_value = 0;
            foreach ($item_list as $item) {
                /** @var Goods $goods */
                $goods = $item['goods'];
                /** @var GoodsSku $sku */
                $sku = $item['sku'];
                $amount = $item['amount'];
                if ($amount <= 0) {
                    throw new Exception('商品数量参数错误。');
                }
                $order_item = new OrderItem();
                $order_item->oid = $order->id;
                $order_item->gid = $goods['id'];
                $order_item->title = $goods['title'];
                if (isset($item['discountMoney']) && isset($item['did'])) {
                    $order_item->mark = OrderItem::DISCOUNT;
                    $order_item->mark_money = $item['discountMoney'];
                }
                $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
                $order_item->amount = $amount;
                $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
                $order_item->self_money = !empty($sku) ? $sku['self_price'] : $goods['self_price'];
                $supplier_price = $goods['supplier_price'];
                if (!empty($sku) && !empty($sku['supplier_price'])) {
                    $supplier_price = $sku['supplier_price'];
                }
                $order_item->supplier_price = $supplier_price;
                if (!$order_item->save()) {
                    throw new Exception('无法保存订单详情信息。');
                }
                if (isset($item['discountMoney']) && isset($item['did'])) {
                    $order->discount_money += $item['discountMoney'];
                    $order->discount_ids = $item['did'];
                }
                $order->goods_money += $order_item->price * $order_item->amount;
                $order->amount_money += $order_item->price * $order_item->amount;
                //是否积分兑换
                if ($is_use_score == 1) {
                    $order->score += $order_item->goods->score * $order_item->amount;
                }

                if ($user->status == User::STATUS_OK) {
                    //规格产品佣金计算
                    $sku = $order_item->goodsSku;
                    if (empty($sku) || $sku->commission == '') {
                        $share_commission_value += $order_item->goods->share_commission_value * $order_item->amount;
                    } else {
                        $share_commission_value += $sku->commission * $order_item->amount;
                    }
                    //是否优惠券活动商品
                    if ($order_item->goods->is_coupon == 1) {
                        Marketing::calcCoupon($share_commission_value, $order, $order_item, $user, $gift_id);
                    }
                }
                //if ($user->status == User::STATUS_OK && $order_item->goods->is_pack != 1) {
//                if ($user->status == User::STATUS_OK) {
//                    $share_commission_value += $order_item->goods->share_commission_value * $order_item->amount;
//                    //是否优惠券活动商品
//                    if ($order_item->goods->is_coupon == 1) {
//                        $share_commission_value = 0;
//                        $coupon_rule = GoodsCouponRule::find()->where(['gid' => $order_item->goods->id])->one();
//                        //首次购买以及使用最后一张优惠券 送赠品
//                        $coupon_count= GoodsCouponGiftUser::find()
//                            ->where(['status' => GoodsCouponGiftUser::STATUS_WAIT])
//                            ->andWhere(['uid'=>$user->id])
//                            ->andWhere(['gid'=>$order_item->goods->id])
//                            ->count();
//                        /** @var $coupon_rule GoodsCouponRule */
//                        if (!empty($gift_id) && $coupon_rule->status == GoodsCouponRule::STATUS_OK && $coupon_count <=1) {
//                            $order->gift_id = $gift_id;
//                        }
//                        $order->is_coupon = 1;
//                        /** @var $coupon GoodsCouponGiftUser */
//                        $coupon = GoodsCouponGiftUser::find()
//                            ->andWhere(['and', ['uid' => $user->id], ['gid' => $order_item->goods->id], ['status' => GoodsCouponGiftUser::STATUS_WAIT]])
//                            ->one();
//                        if (!empty($coupon)) {
//                            $order->coupon_money = $coupon->rule->price;
//                            $order->amount_money -= $coupon->rule->price;
//                            $order->coupon_id = $coupon->id;
//                            $coupon->status = GoodsCouponGiftUser::STATUS_LOCK;
//                            $r = $coupon->save(false);
//                            if (!$r) {
//                                throw new Exception('优惠券记录更新失败。');
//                            }
//                        }
//
//                    }
//                }

                $fee_goods_list[] = ['gid' => $order_item->gid, 'amount' => $order_item->amount];
                /** @var UserCart $cart */
                if (!empty($item['cart_id'])) {
                    $cart = UserCart::findOne($item['cart_id']);
                    if (!empty($cart)) {
                        try {
                            $cart->delete();
                        } catch (\Throwable $e) {
                        }
                    }
                }
            }
            // 计算运费价格
            $deliver_to_city = City::findByCode($address->area);
            if (empty($deliver_to_city)) {
                return [
                    'error_code' => ErrorCode::SERVER,
                    'message' => '没有找到编号为[' . $address->area . ']的城市信息。',
                ];
            }
            $code_list = $deliver_to_city->address(true);
            /** @var $fee_goods_list [] */
            $fee = GoodsExpress::multiGoodsExpress($fee_goods_list, $code_list[0], count($code_list) > 1 ? $code_list[1] : '');
            if (!empty($fee['message'])) {
                throw new Exception('不能保存订单 ' . $fee['message']);
            }
            if (!empty($fee['fee']) && $fee['fee'] != 0) {
                $order->deliver_fee = $fee['fee'];
                $order->amount_money += $order->deliver_fee;
            }

            $user_commission = $user->buyRatio;
            if ($user_commission != 0 && $is_use_score != 1) {
                $order->amount_money -= round($share_commission_value * $user_commission / 100, 2);
                $order->self_buy_money = round($share_commission_value * $user_commission / 100, 2);
            }

            //积分兑换  自购省清空
            if ($is_use_score == 1) {
                $order->is_score = 1;
                $order->amount_money -= round($order->score * System::getConfig('score_ratio') / 100, 2);
                $order->score_money = round($order->score * System::getConfig('score_ratio') / 100, 2);

                //扣掉积分
                $r = UserAccount::updateAllCounters(['score' => -1 * $order->score], ['uid' => $user->id]);
                if ($r <= 0) {
                    throw new Exception('无法更新用户账户：' . $user->id . ' 积分 ' . $order->score);
                }
                $ual = new UserAccountLog();
                $ual->uid = $user->id;
                $ual->oid = $order->id;
                $ual->score = -1 * $order->score;
                $ual->time = time();
                /** @var $item OrderItem */
                foreach ($order->itemList as $item) {
                    $ual->remark = '购买' . $item->title . '商品积分抵扣';
                }
                $r = $ual->save();
                if (!$r) {
                    throw new Exception('账号记录更新失败。');
                }
            }

            if (!$order->save()) {
                Yii::warning($order->getErrors());
                throw new Exception('金额小于0， 无法更新订单金额。');
            }
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '创建订单。', print_r($this->get(), true));

            $trans->commit();
            return [
                'order' => ['no' => $order->no],
            ];
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '生成订单错误：' . $e->getMessage(),
            ];
        }
    }

    /**
     * 准备支付
     * GET
     * order_no
     */
    public function actionPreparePay()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $pay_method = $this->get('pay_method');
        if (Yii::$app->cache->exists('pay_order_no_' . $order_no)
            && Yii::$app->cache->exists('pay_method_no_' . $pay_method)
        ) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('pay_order_no_' . $order_no, $order_no, 2);
            Yii::$app->cache->set('pay_method_no_' . $pay_method, $pay_method, 2);
        }
        $payment_password = $this->get('payment_password');
        $client_type = $this->get('client_type', 'web');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }

        if ($order->status != Order::STATUS_CREATED) {
            return [
                'error_code' => ErrorCode::ORDER_STATUS,
                'message' => '订单状态错误。',
            ];
        }
        if ($order->is_pack == 1 && $order->pack_coupon_status == 1 && $user->status == User::STATUS_OK) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '仅限未激活会员购买。',
            ];
        }
        //限时抢购
        foreach ($order->itemList as $item) {
            $goods = $item->goods;
            $amount = $item->amount;
            /** @var null|GoodsSku $sku */
            $sku = null;
            if (!empty($item->sku_key_name)) {
                $sku = GoodsSku::find()
                    ->andWhere(['gid' => $goods->id, 'key_name' => $item->sku_key_name])
                    ->one();
                if (empty($sku)) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']规格[' . $item->sku_key_name . ']不存在。',
                    ];
                }
                if ($sku->getStock() < $amount) {
                    return [
                        'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                        'message' => '您选择的商品[' . $goods->title . ']规格[' . $item->sku_key_name . ']库存不足。',
                    ];
                }
            }
            if (empty($sku) && !empty($goods->skuList)) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']没有选择规格。',
                ];
            }
            if ($goods->getAllStock() < $amount) {
                return [
                    'error_code' => ErrorCode::ORDER_GOODS_SKU_NOT_FOUND,
                    'message' => '您选择的商品[' . $goods->title . ']库存不足。',
                ];
            }

            $item_list[] = [
                'goods' => [
                    'id' => $item->goods->id,
                ],
                'amount' => $item->amount,
            ];
        }
        $res = Marketing::calcDiscount($item_list, $user->id, true);
        if (!empty($res['message'])) {
            return $res;
        }

//        if (empty($payment_password)) {
//            return [
//                'error_code' => ErrorCode::USER_PAYMENT_PASSWORD,
//                'message' => '必须输入支付密码。',
//            ];
//        }
//        if (empty($user->payment_password)) {
//            return [
//                'error_code' => ErrorCode::USER_PASSWORD_EMPTY,
//                'message' => '用户没有设置支付密码。',
//            ];
//        }
//        try {
//            if (strpos($payment_password, '$base64aes$') === 0) {
//                $payment_password = SystemVersion::aesDecode($this->client_api_version, substr($payment_password, 11));
//            }
//        } catch (Exception $e) {
//            return [
//                'error_code' => ErrorCode::SERVER,
//                'message' => $e->getMessage(),
//            ];
//        }
//        if (!$user->validatePaymentPassword($payment_password)) {
//            return [
//                'error_code' => ErrorCode::USER_PAYMENT_PASSWORD,
//                'message' => '支付密码错误。',
//            ];
//        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            if (empty($order->fid)) {
                $finance_log = new FinanceLog();
                $finance_log->type = FinanceLog::TYPE_ORDER_PAY;
                $finance_log->create_time = time();
            } else {
                $finance_log = FinanceLog::findOne($order->fid);
            }
            if (!empty($finance_log->id) && (Util::comp($order->amount_money, $finance_log->money, 2) != 0)) {
                $finance_log->trade_no = null;
            }
            if ($finance_log->pay_method != $pay_method) {
                $finance_log->trade_no = null;
            }
            $finance_log->money = $order->amount_money;
            $finance_log->pay_method = $pay_method;
            $finance_log->status = FinanceLog::STATUS_WAIT;

            if (!$finance_log->save()) {
                Yii::error(json_encode($finance_log->errors));
                throw new Exception('无法保存财务记录。', ErrorCode::SERVER);
            }
            $order->fid = $finance_log->id;
            if (!$order->save(false)) {
                Yii::error(json_encode($order->errors));
                throw new Exception('无法更新订单财务关联。', ErrorCode::SERVER);
            }
            $result = [];
            switch ($pay_method) {
                case FinanceLog::PAY_METHOD_YHK: // 银行卡
                    if (System::getConfig('pingan_open') != 1) {
                        throw new Exception('系统没有开通银行卡支付。', ErrorCode::SERVER);
                    }
                    $pingan_api = new PinganApi();
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_YHK;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = $pingan_api->generateOrderNo(rand(10000000, 99999999));
                    }
                    $finance_log->save();
                    $result['trade_no'] = $finance_log->trade_no;
                    $result['money'] = $finance_log->money;
                    break;
                case FinanceLog::PAY_METHOD_WX_SCAN: // 微信扫码
                    if (System::getConfig('weixin_scan_pay_open') != 1) {
                        throw new Exception('系统没有开通微信扫码支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_WX_SCAN;
                    $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $finance_log->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id, $code_url) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '订单', $finance_log->trade_no, $finance_log->money);
                    $result['weixin'] = [
                        'prepay_id' => $prepay_id,
                        'code_url' => $code_url,
                    ];
                    break;
                case FinanceLog::PAY_METHOD_WX_APP: // 微信APP
                    if (System::getConfig('weixin_app_pay_open') != 1) {
                        throw new Exception('系统没有开通微信APP支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_WX_APP;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . Util::randomStr(4, 2) . $user->id;
                    }
                    $finance_log->save();
                    $weixin_api = new WeixinAppApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $finance_log->trade_no, $finance_log->money, 'APP');
                    $result['weixin'] = [
                        'appid' => System::getConfig('weixin_app_app_id'),
                        'partnerid' => System::getConfig('weixin_app_mch_id'),
                        'prepayid' => $prepay_id,
                        'package' => 'Sign=WXPay',
                        'noncestr' => Util::randomStr(32, 7),
                        'timestamp' => time(),
                    ];
                    $result['weixin']['sign'] = $weixin_api->makeSign($result['weixin']);
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    if (System::getConfig('weixin_mp_pay_open') != 1) {
                        throw new Exception('系统没有开通微信公众号支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_WX_MP;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . Util::randomStr(4, 2) . $user->id;
                    }
                    $finance_log->save();

                    if ($order->no != 'C000000') {
                        $weixin_api = new WeixinMpApi();
                        list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $finance_log->trade_no, $finance_log->money, 'JSAPI', $this->get('openid'));
                    } else {
                        $weixin_api = new YWeixinMpApi();
                        list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-ymall-订单', $finance_log->trade_no, $finance_log->money, 'JSAPI', $this->get('openid'), '333');
                    }
                    //list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $finance_log->trade_no, $finance_log->money, 'JSAPI', $this->get('openid'));
                    $result['weixin'] = [
                        'timeStamp' => time(),
                        'nonceStr' => Util::randomStr(32, 7),
                        'package' => 'prepay_id=' . $prepay_id,
                        'signType' => 'MD5',
                    ];
                    $result['weixin']['paySign'] = $weixin_api->makeSign($result['weixin'], true);
                    break;
                case FinanceLog::PAY_METHOD_WX_H5: // 微信H5支付
                    if (System::getConfig('weixin_h5_pay_open') != 1) {
                        throw new Exception('系统没有开通微信H5支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_WX_H5;
                    $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    $finance_log->save();
                    $weixin_api = new WeixinH5Api();
                    if (strpos(Yii::$app->request->userAgent, 'Android') > -1) {
                        $scene_info = [
                            'h5_info' => [
                                'type' => '订单AndroidH5支付',
                                'app_name' => '惠民商城',
                                'package_name' => 'com.liuniukeji.yunyue', // TODO:安卓PackageName
                            ],
                        ];
                    } elseif (strpos(Yii::$app->request->userAgent, 'AppleWebKit') > -1) {
                        $scene_info = [
                            'h5_info' => [
                                'type' => '订单IOSH5支付',
                                'app_name' => '惠民商城',
                                'bundle_id' => 'com.liuniukeji.yunyueOS', // TODO:苹果BundleId
                            ],
                        ];
                    } else {
                        $scene_info = [
                            'h5_info' => [
                                'type' => '订单H5支付',
                                'wap_url' => Url::to(['/h5/order/view', 'order_no' => $order->no], true),
                                'wap_name' => System::getConfig('site_name'),
                            ],
                        ];
                    }
                    $redirect_url = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $finance_log->trade_no, $finance_log->money, 'MWEB', $scene_info);
                    $redirect_url .= '&redirect_url=' . urlencode(Url::to(['/h5/order/pay', 'order_no' => $order->no], true));
                    $result['redirect_url'] = $redirect_url;
                    break;
                case FinanceLog::PAY_METHOD_ZFB: // 支付宝
                    if (System::getConfig('alipay_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_ZFB;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $finance_log->save();
                    $alipay_api = new AlipayApi();
                    $form = $alipay_api->AlipayTradeWapPay(System::getConfig('site_name') . '订单', $finance_log->trade_no, $finance_log->money, Url::to(['/h5/order/view', 'order_no' => $order->no], true));
                    $result['form'] = $form;
                    break;
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝App
                    if (System::getConfig('alipay_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_ZFB_APP;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . Util::randomStr(4, 2) . $user->id;
                    }
                    $finance_log->save();
                    $alipay_api = new AlipayApi();
                    $alipay = $alipay_api->AlipayTradeAppPay(System::getConfig('site_name') . '订单', System::getConfig('site_name') . '订单', $finance_log->trade_no, $finance_log->money);
                    $result['alipay'] = $alipay;
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY: // 通联支付
                    if (System::getConfig('allinpay_open') != 1) {
                        throw new Exception('系统没有开通通联支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_ALLINPAY;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $finance_log->save();
                    $allinpay_api = new AllInPayApi();
                    list($url, $data) = $allinpay_api->submit($finance_log->trade_no, $finance_log->money, $finance_log->create_time, System::getConfig('site_name') . '订单', $client_type);
                    $form = '<form id="allinpay_form" method="post" action="' . $url . '">';
                    foreach ($data as $k => $v) {
                        $form .= '<input type="hidden" name="' . $k . '" value ="' . $v . '" />';
                    }
                    $form .= '</form>';
                    $form .= '<script>document.getElementById("allinpay_form").submit();</script>';
                    $result['form'] = $form;
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY_H5: // 通联H5支付
                    if (System::getConfig('allinpay_h5_open') != 1) {
                        throw new Exception('系统没有开通此支付方式。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_ALLINPAY_H5;
                    $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id; // 每次提交支付生成一个新的交易号
                    $finance_log->save();
                    try {
                        $api = new AllInPayH5Api();
                        $userId = UserConfig::getConfig($user->id, 'allinpay_h5_user_id');
                        if (empty($userId)) {
                            $userId = $api->userReg('user_' . $user->id);
                            UserConfig::setConfig($user->id, 'allinpay_h5_user_id', $userId);
                        }
                        $redirect = Url::to(['/api/all-in-pay-h5/return'], true);
                        list($url, $data) = $api->getSubmitForm($finance_log->trade_no, $finance_log->money, $finance_log->create_time, $userId, $redirect);
                        $form = '<form id="allinpay_form" method="post" action="' . $url . '">';
                        foreach ($data as $k => $v) {
                            $form .= '<input type="hidden" name="' . $k . '" value ="' . $v . '" />';
                        }
                        $form .= '</form>';
                        $form .= '<script>document.getElementById("allinpay_form").submit();</script>';
                        $result['form'] = $form;
                    } catch (Exception $e) {
                        throw new Exception('通联支付接口调用失败：' . $e->getMessage(), ErrorCode::SERVER);
                    }
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY_ALI: // 通联支付宝支付
                    if (System::getConfig('allinpay_ali_open') != 1) {
                        throw new Exception('系统没有开通支付宝支付。', ErrorCode::SERVER);
                    }
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_ALLINPAY_ALI;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $r = $finance_log->save();
                    if (!$r) {
                        throw new Exception('没有生成财务记录。', ErrorCode::SERVER);
                    }
                    $allinpay_ali_api = new AllInPayAliApi();
                    try {
                        $json = $allinpay_ali_api->unitOrder($finance_log->trade_no, $finance_log->money);
                        if ($json['retcode'] != 'SUCCESS') {
                            throw new Exception($json['retmsg'], ErrorCode::SERVER);
                        }
                        if ($json['trxstatus'] != '0000') {
                            throw new Exception($json['errmsg'], ErrorCode::SERVER);
                        }
                        $result['payinfo'] = $json['payinfo'];
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage(), ErrorCode::SERVER);
                    }
                    break;
                case FinanceLog::PAY_METHOD_YE: // 佣金
                    $finance_log->pay_method = FinanceLog::PAY_METHOD_YE;
                    if (empty($finance_log->trade_no)) {
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $finance_log->save();
                    if (!UserAccount::find()
                        ->andWhere(['uid' => $user->id])
                        ->andWhere(['>', 'commission', $finance_log->money])
                        ->exists()) {
                        throw new Exception('余额不足。', ErrorCode::PARAM);
                    }
                    // 佣金扣款
                    if (Util::comp($finance_log->money, 0, 2) != 0) {
                        $r = UserAccount::updateAllCounters(['commission' => -1 * $finance_log->money], ['uid' => $user->id]);
                        if ($r <= 0) {
                            throw new Exception('无法更新账户。', ErrorCode::SERVER);
                        }
                    }
                    $ual = new UserAccountLog();
                    $ual->uid = $user->id;
                    $ual->commission = -1 * $finance_log->money;
                    $ual->time = time();
                    $ual->remark = '支付订单';
                    if (!$ual->save()) {
                        throw new Exception('无法保存账户记录。', ErrorCode::SERVER);
                    }
                    FinanceLog::payNotify($finance_log->trade_no, $finance_log->money, FinanceLog::STATUS_SUCCESS, null);
                    $result['pay_success'] = true;
                    $result['pay_money'] = $finance_log->money;
                    break;
                default:
                    throw new Exception('无法确定支付方式。', ErrorCode::SERVER);
            }
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '生成支付信息。', print_r($finance_log->attributes, true));
            $result['order_no'] = $order->no;
            $trans->commit();
            return $result;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 订单支付信息
     * GET
     * order_no
     */
    public function actionFinance()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        if (empty($order->fid)) {
            return [
                'error_code' => ErrorCode::NO_RESULT,
                'message' => '没有找到支付信息。',
            ];
        }
        $list = [];
        $pack_type = 0;//判断是否是礼包卡券商品 0否 1是
        //如果是购买大礼包支付成功  激活会员 以及  以后相对应条件
        foreach ($order->itemList as $item) {
//            if ($item->gid == 2 && $order->financeLog->status == FinanceLog::STATUS_SUCCESS) {
//                Yii::warning($item->gid, $order->user->id, $order->user->status);
//                if ($order->user->status == 2) {
//                    $user = User::findOne($order->user->id);
//                    $user->status = 1;
//                    $user->save(false);
//                }
//            }
            if ($item->goods->is_pack == 1 && $item->goods->is_pack_redeem == 1) {
                $list['goods'] = [
                    'title' => $item->title,
                    'price' => $item->price,
                    'desc' => $item->goods->desc,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item->goods->main_pic,

                ];
                $pack_type = 1;
            }
            if ($order->is_coupon == 1) {
                $list['goods'] = [
                    'title' => $item->title,
                    'price' => $item->price,
                    'desc' => $item->goods->desc,
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item->goods->main_pic,

                ];
                if (!empty($order->gift_id)) {
                    $gift = GoodsCouponGift::findOne($order->gift_id);
                    $list['gift'] = [
                        'title' => System::getConfig('ground_push_active_name'),
                        'name' => $gift->name,
                        'price' => $gift->price,
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $gift->thumb_pic,
                    ];
                }
                $count = GoodsCouponGiftUser::find()
                    ->where(['and', ['gid' => $item->gid], ['uid' => $user->id], ['status' => GoodsCouponGiftUser::STATUS_WAIT]])
                    ->count();
                if ($count == 3) {
                    /** @var $coupon GoodsCouponGiftUser */
                    $coupon = GoodsCouponGiftUser::find()
                        ->where(['and', ['gid' => $item->gid], ['uid' => $user->id], ['status' => GoodsCouponGiftUser::STATUS_WAIT]])
                        ->one();
                    $list['coupon'] = [
                        'name' => $coupon->rule->name,
                        'price' => intval($coupon->rule->price),
                        'time' => $coupon->create_time,
                        'num' => $coupon->rule->count,

                    ];

                }
            }

        }

        return [
            'list' => empty($list) ? new stdClass() : $list,
            'trade_no' => $order->financeLog->trade_no,
            'money' => $order->financeLog->money,
            'pay_method' => $order->financeLog->pay_method,
            'status' => $order->financeLog->status,
            'type' => $pack_type,
        ];
    }

    /**
     * 查看物流信息接口
     * get
     * {
     *      order_no 物流编号
     * }
     */
    public function actionDeliverInfo()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        /** @var OrderDeliver[] $deliver_list */
        $deliver_list = OrderDeliver::find()->joinWith(['express'])->where(['oid' => $order->id])->all();
        $list = [];
        foreach ($deliver_list as $deliver) {
            $order_item = [];
            foreach ($deliver->itemList as $item) {
                $order_item[] =
                    [
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $item->orderItem->goods->main_pic,
                        'title' => $item->orderItem->title,
                        'price' => $item->orderItem->price,
                        'amount' => $item->orderItem->getDeliverListAmount($deliver->id)
                    ];
            }
            $list[] = [
                'item_list' => $order_item,
                'express_info' => [
                    'express_name' => $deliver->express->name,
                    'express_no' => $deliver->no,
                ],
                'trace' => empty($deliver->trace) ? [] : json_decode($deliver->trace),
            ];
        }
        return [
            'deliver_list' => $list,
        ];
    }

    /**
     * 保存订单用户留言
     * GET
     * remark 备注
     * order_no 订单号
     */
    public function actionSaveRemark()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $remark = $this->get('remark');
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息.',
            ];
        }
        if (!in_array($order->status, [Order::STATUS_CREATED, Order::STATUS_PAID])) {
            return [
                'error_code' => ErrorCode::ORDER_REMARK_DENIED,
                'message' => '订单状态不允许再附加留言信息。',
            ];
        }
        $order->user_remark = $remark;
        $r = $order->save();
        if (!$r) {
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '无法保存留言。',
                'errors' => $order->errors,
            ];
        }
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '设置用户留言。', $remark);
        return [];
    }

    /**
     * 更新订单地址
     * GET
     * order_no 订单号
     * address_id 地址编号
     */
    public function actionUpdateOrderAddress()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }

        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        $address_id = $this->get('address_id');
        /** @var UserAddress $address */
        $address = UserAddress::find()->where(['uid' => $user->id, 'id' => $address_id])->one();
        if (empty($address)) {
            return [
                'error_code' => ErrorCode::USER_ADDRESS_NOT_FOUND,
                'message' => '收货地址不存在。',
            ];
        }
        $order->deliver_info = json_encode([
            'area' => $address->area,
            'address' => $address->address,
            'name' => $address->name,
            'mobile' => $address->mobile,
        ]);
        // 计算运费价格
        $user_city = City::findByCode($address->area);
        $p_area = substr($user_city->code, 0, 2) . '0000';
        $c_area = substr($user_city->code, 0, 4) . '00';
        /** @var $fee_goods_list [] */
        $fee_goods_list = [];
        foreach ($order->itemList as $item) {
            $fee_goods_list[] = ['gid' => $item->gid, 'amount' => $item->amount];
        }
        $fee = GoodsExpress::multiGoodsExpress($fee_goods_list, $p_area, $c_area);
        if (!empty($fee['message'])) {
            return [
                'error_code' => ErrorCode::ORDER_SAVE_ADDRESS_FAIL,
                'message' => '更新地址失败' . $fee['message'],
            ];
        }
        if ($fee['fee'] >= 0) {
            $old_deliver_fee = $order->deliver_fee;
            $order->deliver_fee = $fee['fee'];
            $order->amount_money = $order->amount_money - $old_deliver_fee + $order->deliver_fee;
        }
        $r = $order->save();
        if (!$r) {
            return [
                'error_code' => ErrorCode::ORDER_SAVE_FAIL,
                'message' => '订单更新失败。',
                'errors' => $order->errors,
            ];
        }
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '修改收货地址。', print_r($order->attributes, true));
        return [
            'deliver_fee' => $order->deliver_fee,
            'amount_money' => $order->amount_money,
        ];
    }

    /**
     * 提交商品评论
     * post {
     *          order : {
     *              order_no : 订单编号
     *          }，
     *          shop : {
     *              sid : 店铺编号
     *              score : 评分
     *          },
     *          goods_comment :
     *              {
     *              "12": 订单商品编号  order_item:id
     *               {
     *                  gid 商品编号
     *                  score 评分
     *                  content 评论内容
     *                  img_list 评论图片
     *                  is_anonymous 是否匿名
     *              },
     *              {
     *              "13": 订单商品编号  order_item:id
     *               {
     *                  gid 商品编号
     *                  score 评分
     *                  content 评论内容
     *                  img_list 评论图片
     *                  is_anonymous 是否匿名
     *              }
     *
     *      }
     * {
     *   "order":{"order_no":"0B09000001750959"},
     *   "shop":{"sid":"1","score":"5"},
     *   "goods_comment":{
     *      "a33":{"gid":"5","content":"good","score":"4","img_list":""},
     *      "b34":{"gid":"6","content":"good2","score":"5","img_list":""}
     *   }
     * }
     */
    public function actionGoodsComment()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['order/order_no'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $order_no = $json['order']['order_no'];
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        if (!in_array($order->status, [Order::STATUS_RECEIVED, Order::STATUS_COMPLETE])) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '订单状态错误，禁止评论。',
            ];
        }
        $shop = $json['shop'];
        if (empty($json['shop']) || empty($json['shop']['score'])) {
            return [
                'error_code' => ErrorCode::COMMENT_SHOP_EMPTY_SCORE,
                'message' => '没有找到店铺评分。',
            ];
        }
        $goods_comment_data = $json['goods_comment'];
        $trans = Yii::$app->db->beginTransaction();
        try {
            if ($order->status == Order::STATUS_RECEIVED) {
                $shop_score = new ShopScore();
                $shop_score->score = $shop['score'];
                $shop_score->sid = $order->sid;
                $shop_score->uid = $order->uid;
                $shop_score->oid = $order->id;
                $shop_score->create_time = time();
                if (!$shop_score->save()) {
                    throw new Exception('无法保存店铺评分。');
                }
            }
            if (empty($order->itemList)) {
                throw new Exception('没有找到订单商品。');
            }
            foreach ($order->itemList as $orderItem) {
                $goods_comment = new GoodsComment();
                $post_data = $goods_comment_data;
                if (empty($post_data) || !is_array($post_data) || !isset($post_data[$orderItem->id])) {
                    throw new Exception('参数错误。');
                }
                $goods_comment->setAttributes($post_data[$orderItem->id]);
                if (empty($goods_comment->img_list)) {
                    unset($goods_comment->img_list);
                } else {
                    $goods_comment->img_list = json_encode($goods_comment->img_list);
                }
                if ($order->status == Order::STATUS_COMPLETE) {
                    /** @var GoodsComment $p_comment */
                    $p_comment = GoodsComment::find()->andWhere(['gid' => $orderItem->gid, 'uid' => $order->uid, 'oid' => $order->id, 'sku_key_name' => $orderItem->sku_key_name])->one();
                    if (!empty($p_comment)) {
                        $goods_comment->pid = $p_comment->id;
                    }
                }
                $goods_comment->gid = $orderItem->gid;
                $goods_comment->uid = $order->uid;
                $goods_comment->oid = $order->id;
                $goods_comment->sku_key_name = $orderItem->sku_key_name;
                $goods_comment->status = System::getConfig('comment_need_verify', 0) == 1 ? GoodsComment::STATUS_SHOW : GoodsComment::STATUS_HIDE;
                $goods_comment->create_time = time();
                if (!$goods_comment->save()) {
                    $errors = $goods_comment->errors;
                    $error = array_shift($errors)[0];
                    throw new Exception('无法保存对商品[' . $orderItem->title . ']的评价：' . $error . '。');
                }
            }
            if ($order->status == Order::STATUS_RECEIVED) {
                $order->status = Order::STATUS_COMPLETE;
                if (!$order->save()) {
                    throw new Exception('无法保存订单状态。');
                }
            }
            OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '发表评价。');
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => $e->getMessage(),
            ];
        }
        return [];
    }

    /**
     * 订单商品列表
     * get
     * {
     *      order_no 订单编号
     * }
     */
    public function actionOrderItemList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $order_no = $this->get('order_no');
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        $item_list = [];
        foreach ($order->itemList as $orderItem) {
            $order_refund = OrderRefund::find()->where(['oiid' => $orderItem->id])->one();
            if (empty($order_refund)) {
                $is_refund = 0;
            } else {
                $is_refund = 1;
            }
            $orderItemOld = $orderItem;
            $sku = $orderItem->goodsSku;
            $main_pic = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $orderItem->goods->main_pic;
            $orderItem = $orderItem->toArray();
            $orderItem['is_refund'] = $is_refund;
            $orderItem['main_pic'] = $main_pic;

            if ($this->client_api_version < '1.0.8') {
                if ($orderItemOld->goods->is_score == 1 && $orderItemOld->order->is_score == 1) {
                    $orderItem['price'] = round($orderItemOld->price - round($orderItemOld->goods->score * $orderItemOld->amount * System::getConfig('score_ratio') / 100, 2), 2);
                } else {
                    if (empty($sku) || $sku->commission == '') {
                        $orderItem['price'] = round($orderItemOld->price - round($orderItemOld->goods->share_commission_value * $user->buyRatio / 100, 2), 2);
                    } else {
                        $orderItem['price'] = round($orderItemOld->price - round($sku->commission * $user->buyRatio / 100, 2), 2);
                    }
                }
            } else {
                $orderItem['refund_price'] = $orderItemOld->getRefundMoney();
                $orderItem['price'] = $orderItemOld->price;
            }
            $item_list[] = $orderItem;
        }

        $shop = $order->shop->toArray();
        $shop['logo'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($shop['id'], 'logo');
        return [
            'item_list' => $item_list,
            'shop' => $shop,
        ];
    }

    /**
     * 申请售后服务
     * post
     * {
     *      oiid 订单商品编号
     *      amount 退款退货数量
     *      money 退款金额
     *      type 退款/退款退货
     *      reason 售后理由
     *      image_list 售后上传图片
     * }
     */
    public function actionRequireAfterSaleService()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['oiid', 'money'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }

        $oiid = $json['oiid'];
        $order_item = OrderItem::findOne($oiid);
        if (empty($order_item) || $order_item->order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        $refund_money = $json['money'];
        if (Util::comp($refund_money, 0, 2) <= 0) {
            return [
                'error_code' => ErrorCode::ORDER_STATUS_EXCEPTION,
                'message' => '订单售后金额不能低于0。',
            ];
        }
        if ($order_item->order->status != Order::STATUS_RECEIVED) {
            return [
                'error_code' => ErrorCode::ORDER_STATUS_EXCEPTION,
                'message' => '订单状态异常，无法申请售后，请联系客服解决。',
            ];
        }
        if (Yii::$app->cache->exists('check_AfterSale_' . $oiid)) {
            return [
                'error_code' => ErrorCode::REQUEST_TO_MANY,
                'message' => '请求太频繁了，请稍后重试。',
            ];
        } else {
            Yii::$app->cache->set('check_AfterSale_' . $oiid, $oiid, 2);
        }
        $supplier_id = !empty($order_item->goods->supplier_id) ? $order_item->goods->supplier_id : null;
        $order_refund = new OrderRefund();
        $order_refund->create_time = time();
        $order_refund->oiid = $oiid;
        $order_refund->oid = $order_item->order->id;
        $order_refund->supplier_id = $supplier_id;
        $order_refund->status = OrderRefund::STATUS_REQUIRE;
        $count = OrderRefund::find()->where(['oiid' => $oiid])->count();
        if (!empty($count)) {
            return [
                'error_code' => ErrorCode::ORDER_STATUS_EXCEPTION,
                'message' => '该商品已经申请售后，请勿重复申请。',
            ];
        }
        $order_refund->setAttributes($json);
        if (empty($order_refund->image_list)) {
            unset($order_refund->image_list);
        } else {
            $order_refund->image_list = json_encode($order_refund->image_list);
        }

        if ($this->client_api_version >= '1.0.8')  //写入自购省金额item表
        {
            $refund_money_max = $order_item->getRefundMoney();
        } else {
            $refund_money_max = $order_item->order->is_score > 0 ? $order_item->price * $order_item->amount - $order_item->order->score_money : ($order_item->price * $order_item->amount) - (!empty($order_item->goodsSku) && $order_item->goodsSku->commission != '' ? sprintf("%.2f",$order_item->goodsSku->commission * $user->buyRatio / 100) : sprintf("%.2f",$order_item->goods->share_commission_value * $user->buyRatio / 100)) * $order_item->amount;
        }

        if (Util::comp($refund_money, $refund_money_max, 2) > 0) {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_MONEY_EXCEPTION,
                'message' => '您输入的退款金额超出最大可退款金额。',
            ];
        }
        if ($order_refund->save()) {
            $order_item->order->status = Order::STATUS_AFTER_SALE;
            $order_item->order->save(false);
            OrderLog::info($order_item->order->uid, OrderLog::U_TYPE_USER, $order_item->order->id, '申请售后。', print_r($order_refund->attributes, true));
        } else {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_SAVE_FAIL,
                'message' => '无法保存申请退货信息。',
                'errors' => $order_refund->errors,
            ];
        }
        return [];
    }

    /**
     * 填写退货信息获取商家信息
     * get
     * {
     *      sid 商家编号
     * }
     */
    public function actionRefundRelated()
    {
        $sid = $this->get('sid');
        if (empty($sid)) {
            return [
                'error_code' => ErrorCode::SHOP_NOT_FOUND,
                'message' => '无法保存申请退货信息。',
            ];
        }
        $info = [];
        $info['refund_deliver_user'] = ShopConfig::getConfig($sid, 'refund_deliver_user');
        $info['refund_deliver_address'] = ShopConfig::getConfig($sid, 'refund_deliver_address');
        $info['refund_deliver_mobile'] = ShopConfig::getConfig($sid, 'refund_deliver_mobile');
        return ['info' => $info];
    }

    /**
     * 获取商品的供货商信息
     */
    public function actionRefundShopInfo()
    {
        $sid = $this->get('sid');
        $oiid = $this->get('oiid');
        if (empty($sid)) {
            return [
                'error_code' => ErrorCode::SHOP_NOT_FOUND,
                'message' => '店铺编号必填。',
            ];
        }
        $info = [];
        $orderItem = OrderItem::findOne($oiid);
        if (empty($orderItem->goods)) {
            return [
                'error_code' => ErrorCode::SHOP_NOT_FOUND,
                'message' => '商品不存在。',
            ];
        }
        if ($orderItem->goods->sale_type == Goods::TYPE_SUPPLIER && !empty($orderItem->goods->supplier_id)) {
            $info['refund_deliver_user'] = SupplierConfig::getConfig($orderItem->goods->supplier_id, 'refund_deliver_user');
            $info['refund_deliver_address'] = SupplierConfig::getConfig($orderItem->goods->supplier_id, 'refund_deliver_address');
            $info['refund_deliver_mobile'] = SupplierConfig::getConfig($orderItem->goods->supplier_id, 'refund_deliver_mobile');
        } else {
            $info['refund_deliver_user'] = ShopConfig::getConfig($sid, 'refund_deliver_user');
            $info['refund_deliver_address'] = ShopConfig::getConfig($sid, 'refund_deliver_address');
            $info['refund_deliver_mobile'] = ShopConfig::getConfig($sid, 'refund_deliver_mobile');
        }
        return ['info' => $info];
    }

    /**
     * 退货填写物流单号
     * post
     * {
     *      id 申请售后的编号
     *      express_name 物流公司名称
     *      express_no 物流单号
     *      contact_mobile 联系电话
     * }
     */
    public function actionRefundExpress()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $json = $this->checkJson([
            [['id', 'express_name', 'express_no', 'contact_mobile'], 'required', 'message' => '缺少必要参数。'],
        ]);
        if (isset($json['error_code'])) {
            return $json;
        }
        $id = $json['id'];
        $model = OrderRefund::findOne($id);
        if (empty($model) || $model->orderItem->order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_NOT_FOUND,
                'message' => '没有找到退货信息。',
            ];
        }
        $model->send_time = time();
        $model->status = OrderRefund::STATUS_SEND;
        $model->setAttributes($json);
        if ($model->save()) {
            OrderLog::info($model->orderItem->order->uid, OrderLog::U_TYPE_USER, $model->orderItem->order->id, '填写提货物流信息。', print_r($model->attributes, true));
        } else {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_SAVE_FAIL,
                'message' => '无法保存退货物流信息。',
                'errors' => $model->errors,
            ];
        }
        return [];
    }

    /**
     * 申请售后列表
     */
    public function actionRefundList()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $query = Order::find();
        $item = $query->joinWith('itemList')
            ->where(['uid' => $user->id])
            ->andWhere(['<>', 'status', Order::STATUS_DELETE])
            ->select('{{%order_item}}.id')
            ->all();
        $oiid = ArrayHelper::getColumn($item, 'id');
        $query = OrderRefund::find();
        $query->select(["id", "oiid", "amount", "money", "type", "reason", "image_list", "status", "express_name",
            "express_no", "contact_mobile", "create_time"]);
        $query->where(['in', 'oiid', $oiid]);
        $query->andWhere(['<>', 'status', OrderRefund::STATUS_DELETE]);
        /** @var OrderRefund[] $list */
        $list = $query->orderBy('create_time DESC')->all();
        $refund_list = [];
        foreach ($list as $key => $val) {
            if (!empty($val['image_list'])) {
                $val['image_list'] = json_decode($val['image_list'], true);
                if (is_array($val['image_list'])) {
                    $val['image_list'] = array_map(function ($v) {
                        return Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $v;
                    }, $val['image_list']);
                }
            } else {
                $val['image_list'] = [];
            }
            $list[$key]['image_list'] = $val['image_list'];
            $val_tmp = $val->toArray();
            $val_tmp['title'] = $val->orderItem->title;
            $val_tmp['sku_key_name'] = $val->orderItem->sku_key_name;
            $val_tmp['main_pic'] = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $val->orderItem->goods->main_pic;
            $refund_list[] = [
                'shop' => [
                    'id' => $val->orderItem->order->shop->id,
                    'name' => $val->orderItem->order->shop->name,
                    'logo' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($val->orderItem->order->shop->id, 'logo'),
                ],
                'item_list' => $val_tmp,
                'id' => $val['id'],
                'type' => $val['type'],
                'type_str' => KeyMap::getValue('order_refund_type', $val['type']),
                'status' => $val['status'],
                'status_str' => KeyMap::getValue('order_refund_status', $val['status']),
            ];
        }
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        return [
            'refund_list' => $refund_list,
            'page' => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->pageCount,
                'page' => $pagination->page + 1,
            ]
        ];
    }

    /**
     * 退款进度
     * get
     * {
     *      id 申请售后编号
     * }
     */
    public function actionRefundView()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $model = OrderRefund::findOne($id);
        if (empty($model) || $model->orderItem->order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_NOT_FOUND,
                'message' => '申请售后不存在。',
            ];
        }
        $model = $model->toArray();
        $model['type_str'] = KeyMap::getValue('order_refund_type', $model['type']);
        $model['status_str'] = KeyMap::getValue('order_refund_status', $model['status']);
        return [
            'refund' => $model,
        ];
    }

    /**
     * 退款信息
     */
    public function actionDeleteRefund()
    {
        $user = $this->loginUser();
        if (is_array($user) && isset($user['error_code'])) {
            return $user;
        }
        $id = $this->get('id');
        $model = OrderRefund::findOne($id);
        if (empty($model) || $model->orderItem->order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_NOT_FOUND,
                'message' => '申请售后不存在。',
            ];
        }
        $model->status = OrderRefund::STATUS_DELETE;
        if (!$model->save()) {
            return [
                'error_code' => ErrorCode::ORDER_REFUND_DELETE_FAIL,
                'message' => '无法删除退款申请。',
            ];
        }
        return [];
    }


}
