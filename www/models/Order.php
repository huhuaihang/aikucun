<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * 订单
 * Class Order
 * @package app\models
 *
 * @property integer $id PK
 * @property string $no 订单号
 * @property integer $uid 用户编号
 * @property integer $sid 店铺编号
 * @property integer $fid 财务记录编号
 * @property integer $cancel_fid 取消订单退款财务记录编号
 * @property string $deliver_info 收货信息JSON
 * @property float $deliver_fee 物流费用
 * @property float $goods_money 商品金额
 * @property float $amount_money 订单总金额
 * @property float $self_buy_money 订单总自购剩下的金额
 * @property string $user_remark 用户备注
 * @property string $merchant_remark 商户备注
 * @property string $supplier_remark 供应商备注
 * @property integer $status 状态
 * @property integer $score 消耗积分
 * @property integer $is_pack 是否礼包订单
 * @property integer $pack_coupon_status 卡券礼包订单0否 1卡券获得 2卡券使用
 * @property integer $is_score 是否积分兑换订单
 * @property integer $is_coupon 是否优惠券活动订单
 * @property integer $gift_id   活动赠品id
 * @property integer $coupon_id   优惠券id
 * @property integer $discount_ids   限时抢购活动id
 * @property float $score_money 积分抵扣金额
 * @property float $coupon_money 优惠券抵扣金额
 * @property float $discount_money 限时抢购优惠金额
 * @property integer $create_time 创建时间
 * @property integer $receive_time 确认收货时间
 * @property integer $delete_time 删除时间
 *
 * @property User $user 关联用户
 * @property Shop $shop 关联店铺
 * @property GoodsCouponGift $gift 关联赠品
 * @property OrderItem[] $itemList 关联订单内容列表
 * @property FinanceLog $financeLog 关联财务记录
 * @property FinanceLog $cancelFinanceLog 关联取消订单财务记录
 * @property MerchantFinancialSettlement $merchantFinancialSettlement 关联结算记录
 */
class Order extends ActiveRecord
{
    const STATUS_CREATED = 1;    // 已创建 待支付
    const STATUS_PAID = 2;       // 已支付 待配货
    const STATUS_PACKING = 3;    // 配货中
    const STATUS_PACKED = 4;     // 已配货 待发货
    const STATUS_DELIVERED = 5;  // 已发货 待收货
    const STATUS_RECEIVED = 6;   // 已收货 待评论
    const STATUS_COMPLETE = 7;   // 已评论 订单完成
    const STATUS_AFTER_SALE = 8; // 售后处理中
    const STATUS_CANCEL_WAIT_MERCHANT = 91; // 取消待商户审核
    const STATUS_CANCEL_WAIT_MANAGER = 92;  // 取消待商户审核
    const STATUS_CANCEL = 9;     // 已取消
    const STATUS_DELETE = 0;     // 已删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['no', 'user_remark'], 'string', 'max' => 128],
            [['uid', 'sid', 'status', 'create_time'], 'required'],
            [['uid', 'sid', 'status', 'create_time','gift_id','coupon_id','pack_coupon_status'], 'integer'],
            ['deliver_info', 'string', 'max' => 1024],
            [['merchant_remark','supplier_remark'], 'string', 'max' => 512],
            [['deliver_fee', 'goods_money', 'amount_money'], 'default', 'value' => 0],
            [['deliver_fee', 'goods_money', 'amount_money'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '用户编号',
            'money' => '金额',
            'status' => '状态',
            'create_time' => '创建时间',
            'deliver_fee' => '配送费',
            'goods_money' => '商品总价',
            'amount_money' => '订单总价',
        ];
    }

    /**
     * 关联店铺
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'sid']);
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
     * 关联订单内容列表
     * @return \yii\db\ActiveQuery
     */
    public function getItemList()
    {
        return $this->hasMany(OrderItem::className(), ['oid' => 'id']);
    }

    /**
     * 关联财务记录
     * @return \yii\db\ActiveQuery
     */
    public function getFinanceLog()
    {
        return $this->hasOne(FinanceLog::className(), ['id' => 'fid']);
    }

    /**
     * 关联取消订单财务记录
     * @return \yii\db\ActiveQuery
     */
    public function getCancelFinanceLog()
    {
        return $this->hasOne(FinanceLog::className(), ['id' => 'cancel_fid']);
    }

    /**
     * 关联结算记录
     * @return \yii\db\ActiveQuery
     */
    public function getMerchantFinancialSettlement()
    {
        return $this->hasOne(MerchantFinancialSettlement::className(), ['oid' => 'id']);
    }

