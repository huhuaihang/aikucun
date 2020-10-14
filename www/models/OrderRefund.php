<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 订单退货退款
 * Class OrderRefund
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $oid 订单编号
 * @property integer $oiid 订单内容编号
 * @property integer $supplier_id 供应商编号
 * @property integer $amount 商品数量
 * @property float $money 退款金额
 * @property integer $type 类型
 * @property string $reason 原因
 * @property string $image_list 图片列表JSON
 * @property integer $fid 财务记录编号
 * @property integer $status 状态
 * @property string $express_name 快递名称
 * @property string $express_no 快递单号
 * @property string $contact_mobile 联系手机
 * @property integer $create_time 创建时间
 * @property integer $update_time 修改金额时间
 * @property string $update_money_remark 修改金额备注
 * @property integer $apply_time 同意时间
 * @property integer $send_time 发货时间
 * @property integer $receive_time 收货时间
 * @property integer $complete_time 完成时间
 * @property integer $reject_time 拒绝时间
 * @property integer $delete_time 删除时间
 *
 * @property OrderItem $orderItem 关联订单内容
 * @property Order $order 关联订单
 * @property User $user 关联用户
 */
class OrderRefund extends ActiveRecord
{
    const TYPE_MONEY = 1; // 退款
    const TYPE_GOODS_MONEY = 2; // 退货退款
    const TYPE_EXCHANGE = 3; // 换货
    const TYPE_RESEND = 4; // 补发

