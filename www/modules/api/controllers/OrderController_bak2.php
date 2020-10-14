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
use app\models\GoodsExpress;
use app\models\GoodsSku;
use app\models\KeyMap;
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
class OrderController_bak2 extends BaseController
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
        $query->andFilterWhere(['status' => $this->get('search_status')]);
        $pagination = new Pagination(['totalCount' => $query->count(), 'validatePage' => false]);
        $query->orderBy('create_time DESC');
        $query->offset($pagination->offset);
        $query->limit($pagination->limit);
        $order_list = [];
        $is_pack = 0;
        foreach ($query->each() as $order) {/** @var Order $order */
            $item_list = [];
            foreach ($order->itemList as $orderItem) {
                $item_list[] = [
                    'id' => $orderItem->id,
                    'goods' => [
                        'id' => $orderItem->goods->id,
                        'title' => $orderItem->goods->title,
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $orderItem->goods->main_pic,
                    ],
                    'amount' => $orderItem->amount,
                    'price' => $orderItem->price,
                    'sku_key_name' => $orderItem->sku_key_name,
                ];
                if ($orderItem->goods->is_pack == 1) {
                    $is_pack = 1;
                }
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
                ],
            ];
            if ($orderItem->goods->is_pack == 1) {
                $is_pack = 1;
            }
        }
        $address = $order->getDeliverInfoJson();
        $city = City::findByCode($address['area']);
        if (empty($city)) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => '没有找到编号为[' . $address['area'] . ']的城市信息。',
            ];
        }
        $address['city'] = $city->address();
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
                'address' => $address,
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
        $order = Order::findByNo($order_no);
        if (empty($order) || $order->status == Order::STATUS_DELETE || $order->uid != $user->id) {
            return [
                'error_code' => ErrorCode::ORDER_NOT_FOUND,
                'message' => '没有找到订单信息。',
            ];
        }
        //if ($order->status != Order::STATUS_CANCEL && $order->status != Order::STATUS_CREATED) {
        if (!in_array($order->status , [Order::STATUS_CANCEL, Order::STATUS_CREATED, Order::STATUS_COMPLETE])) {
            return [
                'error_code' => ErrorCode::ORDER_DELETE_DENIED,
                'message' => '订单状态不允许删除。',
            ];
        }
        $order->status = Order::STATUS_DELETE;
        $order->delete_time = time();
        $order->save(false);
        OrderLog::info($order->uid, OrderLog::U_TYPE_USER, $order->id, '删除订单。', json_encode($order->attributes));
        return [];
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
            if ($item->goods->is_pack == 1) {
                return [
                    'error_code' => ErrorCode::ORDER_NOT_FOUND,
                    'message' => '大礼包订单不能取消。',
                ];
            }
        }
        if ($order->status != Order::STATUS_PAID) {
            return [
                'error_code' => ErrorCode::ORDER_CANCEL_DENIED,
                'message' => '订单状态不允许取消。',
            ];
        }
        $order->status = Order::STATUS_CANCEL_WAIT_MERCHANT;
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
     * @return array array($shop, array('cart' => $cart, 'goods' => $goods, 'sku' => $sku, 'amount' => $amount))
     */
    private function makeOrderList($uid, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no)
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
            $user = User::findOne($uid);
            //自购省钱
            $selfBuyRatio = $user->buyRatio;
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
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                    ],
                    'amount' => $cart->amount,
                ];
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
            //自购省钱
            $selfBuyRatio = $user->buyRatio;
            $item_list[] = [
                'goods' => [
                    'id' => $goods->id,
                    'title' => $goods->title,
                    'price' => $goods->price,
                    'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                    'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                ],
                'sku' => empty($sku) ? null : [
                    'id' => $sku->id,
                    'key' => $sku->key,
                    'key_name' => $sku->key_name,
                    'market_price' => $sku->market_price,
                    'price' => $sku->price,
                    'stock' => $sku->stock,
                    'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
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
            //自购省钱
            $selfBuyRatio = $order->user->buyRatio;
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
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic,
                    ],
                    'sku' => empty($sku) ? null : [
                        'id' => $sku->id,
                        'key' => $sku->key,
                        'key_name' => $sku->key_name,
                        'market_price' => $sku->market_price,
                        'price' => $sku->price,
                        'stock' => $sku->stock,
                        'self_price' => round($goods->share_commission_value * $selfBuyRatio / 100, 2),
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
        if (empty($amount)) {$amount ==1;}

        $order_no = $this->get('order_no'); // 订单号
        Yii::warning($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no);
        if ($user->status == User::STATUS_OK && $gid == 2) {
            return [
                'error_code' => ErrorCode::ORDER_NO_GOODS,
                'message' => '会员已经激活不能重复购买。',
            ];
        }

        $result = $this->makeOrderList($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no);
        if (!empty($result['message'])) {
            return $result;
        } else {
            list($shop, $item_list) = $result;
        }
        array_walk($item_list, function (&$item) {
            if (empty($item['sku'])) {
                $item['sku'] = new stdClass();
            }
        });
        return [
            'shop' => $shop,
            'item_list' => $item_list,
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

        $result = $this->makeOrderList($user->id, $type, $sid, $cart, $gid, $sku_key_name, $amount, $order_no);
        if (!empty($result['message'])) {
            return $result;
        } else {
            list($shop, $item_list) = $result;
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
                $order_item = new OrderItem();
                $order_item->oid = $order->id;
                $order_item->gid = $goods['id'];
                $order_item->title = $goods['title'];
                $order_item->sku_key_name = !empty($sku) ? $sku['key_name'] : null;
                $order_item->amount = $amount;
                $order_item->price = !empty($sku) ? $sku['price'] : $goods['price'];
                if (!$order_item->save()) {
                    throw new Exception('无法保存订单详情信息。');
                }
                $order->goods_money += $order_item->price * $order_item->amount;
                $order->amount_money += $order_item->price * $order_item->amount;

                if ($user->status == User::STATUS_OK && $order_item->goods->is_pack != 1) {
                    $share_commission_value += $order_item->goods->share_commission_value * $order_item->amount;
                }

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
            if ($user_commission != 0) {
                $order->amount_money -= round($share_commission_value * $user_commission / 100, 2);
                $order->self_buy_money = round($share_commission_value * $user_commission / 100, 2);
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
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
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
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
                    }
                    $finance_log->save();
                    $weixin_api = new WeixinMpApi();
                    list($prepay_id) = $weixin_api->unifiedOrder(System::getConfig('site_name') . '-订单', $finance_log->trade_no, $finance_log->money, 'JSAPI', $this->get('openid'));
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
                        $finance_log->trade_no = 'Y' . date('YmdHis') . $user->id;
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
                    $r =$finance_log->save();
                    if(!$r){
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
        //如果是购买大礼包支付成功  激活会员 以及  以后相对应条件
        foreach ($order->itemList as $item) {
            if ($item->gid == 2 && $order->financeLog->status == FinanceLog::STATUS_SUCCESS) {
                Yii::warning($item->gid,$order->user->id,$order->user->status);
                if ($order->user->status == 2) {
                    $user = User::findOne($order->user->id);
                    $user->status = 1;
                    $user->save(false);
                }
            }
        }
        return [
            'trade_no' => $order->financeLog->trade_no,
            'money' => $order->financeLog->money,
            'pay_method' => $order->financeLog->pay_method,
            'status' => $order->financeLog->status,
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
                        'main_pic' => Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] .$item->orderItem->goods->main_pic,
                        'title' => $item->orderItem->goods->title,
                        'price' => $item->orderItem->goods->price,
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
            'deliver_list' =>$list,
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
        $p_area = substr($user_city->code, 0, 2).'0000';
        $c_area = substr($user_city->code, 0, 4).'00';
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
            foreach ($order->itemList as  $orderItem) {
                $goods_comment = new GoodsComment();
                $post_data = $goods_comment_data;
                if (empty($post_data) || !is_array($post_data) || !isset($post_data[$orderItem->id])) {
                    throw new Exception('参数错误。');
                }
                $goods_comment->setAttributes($post_data[$orderItem->id]);
                if (empty($goods_comment->img_list)){
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
        foreach ($order->itemList as  $orderItem) {
            $order_refund = OrderRefund::find()->where(['oiid' => $orderItem->id])->one();
            if (empty($order_refund)) {
                $is_refund = 0;
            } else {
                $is_refund = 1;
            }
            $orderItemOld = $orderItem;
            $main_pic = Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $orderItem->goods->main_pic;
            $orderItem = $orderItem->toArray();
            $orderItem['is_refund'] = $is_refund;
            $orderItem['main_pic'] = $main_pic;
            $orderItem['price'] = round($orderItemOld->price - round($orderItemOld->goods->share_commission_value * $user->buyRatio /100, 2), 2);
            $item_list[] = $orderItem;
        }

        $shop = $order->shop->toArray();
        $shop['logo'] =  Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . ShopConfig::getConfig($shop['id'],'logo');
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
            [['oiid'], 'required', 'message' => '缺少必要参数。'],
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
        if ($order_item->order->status != Order::STATUS_RECEIVED) {
            return [
                'error_code' => ErrorCode::ORDER_STATUS_EXCEPTION,
                'message' => '订单状态异常，无法申请售后，请联系客服解决。',
            ];
        }
        $order_refund = new OrderRefund();
        $order_refund->create_time = time();
        $order_refund->oiid = $oiid;
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
        if (Util::comp($order_refund->money, $order_refund->getRefundMoney(), 2) > 0) {
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
        $query->select(["id", "oiid", "amount","money", "type", "reason", "image_list", "status", "express_name",
            "express_no", "contact_mobile", "create_time"]);
        $query->where(['in', 'oiid' , $oiid]);
        $query->andWhere(['<>', 'status', OrderRefund::STATUS_DELETE]);
        /** @var OrderRefund[] $list */
        $list = $query->orderBy('create_time DESC')->all();
        $refund_list = [];
        foreach ($list as $key => $val) {
            if (!empty($val['image_list'])) {
                $val['image_list'] = json_decode($val['image_list'], true);
                if (is_array($val['image_list'])) {
                    $val['image_list'] = array_map(function($v){
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