    /**
     * 关联活动赠品
     * @return \yii\db\ActiveQuery
     */
    public function getGift()
    {
        return $this->hasOne(GoodsCouponGift::className(), ['id' => 'gift_id']);
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->no = Order::generateNo($this->uid);
        }
        return parent::beforeSave($insert);
    }

    /**
     * 生成订单号
     * @param $uid integer 买家用户编号
     * @return string
     */
    public static function generateNo($uid)
    {
        $y = strtoupper(dechex(date('Y') - 2017)); // 1位16进制
        $m = strtoupper(dechex(date('m'))); // 1位16进制
        $d = date('d'); // 2位数字
        $uid = str_pad($uid, 6, '0', STR_PAD_LEFT); // 6位用户编号
        $r = Util::randomStr(6); // 6位随机数字
        return $y . $m . $d . $uid . $r;
    }

    /**
     * 根据订单号返回订单信息
     * @param $order_no string 订单号
     * @return Order
     */
    public static function findByNo($order_no)
    {
        /** @var Order $order */
        $order = Order::find()->where(['no' => $order_no])->one();
        return $order;
    }

    /**
     * 订单支付结果
     * @param boolean $is_success 是否成功
     * @return boolean
     * @throws Exception
     */
    public function payNotify($is_success)
    {
        if (!$is_success) {
            if ($this->status == Order::STATUS_AFTER_SALE || $this->status == Order::STATUS_COMPLETE) {
                //$this->status = Order::STATUS_CANCEL;
                $this->status = Order::STATUS_COMPLETE;
            } else {
                $this->status = Order::STATUS_CREATED;
            }

        } else {
            if ($this->status == Order::STATUS_CREATED
                || ($this->status == Order::STATUS_CANCEL && empty($this->cancel_fid))
            ) {
                $is_pack_redeem=0;//判断是否卡券礼包商品
                //如果是购买大礼包支付成功  激活会员 以及  以后相对应条件
                foreach ($this->itemList as $item) {
                    if (($item->gid == 2 || $this->is_pack==1 || $item->goods->is_pack == 1) && $this->financeLog->status == FinanceLog::STATUS_SUCCESS) {
                        Yii::warning($item->gid);
                        Yii::warning($item->order->user->id);
                        Yii::warning($item->order->user->status);
                        if ($item->order->user->status == 2) {
                            $user = User::findOne($this->user->id);
                            $user->status = 1;
                            $user->handle_time = time();
                            $user->is_self_active = 1;
                            $user->save(false);
                            //发放补贴
                            //$user->subsidy($user);
                            $user->all_no_next_sub($user);
                            $user->updateScore(); // 激活给400积分
//                            if ($this->user->parent) {
//                                UserAccount::updateAllCounters(['level_money' => 399], ['uid' => $this->user->parent->id]);
//                                $user_a_log = new UserAccountLog();
//                                $user_a_log->uid = $this->user->id;
//                                $user_a_log->level_money = 399;
//                                $user_a_log->time = time();
//                                $user_a_log->remark = '下级购买礼包 等级金额增加 uid' . $this->user->id . ' order_no:' . $this->no;
//                                $user_a_log->save();
//                            }
                            $user->activateMessage(); //激活发送相关通知
                            //礼包卡券相关记录
                            if($item->goods->is_pack_redeem == 1 || $this->pack_coupon_status == 1)
                            {
                                $user_package = new UserPackageCoupon();
                                $user_package->uid = $item->order->user->id;
                                $user_package->oid = $item->order->id;
                                $user_package->create_time = time();
                                $user_package->over_time = time() + 86400 * System::getConfig('pack_redeem_over_day');
                                $user_package->status = UserPackageCoupon::STATUS_OK;
                                if($user_package->save(false))
                                {
                                    $is_pack_redeem = 1;
                                }
                                $user->is_package_coupon_active = 1;
                                $user->save(false);
                            }

                        }
                    }
                    if ($item->goods->is_coupon == 1 && $this->financeLog->status == FinanceLog::STATUS_SUCCESS) {
                        if ($item->order->user->status == 1) {
                            $user = User::findOne($this->user->id);
                            $user->updateCoupons($item->goods->id, $item->order->coupon_id);//更新优惠券记录
                        }

                    }


                }
                if ($is_pack_redeem == 1) {
                    $this->status = Order::STATUS_COMPLETE;
                } else {
                    $this->status = Order::STATUS_PAID;
                }
            } else {
                Yii::warning($this->no . ' 订单支付结果更新时订单状态为：' . $this->status);
            }
        }
        $r = $this->save(false);
        if (!$r) {
            throw new Exception('无法保存订单状态。');
        }
        //更新商品库存
        $this->updateStock();
        // 支付成功  如果有 供应商一件代发商品自动生成 发货单 如果全部是供货商商品直接全部生成发货单
//        $deliverItemList = true;
//        foreach ($this->itemList as $item) {
//            if ($item->goods->sale_type != Goods::TYPE_SUPPLIER) {
//                $deliverItemList = false;
//            }
//        }
//        if ($deliverItemList) {
//            $this->generateDeliver();
//        }
        if ($is_pack_redeem != 1) {
            $this->generateDeliver();
        }

        return true;
    }

    /**
     * 减库存
     */
    public function updateStock()
    {
        /** @var OrderItem $orderItem */
        foreach ($this->itemList as $orderItem) {
            // 直接更新库存
            $sku = $orderItem->goodsSku;
            if (!empty($sku)) {
                GoodsSku::updateAllCounters(
                    ['stock' => -1 * $orderItem->amount],
                    ['gid' => $orderItem->gid, 'id' => $sku->id]);
            }

            $goods = Goods::findOne($orderItem->gid);
            if ($goods->stock > $orderItem->amount) {
                Goods::updateAllCounters(['stock' => (-1 * $orderItem->amount)], ['id' => $orderItem->gid]);
            } else {
                $goods->stock = 0;
                $goods->status = Goods::STATUS_OFF; // 下架商品
                $goods->save(false);
            }

            //更新每天限购商品数量
            Goods::setTodayGoodsLimit($orderItem->gid, $orderItem->amount);
        }
    }

    /**
     * 判断限购商品购买数量
     * @param $uid integer 用户编号
     * @param $gid int 商品编号
     * @param $amount int 数量
     * @return  array
     */
    public static function checkLimitGoods($uid, $gid, $amount)
    {
        $bool = true;
        $goods = Goods::findOne($gid);
        $stock = $goods->getAllStock();
        if ($goods->is_limit != 1) {
            return [$bool, $stock];
        }
        if ($goods->limit_type == 1) {
            $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        } elseif ($goods->limit_type == 2) {
            $beginToday = $goods->limit_start_time;
            $endToday = $goods->limit_end_time;
        } else {
            $bool = true;
            return [$bool, $stock];
        }
//        $goods_arr = [6, 7, 10, 31, 32, 33, 35, 38];
////        if ($gid != 6) {
//        if (!in_array($gid, $goods_arr)) {
//            return true;
//        }

        $query = OrderItem::find();
        $sell = intval($query
            ->alias('order_item')
            ->joinWith('order order')
            ->andWhere(['>=', 'order.status', Order::STATUS_CREATED])
            ->andWhere(['<>', 'order.status', Order::STATUS_CANCEL])
            ->andWhere(['order_item.gid' => $gid])
            ->andWhere(['order.uid' => $uid])
            ->andWhere(['BETWEEN', 'order.create_time', $beginToday, $endToday])
            ->sum('amount'));
        Yii::warning($query->createCommand()->getRawSql());
//        Yii::warning( Yii::$app->cache->set('today_goods_' . $beginToday. '_'. $gid, 3));
        Yii::warning( Yii::$app->cache->get('today_goods_' . $beginToday. '_'. $gid));
        $stock = $goods->limit_amount - $sell;
        if (($sell + $amount) > $goods->limit_amount) {
            $bool = false;
            return [$bool, $stock];
        }
        return [$bool, $stock];
    }

    /**
     * 限购商品购买数量 还剩多少可以买
     * @param $uid integer 用户编号
     * @param $gid int 商品编号
     * @return  int
     */
    public static function UserLimitGoods($uid, $gid)
    {
        $goods = Goods::findOne($gid);
        if ($goods->limit_type == 1) {
            $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        } elseif ($goods->limit_type == 2) {
            $beginToday = $goods->limit_start_time;
            $endToday = $goods->limit_end_time;
        }  else {
            return $goods->stock;
        }


        $query = OrderItem::find();
        $sell = intval($query
            ->alias('order_item')
            ->joinWith('order order')
            ->andWhere(['>=', 'order.status', Order::STATUS_CREATED])
            ->andWhere(['<>', 'order.status', Order::STATUS_CANCEL])
            ->andWhere(['order_item.gid' => $gid])
            ->andWhere(['order.uid' => $uid])
            ->andWhere(['BETWEEN', 'order.create_time', $beginToday, $endToday])
            ->sum('amount'));
        $left_amount = $goods->limit_amount - $sell;

        return $left_amount;
    }

    /**
     * 返回收货信息JSON
     * @param $k string 收货地址详细信息
     * @return array|string
     */
    public function getDeliverInfoJson($k = null)
    {
        $json = json_decode($this->deliver_info, true);
        if (empty($k)) {
            return $json;
        }
        return $json[$k];
    }

    /**
     * 取消订单
     * @return boolean
     * @throws Exception
     */
    public function doCancel()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_ORDER_CANCEL;
            $financeLog->money = -1 * $this->financeLog->money;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            $financeLog->pay_method = $this->financeLog->pay_method;
            switch ($this->financeLog->pay_method) {
                case FinanceLog::PAY_METHOD_YHK: // 银行卡
                    $pinganApi = new PinganApi();
                    $financeLog->trade_no = $pinganApi->generateOrderNo(rand(10000000, 99999999));
                    $r = $pinganApi->KH0005($financeLog->trade_no, $this->financeLog->trade_no, 'RMB', -1 * $financeLog->money, '订单退款', '');
                    if ($r === false || !is_array($r)) {
                        throw new Exception('无法申请退款，银行返回错误，请稍后重试。');
                    }
                    if (!empty($r['errorCode'])) {
                        throw new Exception('无法申请退款，银行退款请求返回错误：' . $r['errorCode'] . ':' . $r['errorMsg']);
                    }
                    if ($r['status'] != '01') {
                        throw new Exception('无法申请退款，银行退款请求返回：' . $r['status'] . '，请稍后重试。');
                    }
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_WX_SCAN: // 微信扫码
                case FinanceLog::PAY_METHOD_WX_APP: // 微信APP
                    $weixinApi = new WeixinAppApi();
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $this->uid;
                    $response = $weixinApi->refund($this->financeLog->trade_no, $financeLog->trade_no, $this->financeLog->money, -1 * $financeLog->money);
                    if (empty($response)) {
                        throw new Exception('无法申请退款');
                    }
                    if ($response['return_code'] != 'SUCCESS') {
                        throw new Exception($response['return_msg']);
                    }
                    if ($response['result_code'] != 'SUCCESS') {
                        throw new Exception('code:' . $response['err_code'] . ';msg:' . $response['err_code_des']);
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $financeLog->remark = print_r($response, true);
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                    $weixinApi = new WeixinMpApi();
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $this->uid;
                    $response = $weixinApi->refund($this->financeLog->trade_no, $financeLog->trade_no, $this->financeLog->money, -1 * $financeLog->money);
                    if (empty($response)) {
                        throw new Exception('无法申请退款');
                    }
                    if ($response['return_code'] != 'SUCCESS') {
                        throw new Exception($response['return_msg']);
                    }
                    if ($response['result_code'] != 'SUCCESS') {
                        throw new Exception('code:' . $response['err_code'] . ';msg:' . $response['err_code_des']);
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $financeLog->remark = print_r($response, true);
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_WX_H5: // 微信H5支付
                    $weixinApi = new WeixinH5Api();
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $this->uid;
                    $response = $weixinApi->refund($this->financeLog->trade_no, $financeLog->trade_no, $this->financeLog->money, -1 * $financeLog->money);
                    if (empty($response)) {
                        throw new Exception('无法申请退款');
                    }
                    if ($response['return_code'] != 'SUCCESS') {
                        throw new Exception($response['return_msg']);
                    }
                    if ($response['result_code'] != 'SUCCESS') {
                        throw new Exception('code:' . $response['err_code'] . ';msg:' . $response['err_code_des']);
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $financeLog->remark = print_r($response, true);
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_ZFB: // 支付宝
                    $alipayApi = new AlipayApi();
                    $financeLog->trade_no = date('YmdHis') . $this->uid;
                    try {
                        $response = $alipayApi->AlipayTradeRefund($this->financeLog->trade_no, '', $financeLog->trade_no, -1 * $financeLog->money);
                        if (empty($response)) {
                            throw new Exception('无法申请退款，支付宝退款请求没有正常返回。');
                        }
                        if ($response->code != '10000') {
                            throw new Exception('无法申请退款，支付宝退款请求返回：' . $response->code . ':' . $response->msg);
                        }
                        $financeLog->status = FinanceLog::STATUS_SUCCESS;
                        $financeLog->update_time = time();
                        $financeLog->remark = print_r($response, true);
                        $r = $financeLog->save();
                        if (!$r) {
                            Yii::error(json_encode($financeLog->errors));
                            throw new Exception('无法保存财务记录。');
                        }
                    } catch (\Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    break;
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝
                    $alipayApi = new AlipayApi();
                    $financeLog->trade_no = date('YmdHis') . $this->uid;
                    try {
                        $response = $alipayApi->AlipayTradeRefund($this->financeLog->trade_no, '', $financeLog->trade_no, -1 * $financeLog->money);
                        if (empty($response)) {
                            throw new Exception('无法申请退款，支付宝退款请求没有正常返回。');
                        }
                        if ($response->code != '10000') {
                            throw new Exception('无法申请退款，支付宝退款请求返回：' . $response->code . ':' . $response->msg);
                        }
                        $financeLog->status = FinanceLog::STATUS_SUCCESS;
                        $financeLog->update_time = time();
                        $financeLog->remark = print_r($response, true);
                        $r = $financeLog->save();
                        if (!$r) {
                            Yii::error(json_encode($financeLog->errors));
                            throw new Exception('无法保存财务记录。');
                        }
                    } catch (\Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY: // 通联支付
                    $allinpay_api = new AllInPayApi();
                    $financeLog->trade_no = date('YmdHis') . $this->uid;
                    $response = $allinpay_api->refund($this->financeLog->trade_no, -1 * $financeLog->money, $financeLog->trade_no, $this->financeLog->create_time);
                    if (empty($response) || !is_array($response) || !isset($response['refundResult']) || $response['refundResult'] != '20') {
                        throw new Exception('无法申请退款，退款请求返回' . print_r($response, true));
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $financeLog->remark = print_r($$response, true);
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY_H5: // 通联H5支付
                    $api = new AllInPayH5Api();
                    $financeLog->trade_no = date('YmdHis') . $this->uid;
                    $response = $api->refund($this->financeLog->trade_no, $this->financeLog->create_time, $financeLog->trade_no, -1 * $financeLog->money);
                    // TODO:处理退款结果
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY_ALI: // 通联支付宝
                    $allinpay_ali_api = new AllInPayAliApi();
                    $financeLog->trade_no = date('YmdHis') . $this->uid;
                    $json = $allinpay_ali_api->refund($this->financeLog->trade_no, $financeLog->trade_no, -1 * $financeLog->money);
                    if ($json['retcode'] != 'SUCCESS') {
                        throw new Exception($json['retmsg']);
                    }
                    if ($json['trxstatus'] != '0000') {
                        throw new Exception($json['errmsg']);
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $financeLog->remark = print_r($json, true);
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_YE: // 佣金
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $this->uid;
                    if (bccomp($financeLog->money, 0, 2) != 0) {
                        $r = UserAccount::updateAllCounters(['commission' => -1 * $financeLog->money], ['uid' => $this->uid]);
                        if ($r == 0) {
                            throw new Exception('无法更新账户余额。');
                        }
                    }
                    $ual = new UserAccountLog();
                    $ual->uid = $this->uid;
                    $ual->commission = -1 * $financeLog->money;
                    $ual->time = time();
                    $ual->remark = '取消订单退款';
                    if (!$ual->save()) {
                        throw new Exception('无法保存账户记录。');
                    }
                    // 添加佣金记录
                    $uc = new UserCommission();
                    $uc->uid = $this->uid;
                    $uc->from_uid = $this->uid;
                    $uc->level = 0;
                    $uc->commission = -1 * $financeLog->money;
                    $uc->time = time();
                    $uc->remark = '取消订单退款';
                    if (!$uc->save()) {
                        throw new Exception('无法保存佣金记录。');
                    }
                    $financeLog->status = FinanceLog::STATUS_SUCCESS;
                    $financeLog->update_time = time();
                    $r = $financeLog->save();
                    if (!$r) {
                        Yii::error(json_encode($financeLog->errors));
                        throw new Exception('无法保存财务记录。');
                    }
                    break;
                case FinanceLog::PAY_METHOD_COD: // 货到付款
                    // TODO：暂时没有货到付款支付
                    throw new Exception('没有货到付款。');
                    break;
                default:
                    throw new Exception('取消订单时无法确定订单付款方式。');
            }
            $this->cancel_fid = $financeLog->id;
            $this->status = Order::STATUS_CANCEL;
            $this->save(false);
            $trans->commit();
            return true;
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * 订单取消支付结果
     * @param boolean $is_success 是否成功
     * @return boolean
     */
    public function cancelPayNotify($is_success)
    {
        if ($is_success) {
            $this->status = Order::STATUS_CANCEL;
            return $this->save(false);
        }
        return true;
    }

    /**
     * 定时任务：订单自动取消
     */
    public static function task_force_cancel()
    {
        $order_force_cancel_minute = System::getConfig('order_force_cancel_minute');
        if ($order_force_cancel_minute <= 0) {
            return '没有设置自动取消订单的时间。';
        }
        $current_time = time();
        foreach (Order::find()
                     ->andWhere(['status' => Order::STATUS_CREATED])
                     ->each() as $order) {/** @var Order $order */
            if ($order->create_time < $current_time - $order_force_cancel_minute * 60) {
                $order->status = Order::STATUS_CANCEL;
                $order->save(false);
                // 积分订单  返还积分
                if ($order->is_score == 1) {
                    $order->saveUserOrderScore($order->uid, $order->id, $order->score, $order->itemList[0]->title . '商品售后，积分返还');
                }
                // 优惠券订单  还原优惠券未使用状态
                if ($order->is_coupon == 1 && !empty($order->coupon_id)) {
                    $order->saveUserGoodsCoupon($order->coupon_id);
                }
                OrderLog::info(null, OrderLog::U_TYPE_SYSTEM, $order->id, '自动取消订单。');
                Yii::warning('订单[' . $order->no . ']，创建时间[' . date('Y-m-d H:i:s', $order->create_time) . ']，自动设置订单取消。');
            }
        }
        return '订单自动取消任务执行完成。';
    }

    /**
     * 定时任务：订单强制收货
     */
    public static function task_force_receive()
    {
        $deliver_force_receive_day = System::getConfig('deliver_force_receive_day');
        if (empty($deliver_force_receive_day)) {
            return '没有设置强制收货的天数。';
        }
        $current_time = time();
        foreach (Order::find()->andWhere(['status' => Order::STATUS_DELIVERED])->each() as $order) {/** @var Order $order */
            /** @var OrderDeliver $order_deliver 最后发货的发货单 */
            $order_deliver = OrderDeliver::find()->andWhere(['oid' => $order->id])->orderBy('send_time DESC')->one();
            if (empty($order_deliver)) {
                // 无需发货的商品没有发货单
                continue;
            }
            if ($order_deliver->send_time < $current_time - $deliver_force_receive_day * 86400) {
                $order->status = Order::STATUS_RECEIVED;
                $order->receive_time = time();
                $order->save(false);
                Yii::warning('订单[' . $order->no . ']，最后发货时间[' . date('Y-m-d H:i:s', $order_deliver->send_time) . ']，强制设置收货。');
            }
        }
        return '强制收货任务执行完成。';
    }

    /**
     * 定时任务：订单强制评论
     */
    public static function task_force_comment()
    {
        $receive_force_comment_day = System::getConfig('receive_force_comment_day');
        if (empty($receive_force_comment_day)) {
            return '没有设置强制评论的天数。';
        }
        $force_comment_shop_score = System::getConfig('force_comment_shop_score');
        if (empty($force_comment_shop_score)) {
            return '没有设置强制评论店铺评分。';
        }
        $force_comment_goods_score = System::getConfig('force_comment_goods_score');
        if (empty($force_comment_goods_score)) {
            return '没有设置强制评论商品评分。';
        }
        $current_time = time();
        foreach (Order::find()->andWhere(['status' => Order::STATUS_RECEIVED])->each() as $order) {/** @var Order $order */
            if ($order->receive_time < $current_time - $receive_force_comment_day * 86400) {
                $order->status = Order::STATUS_COMPLETE;
                $order->save();
                // 店铺评分
                $shop_score = new ShopScore();
                $shop_score->sid = $order->sid;
                $shop_score->uid = $order->uid;
                $shop_score->oid = $order->id;
                $shop_score->score = $force_comment_shop_score;
                $shop_score->create_time = $current_time;
                $shop_score->save(false);
                // 商品评分
                foreach ($order->itemList as $item) {
                    $comment = new GoodsComment();
                    $comment->gid = $item->gid;
                    $comment->uid = $order->uid;
                    $comment->oid = $order->id;
                    $comment->sku_key_name = $item->sku_key_name;
                    $comment->score = $force_comment_goods_score;
                    $comment->is_anonymous = 1;
                    $comment->status = GoodsComment::STATUS_SHOW;
                    $comment->create_time = $current_time;
                    $comment->save(false);
                }
                Yii::warning('订单[' . $order->no . ']，收货时间[' . date('Y-m-d H:i:s', $order->receive_time) . ']，强制评论。');
            }
        }
        return '强制评论任务执行完成。';
    }

    /**
     * 定时任务：生成结算单
     */
    public static function task_create_financial_settlement()
    {
        $order_complete_financial_settlement_day = System::getConfig('order_complete_financial_settlement_day');
        if (empty($order_complete_financial_settlement_day)) {
            //return '没有设置生成结算单的天数。';
        }
        $merchant_charge_ratio = System::getConfig('merchant_charge_ratio');
        if (empty($merchant_charge_ratio)) {
            //return '没有设置平台服务费比例。';
        }
        $order_complete_financial_settlement_day =0;
        $current_time = time();
        $order_query = Order::find()->orWhere(['status' => Order::STATUS_COMPLETE])
            ->orWhere(['AND', ['status' => Order::STATUS_DELETE], '`delete_time` IS NOT NULL', '`receive_time` IS NOT NULL']);
        foreach ($order_query->each() as $order) {/** @var Order $order */
            if ($order->receive_time >= $current_time - $order_complete_financial_settlement_day * 86400) {
                //continue;
            }
            $financial_settlement = MerchantFinancialSettlement::find()->andWhere(['oid' => $order->id])->one();
            if (!empty($financial_settlement)) {
                continue;
            }
            $_merchant_charge_ratio = MerchantConfig::getConfig($order->shop->mid, 'merchant_charge_ratio', $merchant_charge_ratio);
            $charge = ($order->amount_money - $order->deliver_fee) * $_merchant_charge_ratio;
            $refund_money = 0;
            if (OrderRefund::find()
                ->andWhere(['oiid' => ArrayHelper::getColumn($order->itemList, 'id')])
                ->andWhere(['status' => [
                    OrderRefund::STATUS_REQUIRE,
                    OrderRefund::STATUS_ACCEPT,
                    OrderRefund::STATUS_SEND,
                    OrderRefund::STATUS_RECEIVE
                ]])
                ->exists()) {
                Yii::warning('订单[' . $order->no . ']生成结算单时发现有未完成的售后，此订单将被忽略。');
                continue;
            }
            $trans = Yii::$app->db->beginTransaction();
            try {
                foreach (OrderRefund::find()
                             ->andWhere(['oiid' => ArrayHelper::getColumn($order->itemList, 'id')])
                             ->andWhere(['status' => OrderRefund::STATUS_COMPLETE])
                             ->each() as $refund) {
                    /** @var OrderRefund $refund */
                    $refund_money += $refund->money;
                }
                $merchant_receive_money = $order->amount_money - $refund_money - $charge - Order::shareCommission($order);
                $financial_settlement = new MerchantFinancialSettlement();
                $financial_settlement->mid = $order->shop->mid;
                $financial_settlement->oid = $order->id;
                $financial_settlement->order_money = $order->amount_money;
                $financial_settlement->refund_money = $refund_money;
                $financial_settlement->merchant_receive_money = $merchant_receive_money;
                $financial_settlement->charge = $charge;
                $financial_settlement->status = MerchantFinancialSettlement::STATUS_WAIT;
                $financial_settlement->create_time = $current_time;
                $r = $financial_settlement->save();
                if (!$r) {
                    throw new Exception('无法保存商户订单结算单：' . print_r($financial_settlement->attributes, true) . print_r($financial_settlement->errors, true));
                }

                // 商户结算单 结束
                // 供货商结算单 开始
                foreach ($order->itemList as $orderItem) {
                    if (empty($orderItem->goods->supplier_id) ||  $orderItem->goods->sale_type != Goods::TYPE_SUPPLIER) {
                        continue;
                    }
                    if (empty($orderItem->goodsSku->supplier_price) && empty($orderItem->goods->supplier_price)) {
                        throw new Exception('供货商商品【' . $orderItem->gid . '】【' . $orderItem->title . '】规格【' . $orderItem->sku_key_name . '】没有设置供货商结算价。');
                    }
                    $deliver_fee = 0;
                    $orderItemCount = OrderItem::find()->where(['oid' => $orderItem->order->id])->count();
                    if ($orderItemCount == 1) {
                        $deliver_fee = $orderItem->order->deliver_fee;
                    }
                    $suppler_price = $orderItem->supplier_price;
                    if (empty($orderItem->supplier_price)) {
                        $suppler_price = (empty($orderItem->goodsSku) ? $orderItem->goods->supplier_price : $orderItem->goodsSku->supplier_price);
                    }
                    $supplierSettlement = new SupplierFinancialSettlement();
                    $supplierSettlement->sid = $orderItem->goods->supplier_id;
                    $supplierSettlement->oid = $order->id;
                    $supplierSettlement->oiid = $orderItem->id;
                    $supplierSettlement->gid = $orderItem->gid;
                    $supplierSettlement->price = $suppler_price + $deliver_fee;
                    $supplierSettlement->amount = $orderItem->amount;
                    $supplierSettlement->money = 0; // 需要等到计算结算单时重新计算（因为可能有售后）
                    $supplierSettlement->status = SupplierFinancialSettlement::STATUS_WAIT_DAY;
                    $supplierSettlement->create_time = empty($order->receive_time)? $order->create_time :$order->receive_time;
                    Yii::warning($supplierSettlement->attributes);
                    if (!$supplierSettlement->save()) {
                        $error = new SystemError();
                        $error->time = time();
                        $error->message = '无法生成供货商结算单，订单号[' . $order->no . ']，错误信息：' . print_r($supplierSettlement->errors, true);
                        $error->status = SystemError::STATUS_WAIT;
                        $error->save();
                    }
                }
                // 供货商结算单 结束
                $trans->commit();
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $e) {
                }
                Yii::error('订单生成结算单出现错误：' . $e->getMessage());
                Yii::error($e->getFile() . ' L' . $e->getLine());
            }
        }
        return '生成结算单任务执行完成。';
    }

    /**
     * 处理分享佣金
     * @param $order Order 需要处理的订单
     * @return float
     * @throws Exception
     */
    private static function shareCommission($order)
    {
        $commission = 0;
        if ($order->is_score == 1) {
            return $commission;
        }
        if (empty($order->user->parent)) {
            return $commission;
        }
        $parent = $order->user->parent;
        if ($parent->status == User::STATUS_WAIT) {
            //return $commission; // 如果上级没有激活不给佣金  // 放开非激活用户 可以获得佣金
        }
        if ($order->is_coupon == 1) {
            return $commission; // 活动订单不返佣金
        }
        if ($order->user->status == User::STATUS_OK && $parent->status == User::STATUS_WAIT) {
            return $commission; // 如果上级没有激活不给佣金  自己激活了
        }
        $recommend_keep_day = System::getConfig('recommend_keep_day'); // 推荐关系有效期（天）
        $share_commission_ratio_1 = ShopConfig::getConfig($order->sid, 'share_commission_ratio_1');
        $share_commission_ratio_1 = $order->user->childBuyRatio;
        $share_commission_ratio_2 = 0;
        if ($order->user->status == User::STATUS_OK) {
            $share_commission_ratio_2 = $order->user->buyRatio;
        }
        if ($order->user->status == User::STATUS_OK) {
            $share_commission_ratio_1 = 30;
        }

        foreach ($order->itemList as $item) {
            $refund = OrderRefund::find()->where(['oid' => $order->id, 'oiid' => $item->id])->andWhere(['status' => 5])->one();
            if ($refund) {
                // 已售后不再给佣金
                continue;
            }
            if (!in_array($item->goods->share_commission_type, [Goods::SHARE_COMMISSION_TYPE_MONEY, Goods::SHARE_COMMISSION_TYPE_RATIO])) {
                // 此商品不参与分享佣金
                continue;
            }
            if ($item->goods->is_pack == 1 || $order->is_coupon == 1 ) {
                // 此商品礼包商品/活动商品不参与分享佣金
                continue;
            }
            // 一级分享
            if (empty($share_commission_ratio_1) || Util::comp($share_commission_ratio_1, 0, 2) <= 0) {
                // 店铺没有设置一级分享佣金比例
                continue;
            }
            if (!$order->user->parent) { //没有上级
                continue;
            }
//            if ($order->user->parent->status == User::STATUS_WAIT) { // 上级没有激活
//                continue;
//            }
            $parent = $order->user->parent;
//            if ($parent->status == User::STATUS_WAIT) {
//                continue; // 如果上级没有激活不给佣金
//            }
//            $recommend_1 = UserRecommend::findRecommend($order->uid, $order->sid, $item->gid, time() - $recommend_keep_day * 86400);
//            if (empty($recommend_1)) { // 没有人分享
//                continue;
//            }
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
                }else{
                    $item_commission_1 = round($item->price * $sku->commission * $share_commission_ratio_1 * $item->amount / 10000, 2);
                }
            }
            Yii::warning('订单分享佣金：uid[' . $order->uid . ']->[' . $parent->id . ']oid[' . $order->id . ']oiid[' . $item->id . ']:' . $item_commission_1);

            if (Util::comp($item_commission_1, 0, 2) > 0) {
                Order::saveUserShareCommission($parent->id, $order->uid, $order->id, $item_commission_1, '一级分享佣金', $item->id);
                $commission += $item_commission_1;
                //发送用户消息
                $message=new UserMessage();
                $url=Yii::$app->params['site_host'].'/h5/user/commission-list';
                $message->MessageSend($parent->id,'您有新的佣金到账',$url,'您有新的佣金到账');
            }
            //特殊订单 自购省下的 也给返佣给自己
            $order_arr = [804, 808, 809, 813, 818, 819, 821, 822, 824, 829, 831, 833, 839, 840, 842, 843, 846, 847, 851,
                852, 854, 855, 856, 857, 859, 860, 871, 873, 875, 882, 883, 885, 886, 888, 891, 893, 899, 904, 910, 911, 913, 916, 917, 923, 955];
            if (in_array($order->id, $order_arr)) {
                $self_commission = round($item->goods->share_commission_value * $share_commission_ratio_2 * $item->amount / 100, 2);
                Order::saveUserShareCommission($order->uid, $order->uid, $order->id, $self_commission, '自购返佣金');
            }
        }
        return $commission;
    }

    /**
     * 保存用户分享佣金
     * @param $uid integer 用户编号
     * @param $from_uid integer 来源用户编号
     * @param $oid integer 订单编号
     * @param $money float 金额
     * @param $remark string 备注
     * @param $oiid integer| string
     * @throws Exception
     */
    private static function saveUserShareCommission($uid, $from_uid, $oid, $money, $remark, $oiid = '')
    {
        $result = UserCommission::find()->where(['uid' => $uid, 'from_uid' => $from_uid, 'oid' => $oid, 'commission' => $money])->one();
        if (!empty($result)) {
            return ;
        }
        $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $uid]);
        if ($r <= 0) {
            throw new Exception('无法更新用户账户：' . $uid . ' commission ' . $money);
        }
        $ual = new UserAccountLog();
        $ual->uid = $uid;
        $ual->commission = $money;
        $ual->time = time();
        $ual->remark = $remark;
        $r = $ual->save();
        if (!$r) {
            throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
        }
        $userCommission = new UserCommission();
        $userCommission->uid = $uid;
        $userCommission->from_uid = $from_uid;
        $userCommission->level = 1;
        $userCommission->type = UserCommission::TYPE_FIRST;
        $userCommission->commission = $money;
        $userCommission->time = time();
        $userCommission->remark = '订单编号：' . $oid . $remark;
        $userCommission->oid = $oid;
        $userCommission->oiid = $oiid;
        $r = $userCommission->save();
        if (!$r) {
            throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
        }
    }

    /**
     * 保存用户分享佣金
     * @param $uid integer 用户编号
     * @param $oid integer 订单编号
     * @param $money float 金额
     * @param $remark string 备注
     * @throws Exception
     */
    private static function saveUserShareCommissionBak($uid, $oid, $money, $remark)
    {
        $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $uid]);
        if ($r <= 0) {
            throw new Exception('无法更新用户账户：' . $uid . ' commission ' . $money);
        }
        $ual = new UserAccountLog();
        $ual->uid = $uid;
        $ual->commission = $money;
        $ual->time = time();
        $ual->remark = $remark;
        $r = $ual->save();
        if (!$r) {
            throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
        }
        $userCommission = new UserCommission();
        $userCommission->uid = $uid;
        $userCommission->oid = $oid;
        $userCommission->level = 1;
        $userCommission->commission = $money;
        $userCommission->time = time();
        $r = $userCommission->save();
        if (!$r) {
            throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
        }
    }

    /**
     * 每个月 如果是 店主或者服务商 把会员团队成员  育成店主 育成服务商 月总结佣金的  百分比佣金
     * @return string
     * @throws Exception
     */
    public static function task_create_month_commission_log()
    {

        $trans = Yii::$app->db->beginTransaction();
        try {
            //查询 本月获取了 佣金的 所有等级会员
            //如果是会员  就给  他的团队店主或者服务商佣金
            //如果是店主  就给  他的上级店主或者服务商佣金
            //如果是服务商  就给  他的上级店主或者服务商佣金
            /**
             *分佣比率
             * 会员 1.直接销售每单30% 2.直属会员月分销总额30%
             * 店主 1.直接销售每单40% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30%
             * 服务商 1.直接销售每单50% 2.直属团队每人的分销总额30%  3.育成店主月结算佣金的30% 4.育成服务商月结算佣金的30%
             */
//            $BeginDate = date('Y-m-01', strtotime(date("Y-m-d")));
//            $monthStartTime = strtotime(date('Y-m-01', strtotime(date("Y-m-d"))));
//            $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;

            $BeginDate = date('Y-m-01', strtotime('-1 month'));
            $monthStartTime = strtotime($BeginDate);
            $monthEndTime = strtotime(date('Y-m-d', strtotime("$BeginDate +1 month -1 day")))+86399;
            /** @var User $user */
            $giveUserList = UserCommission::find()->asArray()->joinWith('user')->groupBy('uid')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc, user.id desc')->all();
            $giveUserUid = array_column($giveUserList, 'uid');

            /** @var UserCommission $commission */
            foreach (UserCommission::find()->joinWith('user')->groupBy('uid')->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])->orderBy('level_id asc, user.id desc')->each() as $commission) {
                $user = $commission->user;
                if ($user->status == User::STATUS_WAIT) {
                    continue; // 自身没激活  虽然分享给下级获得分佣了  不再给上级继续月结分佣
                }
                #先给直接上级
                if (!$user->parent) {
                    Yii::warning('没有上级 ' .$user->id);
                    continue;
                }
                if ($user->parent->level_id < $user->level_id) { // 如果上级比自己等级低 不再给上级
                    Yii::warning('上级id ' . $user->parent->id . '上级level_id ' .$user->parent->level_id . ' 自身id ' . $user->id . '自身level_id' . $user->level_id);
                    continue;
                }
                $toUser = $user->parent;
                if ($toUser->status == User::STATUS_WAIT) {
                    continue;
                }
                // 普通会员直接给上级 30%
                // 店主或者服务商 是月结算给上级 30%
                $query = UserCommission::find();
                $query->where(['>=', 'time', $monthStartTime])
                    ->andWhere(['<=', 'time', $monthEndTime])->andWhere(['uid' => $user->id]);
                if ($user->level_id == 1) {
                    //$query->andWhere(['type' => UserCommission::TYPE_FIRST]);
                } elseif ($user->level_id == 2 || $user->level_id == 3) {
                    //$query->andWhere(['type' => UserCommission::TYPE_MONTH]);
                }
                $query->andWhere(['type' => UserCommission::TYPE_FIRST]);
                $commissionMoney = $query->sum('commission');
                $money = $commissionMoney * 30 /100;
                if (Util::comp($money, 0, 2) > 0) {
                    $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $toUser->id]);
                    if ($r <= 0) {
                        throw new Exception('无法更新用户账户：' . $toUser->id . ' commission ' . $money);
                    }
                    $ual = new UserAccountLog();
                    $ual->uid = $toUser->id;
                    $ual->commission = $money;
                    $ual->time = time();
                    $ual->remark = '直接1级月结佣金';
                    $r = $ual->save();
                    if (!$r) {
                        throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                    }

                    $userCommission = new UserCommission();
                    $userCommission->uid = $toUser->id;
                    $userCommission->from_uid = $user->id;
                    $userCommission->commission = $money;
                    $userCommission->type = UserCommission::TYPE_MONTH;
                    $userCommission->time = time();
                    $userCommission->remark = '直接下级月结佣金返佣30%';
                    $r = $userCommission->save();
                    if (!$r) {
                        throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                    }
                    Yii::warning('直接1级佣金 ' .$user->id . '给 ' . $toUser->id . '月佣金');
                }
                #团队上级
                if ($user->level_id == 1 && $user->parent->level_id == 1) {
                    $teamUser = $user->commissionTree($user->parent);
                    if ($teamUser->status == User::STATUS_WAIT) {
                        continue;
                    }
                    $commissionMoney = UserCommission::find()->where(['>=', 'time', $monthStartTime])
                        //->andWhere(['type' => UserCommission::TYPE_MONTH])
                        ->andWhere(['type' => UserCommission::TYPE_FIRST])
                        ->andWhere(['<=', 'time', $monthEndTime])->andWhere(['uid' => $user->id])->sum('commission');
                    $money = $commissionMoney * 30 /100;
                    if (Util::comp($money, 0, 2) > 0) {
                        $r = UserAccount::updateAllCounters(['commission' => $money], ['uid' => $teamUser->id]);
                        if ($r <= 0) {
                            throw new Exception('无法更新用户账户：' . $teamUser->id . ' commission ' . $money);
                        }
                        $ual = new UserAccountLog();
                        $ual->uid = $teamUser->id;
                        $ual->commission = $money;
                        $ual->time = time();
                        $ual->remark = '直属会员团队月结';
                        $r = $ual->save();
                        if (!$r) {
                            throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
                        }

                        $userCommission = new UserCommission();
                        $userCommission->uid = $teamUser->id;
                        $userCommission->from_uid = $user->id;
                        $userCommission->commission = $money;
                        $userCommission->type = UserCommission::TYPE_MONTH;
                        $userCommission->time = time();
                        $userCommission->remark = '团队会员月佣金返佣30%';
                        $r = $userCommission->save();
                        if (!$r) {
                            throw new Exception('无法保存用户佣金记录：' . print_r($userCommission->attributes, true) . print_r($userCommission->errors, true));
                        }
                        Yii::warning('直属会员团队佣金 ' .$user->id . '给 ' . $toUser->id . '月佣金');
                    }

                }

            }

            /** @var UserCommission $commission2 */
            foreach (UserCommission::find()->joinWith('user')->groupBy('uid')
                         ->where(['>=', 'time', $monthStartTime])->andWhere(['<=', 'time', $monthEndTime])
                         ->andWhere(['type' => UserCommission::TYPE_MONTH])
                         ->orderBy('level_id asc, user.id desc')->each() as $commission2) {
                $user2 = $commission2->user;
                if (!$user2->parent) {
                    continue;
                }
                /** @var User $toUser2 */
                $toUser2 = $user2->parent;
                if ($user2->level_id != 2 && $user2->level_id != 3) {
                    continue;
                }
                if ($user2->level_id == 2 && $toUser2->level_id != 3) {
                    continue;
                }
                if ($user2->level_id == 3 && $toUser2->level_id != 3) {
                    continue;
                }
                //if (!in_array($user2->id, $giveUserUid)) {

                    $commissionMoney2 = UserCommission::find()->where(['>=', 'time', $monthStartTime])
                        //->andWhere(['type' => UserCommission::TYPE_MONTH])
                        ->andWhere(['type' => UserCommission::TYPE_MONTH])
                        ->andWhere(['<=', 'time', $monthEndTime])->andWhere(['uid' => $user2->id])->sum('commission');
                    $money2 = $commissionMoney2 * 30 /100;
                    if (Util::comp($money2, 0, 2) > 0) {
                        $r = UserAccount::updateAllCounters(['commission' => $money2], ['uid' => $toUser2->id]);
                        if ($r <= 0) {
                            throw new Exception('无法更新用户账户：' . $toUser2->id . ' commission ' . $money2);
                        }
                        $ual2 = new UserAccountLog();
                        $ual2->uid = $toUser2->id;
                        $ual2->commission = $money2;
                        $ual2->time = time();
                        $ual2->remark = '育成月结';
                        $r = $ual2->save();
                        if (!$r) {
                            throw new Exception('无法保存用户账户记录：' . print_r($ual2->attributes, true) . print_r($ual2->errors, true));
                        }

                        $userCommission2 = new UserCommission();
                        $userCommission2->uid = $toUser2->id;
                        $userCommission2->from_uid = $user2->id;
                        $userCommission2->commission = $money2;
                        $userCommission2->type = UserCommission::TYPE_MONTH;
                        $userCommission2->time = time();
                        $userCommission2->remark = '育成佣金返佣30%';
                        $r = $userCommission2->save();
                        if (!$r) {
                            throw new Exception('无法保存用户佣金记录：' . print_r($userCommission2->attributes, true) . print_r($userCommission2->errors, true));
                        }
                        Yii::warning('育成佣金返佣 ' .$user2->id . '给 ' . $toUser2->id . '月佣金');
                    }
                //}
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            Yii::error('佣金月结出现错误：' . $e->getMessage());
            Yii::error($e->getFile() . ' L' . $e->getLine());
        }
        return '生成佣金月结算单任务执行完成。';
    }

    /**
     * 处理订单积分  取消 或者删除未支付订单 返还积分
     * @param $uid integer 用户编号
     * @param $oid integer 订单编号
     * @param $score integer 积分
     * @param $remark string 备注
     * @throws Exception
     */
    public function saveUserOrderScore($uid, $oid, $score, $remark)
    {
        $r = UserAccount::updateAllCounters(['score' => $score], ['uid' => $uid]);
        if ($r <= 0) {
            throw new Exception('无法更新用户账户：' . $uid . ' score ' . $score);
        }
        $ual = new UserAccountLog();
        $ual->uid = $uid;
        $ual->oid = $oid;
        $ual->score = $score;
        $ual->time = time();
        $ual->remark = $remark;
        $r = $ual->save();
        if (!$r) {
            throw new Exception('无法保存用户账户记录：' . print_r($ual->attributes, true) . print_r($ual->errors, true));
        }
    }

    /**
     * 处理订单优惠券  取消 或者删除未支付订单 优惠券状态还原未使用
     * @param $coupon_id integer 用户优惠券编号
     * @throws Exception
     */
    public function saveUserGoodsCoupon($coupon_id)
    {
        $coupon = GoodsCouponGiftUser::findOne($coupon_id);
        if (empty($coupon)) {
            throw new Exception('没有该优惠券信息');
        }
        $coupon->status=GoodsCouponGiftUser::STATUS_WAIT;
        $r = $coupon->save(false);
        if (!$r) {
            throw new Exception('优惠券状态更新失败');
        }
    }

    /**
     * 生成发货单
     * @param integer $sid 店铺编号
     * @param array $itemList 商品编号数量列表 {oiid1: amount1, oiid2: amount2},
     * @throws Exception
     */
    public function generateDeliver($sid = null, $itemList = null)
    {
        if ($this->status != Order::STATUS_PAID && $this->status != Order::STATUS_PACKING) {
            throw new Exception('订单状态错误。');
        }

        if (!empty($itemList)) { // 只处理提交的商品
            foreach ($this->itemList as $orderItem) {
                if (!empty($sid) && isset($itemList[$orderItem->id]) && !empty($itemList[$orderItem->id]) && !empty($orderItem->goods->supplier_id)) {
                    throw new Exception('此商品需要供货商发货。');
                }
                if (isset($itemList[$orderItem->id])) {
                    if (empty($itemList[$orderItem->id])) {
                        unset($itemList[$orderItem->id]);
                    } else {
                        if ($orderItem->getDeliverAmount() > $orderItem->amount) {
                            unset($itemList[$orderItem->id]);
                            continue;
                        }
                        if ($itemList[$orderItem->id] > $orderItem->amount - $orderItem->getDeliverAmount()) {
                            throw new Exception('[' . $orderItem->title . ']数量错误。');
                        }
                        $itemList[$orderItem->id] = [
                            'amount' => $itemList[$orderItem->id],
                            'orderItem' => $orderItem,
                        ];
                    }
                }
            }
        } else { // 订单中全部商品都要处理
            foreach ($this->itemList as $orderItem) {
                $itemList[$orderItem->id] = [
                    'amount' => $orderItem->amount,
                    'orderItem' => $orderItem,
                ];
            }
        }
        if (empty($itemList)) {
            return;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            // 根据供货商分组
            $supplierItemList = [];
            foreach ($itemList as $item) {
                /** @var OrderItem $orderItem */
                $orderItem = $item['orderItem'];
                $supplierId = intval($orderItem->goods->supplier_id);
                if (!isset($supplierItemList[$supplierId])) {
                    $supplierItemList[$supplierId] = [];
                }
                $supplierItemList[$supplierId][] = $item;
            }
            foreach ($supplierItemList as $supplierId => $orderItemList) {
                $deliver = new OrderDeliver();
                $deliver->oid = $this->id;
                if (!empty($supplierId)) {
                    $deliver->supplier_id = $supplierId;
                }
                $deliver->status = OrderDeliver::STATUS_WAIT;
                $deliver->create_time = time();
                $r = $deliver->save();
                if (!$r) {
                    throw new Exception('无法保存发货单。');
                }
                foreach ($orderItemList as $item) {
                    $amount = $item['amount'];
                    /** @var OrderItem $orderItem */
                    $orderItem = $item['orderItem'];
                    if (!empty($itemList) && (!isset($itemList[$orderItem->id]) || empty($itemList[$orderItem->id]))) {
                        continue;
                    }
                    $deliverItem = new OrderDeliverItem();
                    $deliverItem->did = $deliver->id;
                    $deliverItem->oiid = $orderItem->id;
                    $deliverItem->amount = $amount;
                    $r = $deliverItem->save();
                    if (!$r) {
                        throw new Exception('无法保存发货单内容。');
                    }
                }
                OrderLog::info($sid, empty($sid) ? OrderLog::U_TYPE_SYSTEM : OrderLog::U_TYPE_MERCHANT, $this->id, '生成发货单。', print_r($deliver->attributes, true));
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $_) {
            }
            throw $e;
        }

        if ($this->status == Order::STATUS_PAID) {
            $this->status = Order::STATUS_PACKING; // 配货中
            OrderLog::info($sid, empty($sid) ? OrderLog::U_TYPE_SYSTEM : OrderLog::U_TYPE_MERCHANT, $this->id, '更新订单状态到配货中。');
            $this->save(false);
            $this->refresh();
        }

        /*
         * 如果店铺发货的商品已经全部发货
         * 则将供货商发货的商品统一生成发货单
         * 如果全部商品都已生成发货单
         * 更新订单状态到下一步
         */
        $shopLeft = 0;
        $supplierLeft = 0;
        foreach ($this->itemList as $orderItem) {
            $left = $orderItem->amount - $orderItem->getDeliverAmount();
            if (!empty($orderItem->goods->supplier_id)) {
                $supplierLeft += $left;
            } else {
                $shopLeft += $left;
            }
        }
        if ($shopLeft <= 0) {
            if ($supplierLeft <= 0) {
                $this->status = Order::STATUS_PACKED; // 已配货 待发货
                $this->save(false);
                $this->refresh();
                OrderLog::info($sid, empty($sid) ? OrderLog::U_TYPE_SYSTEM : OrderLog::U_TYPE_MERCHANT, $this->id, '更新订单状态到已配货待发货。');
            } else {
                // 店铺已全部生成发货单，将供货商的商品统一生成发货单
                // 根据供货商分组
                $supplierItemList = [];
                foreach ($this->itemList as $orderItem) {
                    if (empty($orderItem->goods->supplier_id)) {
                        continue;
                    }
                    if ($orderItem->amount - $orderItem->getDeliverAmount() <= 0) {
                        continue;
                    }
                    $supplierId = intval($orderItem->goods->supplier_id);
                    if (!isset($supplierItemList[$supplierId])) {
                        $supplierItemList[$supplierId] = [];
                    }
                    $supplierItemList[$supplierId][$orderItem->id] = $orderItem->amount;
                }
                foreach ($supplierItemList as $supplierId => $itemList) {
                    try {
                        $this->generateDeliver(null, $itemList);
                    } catch (Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * 定时任务：计算结算单
     */
    public static function task_calc_financial_settlement()
    {
        // 从确认收货到生成计算单的天数
        $day = System::getConfig('order_complete_financial_settlement_day');
        $day = 0;
        if (empty($day)) {
            //return '没有设置生成结算单的天数。';
        }

        /**
         * @var float 平台收取商户的针对商品金额的默认比例
         */
        $ratio = System::getConfig('merchant_charge_ratio');
        if (empty($ratio)) {
            return '没有设置平台服务费比例。';
        }

        /**
         * @var float 认证商家平台收取服务费比例
         */
        $certificate_ratio = System::getConfig('certificate_merchant_charge_ratio', $ratio);
        $current_time = time();
        /** @var MerchantFinancialSettlement $settlement */
        foreach (MerchantFinancialSettlement::find()
                     ->andWhere(['status' => MerchantFinancialSettlement::STATUS_WAIT])
                     ->each() as $settlement) {
            if (($settlement->create_time >= $current_time - $day * 86400)
                || ($settlement->merchant->status == Merchant::STATUS_COMPLETE && $settlement->create_time >= $current_time - $day * 86400)) {
                continue;
            }
            /**
             * @var float 判断是否是认证商家决定服务费比例
             */
            if ($settlement->order->shop->merchant->status == Merchant::STATUS_COMPLETE) {
                $_ratio = $certificate_ratio;
            } else {
                $_ratio = $ratio;
            }
            /**
             * @var float 平台收取商户的针对商品金额的比例
             */
            $merchant_ratio = MerchantConfig::getConfig($settlement->order->shop->mid, 'merchant_charge_ratio', '');
            if (!empty($merchant_ratio)) {
                $_ratio = $merchant_ratio;
            }
            $charge = ($settlement->order->amount_money - $settlement->order->deliver_fee) * $_ratio;


            $refund_money = 0;
            if (OrderRefund::find()
                ->andWhere(['oid' => $settlement->oid])
                ->andWhere(['status' => [
                    OrderRefund::STATUS_REQUIRE,
                    OrderRefund::STATUS_ACCEPT,
                    OrderRefund::STATUS_SEND,
                    OrderRefund::STATUS_RECEIVE,
                ]])
                ->exists()) {
                Yii::warning('订单[' . $settlement->order->no . ']生成结算单时发现有未完成的售后，此订单将被忽略。');
                continue;
            }

            $trans = Yii::$app->db->beginTransaction();
            try {
                foreach (OrderRefund::find()
                             ->andWhere(['oid' => $settlement->oid])
                             ->andWhere(['status' => OrderRefund::STATUS_COMPLETE])
                             ->each() as $refund) {
                    /** @var OrderRefund $refund */
                    $refund_money += $refund->money;
                }
                //$share_money = Order::shareCommission($settlement->order);
                foreach ($settlement->order->itemList as $orderItem) {
                    $supplierPayout = Order::calcSupplierFinancialSettlement($orderItem);

                }
                $merchant_receive_money = $settlement->order->amount_money - $refund_money - $charge ;//- $share_money;
                $settlement->refund_money = $refund_money;
                $settlement->merchant_receive_money = $merchant_receive_money;
                $settlement->charge = $charge;
                $settlement->status = MerchantFinancialSettlement::STATUS_WAIT_SETTLE;
                $r = $settlement->save();
                if (!$r) {
                    throw new Exception('无法保存商户订单结算单：' . print_r($settlement->attributes, true) . print_r($settlement->errors, true));
                }

                $trans->commit();
            } catch (Exception $e) {
                try {
                    $trans->rollBack();
                } catch (Exception $_) {
                }
                Yii::error('订单生成结算单出现错误：' . $e->getMessage());
                Yii::error($e->getFile() . ' L' . $e->getLine());
            }
        }
        return '生成结算单任务执行完成。';
    }

    /**
     * 供货商结算
     * @param OrderItem $orderItem 需要处理的订单内容
     * @return float 供货商相关支出
     * @throws Exception
     */
    public static function calcSupplierFinancialSettlement($orderItem)
    {
        if (empty($orderItem->goods->supplier_id) || $orderItem->goods->sale_type != Goods::TYPE_SUPPLIER) {
            // 此商品没有供货商
            return 0;
        }
        $deliver_fee = 0;
        $orderItemCount = OrderItem::find()->where(['oid' => $orderItem->order->id])->count();
        if ($orderItemCount == 1) {
            $deliver_fee = $orderItem->order->deliver_fee;
        }
        $refundAmount = OrderRefund::find()
            ->andWhere(['oiid' => $orderItem->id])
            ->andWhere(['status' => OrderRefund::STATUS_COMPLETE])
            ->sum('amount');
        $amount = $orderItem->amount - $refundAmount;
        $supplierFinancialSettlement = SupplierFinancialSettlement::findOne(['oiid' => $orderItem->id]);
        if (empty($supplierFinancialSettlement)) {
            throw new Exception('没有找到供货商该结算单。');
        }
        if ($amount <= 0) {
            Yii::warning($orderItem->id . '退款了不在生成结算记录了。');
            // 此商品已经全部处理售后
            $supplierFinancialSettlement->status = SupplierFinancialSettlement::STATUS_REFUND;
            $supplierFinancialSettlement->save();
            return 0;
        }
        $supplierFinancialSettlement->amount = $amount;
        $supplierFinancialSettlement->money = $supplierFinancialSettlement->price * $supplierFinancialSettlement->amount + $deliver_fee;
        $supplierFinancialSettlement->status = SupplierFinancialSettlement::STATUS_MONEY_FIXED;
        $supplierFinancialSettlement->save();
        return $supplierFinancialSettlement->money;
    }
}