    const STATUS_REQUIRE = 1; // 买家申请等待卖家同意
    const STATUS_ACCEPT = 2; // 卖家同意等待买家发货
    const STATUS_SEND = 3; // 买家已发货等待卖家收货
    const STATUS_RECEIVE = 4; // 卖家已收货等待退款
    const STATUS_COMPLETE = 5; // 退款成功售后完成
    const STATUS_REJECT = 9; // 卖家拒绝
    const STATUS_DELETE = 0; // 删除

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['reason', 'oiid', 'amount', 'money', 'type', 'status'], 'required'],
            [['image_list'], 'safe'],
            [['express_name', 'express_no', 'contact_mobile'], 'string', 'max' => 32],
            [['update_money_remark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'oiid' => '订单内容编号',
            'amount' => '商品数量',
            'type' => '类型',
            'reason' => '原因',
            'image_list' => '图片列表',
            'status' => '状态',
            'express_name' => '快递名称',
            'express_no' => '快递单号',
            'create_time' => '创建时间',
            'apply_time' => '同意时间',
            'send_time' => '发货时间',
            'receive_time' => '收货时间',
            'complete_time' => '完成时间',
            'reject_time' => '拒绝时间',
            'delete_time' => '删除时间',
        ];
    }

    /**
     * 返回当前售后应退款金额
     * @return float
     */
    public function getRefundMoney()
    {
        $order_item = OrderItem::findOne($this->oiid);
        $money = ($order_item->price - $order_item->self_money) * $order_item->amount;
       // $money = $money / $order_item->order->goods_money * $order_item->order->financeLog->money;
        return $money;
    }




    /**
     * 关联订单商品
     * @return \yii\db\ActiveQuery
     */
    public function getOrderItem()
    {
        return $this->hasOne(OrderItem::className(), ['id' => 'oiid']);
    }

    /**
     * 银行申请退款
     */
    public function doRefund()
    {
        $trans = Yii::$app->db->beginTransaction();
        try {
            /** @var Order $order */
            $order = $this->orderItem->order;
            $financeLog = new FinanceLog();
            $financeLog->type = FinanceLog::TYPE_ORDER_REFUND;
            $financeLog->money = -1 * $this->money;
            $financeLog->status = FinanceLog::STATUS_WAIT;
            $financeLog->create_time = time();
            $financeLog->pay_method = $order->financeLog->pay_method;
            switch ($order->financeLog->pay_method) {
                case FinanceLog::PAY_METHOD_YHK: // 银行卡
                    $pinganApi = new PinganApi();
                    $financeLog->trade_no = $pinganApi->generateOrderNo(rand(10000000, 99999999));
                    $r = $pinganApi->KH0005($financeLog->trade_no, $order->financeLog->trade_no, 'RMB', -1 * $financeLog->money, '订单退款', '');
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
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $order->uid;
                    $response = $weixinApi->refund($order->financeLog->trade_no, $financeLog->trade_no, $order->financeLog->money, -1 * $financeLog->money);
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
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $order->uid;
                    $response = $weixinApi->refund($order->financeLog->trade_no, $financeLog->trade_no, $order->financeLog->money, -1 * $financeLog->money);
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
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $order->uid;
                    $response = $weixinApi->refund($order->financeLog->trade_no, $financeLog->trade_no, $order->financeLog->money, -1 * $financeLog->money);
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
                    $financeLog->trade_no = date('YmdHis') . $order->uid;
                    try {
                        $response = $alipayApi->AlipayTradeRefund($order->financeLog->trade_no, '', $financeLog->trade_no, -1 * $financeLog->money);
                        if (empty($response)) {
                            throw new Exception('无法申请售后退款，支付宝退款请求没有正常返回。');
                        }
                        if ($response->code != '10000') {
                            throw new Exception('无法申请售后退款，支付宝退款请求返回：' . $response->code . ':' . $response->msg);
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
                case FinanceLog::PAY_METHOD_ZFB_APP: // 支付宝APP
                    $alipayApi = new AlipayApi();
                    $financeLog->trade_no = date('YmdHis') . $order->uid;
                    try {
                        $response = $alipayApi->AlipayTradeRefund($order->financeLog->trade_no, '', $financeLog->trade_no, -1 * $financeLog->money);
                        if (empty($response)) {
                            throw new Exception('无法申请售后退款，支付宝退款请求没有正常返回。');
                        }
                        if ($response->code != '10000') {
                            Yii::error(json_encode($response));
                            throw new Exception('无法申请售后退款，支付宝退款请求返回：' . $response->code . ':' . $response->msg . ':' . $response->sub_code);
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
                    $financeLog->trade_no = date('YmdHis') . $order->uid;
                    $response = $allinpay_api->refund($order->financeLog->trade_no, -1 * $financeLog->money, $financeLog->trade_no, $order->financeLog->create_time);
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
                case FinanceLog::PAY_METHOD_ALLINPAY_H5: // 通联H5
                    $api = new AllInPayH5Api();
                    $financeLog->trade_no = date('YmdHis') . $order->uid;
                    $response = $api->refund($order->financeLog->trade_no, $order->financeLog->create_time, $financeLog->trade_no, -1 * $financeLog->money);
                    // TODO:处理退款结果
                    break;
                case FinanceLog::PAY_METHOD_ALLINPAY_ALI: // 通联支付宝
                    $allinpay_ali_api = new AllInPayAliApi();
                    $financeLog->trade_no = date('YmdHis') . $order->uid;
                    $json = $allinpay_ali_api->refund($order->financeLog->trade_no, $financeLog->trade_no, -1 * $financeLog->money);
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
                    $financeLog->trade_no = 'Y' . date('YmdHis') . $order->uid;
                    if (Util::comp($financeLog->money, 0, 2) != 0) {
                        $r = UserAccount::updateAllCounters(['commission' => -1 * $financeLog->money], ['uid' => $order->uid]);
                        if ($r <= 0) {
                            throw new Exception('无法更新账户。');
                        }
                    }
                    $ual = new UserAccountLog();
                    $ual->uid = $order->uid;
                    $ual->commission = -1 * $financeLog->money;
                    $ual->time = time();
                    $ual->remark = '订单售后退款';
                    if (!$ual->save()) {
                        throw new Exception('无法保存账户记录。');
                    }
                    // 添加佣金记录
                    $uc = new UserCommission();
                    $uc->uid = $order->uid;
                    $uc->from_uid = $order->uid;
                    $uc->level = 0;
                    $uc->commission = -1 * $financeLog->money;
                    $uc->time = time();
                    $uc->remark = '订单售后退款';
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
                    throw new Exception('没有货到付款支付。');
                    break;
                default:
                    throw new Exception('订单申请售后时无法确定订单付款方式。');
            }
            OrderRefund::updateAll(['fid' => $financeLog->id], ['id' => $this->id]);
            $trans->commit();
            return true;
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
            Yii::error('售后退款[' . $this->orderItem->title . '] 订单号'. $this->orderItem->order->id .'时出现错误：' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * 退款支付结果
     * @param boolean $is_success 是否成功
     * @return boolean
     */
    public function refundPayNotify($is_success)
    {
        if ($is_success) {
            $this->status = OrderRefund::STATUS_COMPLETE;
            return $this->save(false);
        }
        return true;
    }

    /**
     * 定时任务自动同意申请退款/退货
     * @return string
     */
    public static function task_order_refund_force_accept()
    {
        return '不自动处理';
        $date = date('Ymd', time());
        $order_refund_force_accept_day = System::getConfig('order_refund_force_accept_day');
        Yii::warning('申请退款：' . $date, 'order_refund');
        $model = OrderRefund::find()->where(['status' => self::STATUS_REQUIRE])->all();
        foreach ($model as $item) { /** @var $item OrderRefund**/
            if (time() - 86400 * $order_refund_force_accept_day >= $item->create_time) {
                $order_refund = self::findOne($item->id);
                Yii::warning(print_r($order_refund->attributes, true));
                $order_refund->status = self::STATUS_ACCEPT;
                $order_refund->apply_time = time();
                $order_refund->save();
                // 发送消息给用户
                if (!empty(System::getConfig('order_refund_force_accept_user_message'))) {
                    $user_message = new UserMessage();
                    $user_message->uid = $order_refund->orderItem->order->uid;
                    $user_message->title = '系统消息';
                    $user_message->content = System::getConfig('order_refund_force_accept_user_message');
                    $user_message->status = UserMessage::STATUS_NEW;
                    $user_message->create_time = time();
                    $user_message->save();
                }
                // 发送消息给商家
                if (!empty(System::getConfig('order_refund_force_accept_merchant_message'))) {
                    $merchant_message = new MerchantMessage();
                    $merchant_message->mid = $order_refund->orderItem->order->shop->mid;
                    $merchant_message->title = '系统消息';
                    $merchant_message->content = System::getConfig('order_refund_force_accept_merchant_message');
                    $merchant_message->time = time();
                    $merchant_message->status = SystemMessage::STATUS_UNREAD;
                    $merchant_message->save();
                }
            }
        }
        return '商家自动同意申请退款完成：' . $date;
    }

    /**
     * 定时任务 同意后没有发货自动取消
     * @return string
     */
    public static function task_order_refund_force_delete()
    {
        return '不自动处理';
        $date = date('Ymd', time());
        $order_refund_force_delete_day = System::getConfig('order_refund_force_delete_day');
        Yii::warning('取消售后：' . $date, 'order_refund');
        $model = OrderRefund::find()->where(['status' => self::STATUS_ACCEPT])->all();
        foreach ($model as $item) { /** @var $item OrderRefund**/
            if (time() - 86400 * $order_refund_force_delete_day >= $item->apply_time) {
                $order_refund = self::findOne($item->id);
                Yii::warning(print_r($order_refund->attributes, true));
                $order_refund->status = self::STATUS_DELETE;
                $order_refund->delete_time = time();
                $order_refund->save();
                // 发送消息给用户
                if (!empty(System::getConfig('order_refund_force_delete_user_message'))) {
                    $user_message = new UserMessage();
                    $user_message->uid = $order_refund->orderItem->order->uid;
                    $user_message->title = '系统消息';
                    $user_message->content = System::getConfig('order_refund_force_delete_user_message');
                    $user_message->status = UserMessage::STATUS_NEW;
                    $user_message->create_time = time();
                    $user_message->save();
                }
            }
        }
        return '自动取消申请退款完成：' . $date;
    }

    /**
     * 定时任务 买家发货商家自动收货
     * @return string
     */
    public static function task_order_refund_force_receive()
    {
        $date = date('Ymd', time());
        $order_refund_force_receive_day = System::getConfig('order_refund_force_receive_day');
        Yii::warning('自动收货：' . $date, 'order_refund');
        $model = OrderRefund::find()->where(['status' => self::STATUS_SEND])->all();
        foreach ($model as $item) { /** @var $item OrderRefund**/
            if (time() - 86400 * $order_refund_force_receive_day >= $item->create_time) {
                $order_refund = self::findOne($item->id);
                Yii::warning(print_r($order_refund->attributes, true));
                $order_refund->status = self::STATUS_RECEIVE;
                $order_refund->receive_time = time();
                $order_refund->save();
                // 发消息给用户
                if (!empty(System::getConfig('order_refund_force_receive_user_message'))) {
                    $user_message = new UserMessage();
                    $user_message->uid = $order_refund->orderItem->order->uid;
                    $user_message->title = '系统消息';
                    $user_message->content = System::getConfig('order_refund_force_receive_user_message');
                    $user_message->status = UserMessage::STATUS_NEW;
                    $user_message->create_time = time();
                    $user_message->save();
                }
                // 发消息给商户
                if (!empty(System::getConfig('order_refund_force_receive_merchant_message'))) {
                    $merchant_message = new MerchantMessage();
                    $merchant_message->mid = $order_refund->orderItem->order->shop->mid;
                    $merchant_message->title = '系统消息';
                    $merchant_message->content = System::getConfig('order_refund_force_receive_merchant_message');
                    $merchant_message->time = time();
                    $merchant_message->status = SystemMessage::STATUS_UNREAD;
                    $merchant_message->save();
                }
//                $r = $order_refund->doRefund();
//                if ($r !== true) {
//                    return  $r;
//                }
//                $order_refund->complete_time = time();
//                $order_refund->status = OrderRefund::STATUS_COMPLETE; // 申请退款到支付接口默认退款成功，不再等待异步通知
//                $order_refund->save();
//                $order_refund->orderItem->order->status = Order::STATUS_COMPLETE;
//                $order_refund->orderItem->order->save(false);
//                OrderLog::info($order_refund->order->shop->merchant->id, OrderLog::U_TYPE_MERCHANT, $order_refund->orderItem->oid, '通过售后。', print_r($order_refund->attributes, true));
            }
        }
        return '商家自动收货完成：' . $date;
    }

    /**
     * 关联订单
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'oid']);
    }

    /**
     * 关联用户
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'uid']);
    }

    /**
     * 图片Url列表
     * @return array
     */
    public function getImageUrlList()
    {
        if (empty($this->image_list)) {
            return [];
        }
        $imageList = json_decode($this->image_list, true);
        $imageUrlList = [];
        foreach ($imageList as $image) {
            $imageUrlList[] = Util::fileUrl($image);
        }
        return $imageUrlList;
    }
}
