<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 财务记录
 * Class FinanceLog
 * @package app\models
 *
 * @property integer $id PK
 * @property string $trade_no 交易号
 * @property integer $type 类型
 * @property float $money 金额
 * @property integer $pay_method 支付方式
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 * @property integer $update_time 更新时间
 * @property string $remark 备注
 */
class FinanceLog extends ActiveRecord
{
    const TYPE_USER_RECHARGE = 1; // 用户充值
    const TYPE_ORDER_PAY = 2; // 订单支付
    const TYPE_MERCHANT_EARNEST_MONEY = 3; // 商户保证金
    const TYPE_AGENT_EARNEST_MONEY = 4; // 代理商保证金
    const TYPE_ORDER_REFUND = 5; // 订单售后退款
    const TYPE_ORDER_CANCEL = 6; // 订单取消退款
    const TYPE_USER_UPGRADE = 7; // 用户购买升级卡
    const TYPE_USER_PACKAGE = 8; // 用户购买套餐卡

    const PAY_METHOD_YHK = 11; // 银行卡
    const PAY_METHOD_WX_SCAN = 21; // 微信扫码
    const PAY_METHOD_WX_APP = 22; // 微信APP
    const PAY_METHOD_WX_MP = 23; // 微信公众号
    const PAY_METHOD_WX_H5 = 24; // 微信H5
    const PAY_METHOD_ZFB = 31; // 支付宝
    const PAY_METHOD_ZFB_APP = 32; // 支付宝APP
    const PAY_METHOD_ALLINPAY = 41; // 通联支付
    const PAY_METHOD_ALLINPAY_H5 = 42; // 通联H5支付
    const PAY_METHOD_ALLINPAY_ALI = 43; // 通联支付宝支付
    const PAY_METHOD_YE = 91; // 佣金
    const PAY_METHOD_COD = 99; // 货到付款

    const STATUS_WAIT = 1; // 待支付
    const STATUS_SUCCESS = 2; // 支付成功
    const STATUS_FAIL = 9; // 支付失败
    const STATUS_CLOSED = 0; // 支付取消或关闭

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['trade_no'], 'string', 'max' => 128],
            [['type', 'money', 'pay_method', 'status', 'create_time'], 'required'],
        ];
    }

    /**
     * 返回关联用户
     * @return User|null
     */
    public function getUser()
    {
        switch ($this->type) {
            case FinanceLog::TYPE_USER_RECHARGE:
                /** @var UserRecharge $recharge */
                $recharge = UserRecharge::find()->andWhere(['fid' => $this->id])->one();
                $user = $recharge->user;
                break;
            case FinanceLog::TYPE_ORDER_PAY:
                /** @var Order $order */
                $order = Order::find()->andWhere(['fid' => $this->id])->one();
                $user = $order->user;
                break;
            case FinanceLog::TYPE_MERCHANT_EARNEST_MONEY:
            case FinanceLog::TYPE_AGENT_EARNEST_MONEY:
                // TODO：商户支付没有关联用户
                $user = null;
                break;
            case FinanceLog::TYPE_ORDER_REFUND:
                /** @var OrderRefund $refund */
                $refund = OrderRefund::find()->andWhere(['fid' => $this->id])->one();
                $user = $refund->orderItem->order->user;
                break;
            case FinanceLog::TYPE_ORDER_CANCEL:
                /** @var Order $order */
                $order = Order::find()->andWhere(['cancel_fid' => $this->id])->one();
                $user = $order->user;
                break;
            default:
                $user = null;
        }
        return $user;
    }

    /**
     * 支付结果回调更新
     * @param string $trade_no 交易号
     * @param float $money 实际支付金额
     * @param integer $status 状态
     * @param string $raw 回调原始数据
     * @return boolean
     * @throws Exception
     */
    public static function payNotify($trade_no, $money, $status, $raw)
    {
        /** @var FinanceLog $finance_log */
        $finance_log = FinanceLog::find()->where(['trade_no' => $trade_no])->orderBy('id desc')->one();
        if (empty($finance_log)) {
            throw new Exception('找不到财务记录。');
        }
        if (Util::comp($finance_log->money, $money, 2) != 0) {
            throw new Exception('金额不匹配。');
        }
        if ($finance_log->status == $status) {
            return true;
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $finance_log->status = $status;
            $finance_log->update_time = time();
            $finance_log->remark = $raw;
            $r = $finance_log->save();
            if (!$r) {
                throw new Exception('无法更新财务记录。');
            }
            switch ($finance_log->type) {
                case FinanceLog::TYPE_USER_RECHARGE:
                    /** @var UserRecharge $recharge */
                    $recharge = UserRecharge::find()->where(['fid' => $finance_log->id])->one();
                    $r = $recharge->payNotify($status == FinanceLog::STATUS_SUCCESS, $finance_log->trade_no);
                    if (!$r) {
                        throw new Exception('无法更新充值状态。');
                    }
                    $recharge->backCommission($recharge->money);
                    break;
                case FinanceLog::TYPE_USER_UPGRADE:
                    /** @var UserBuyPack $buy */
                    $buy = UserBuyPack::find()->where(['fid' => $finance_log->id])->one();
                    $r = $buy->payNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新购买状态。');
                    }
                    break;
                case FinanceLog::TYPE_USER_PACKAGE:
                    /** @var UserBuyPack $buy */
                    $buy = UserBuyPack::find()->where(['fid' => $finance_log->id])->one();
                    $r = $buy->payNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新购买状态。');
                    }
                    break;
                case FinanceLog::TYPE_ORDER_PAY:
                    /** @var Order $order */
                    $order = Order::find()->where(['fid' => $finance_log->id])->one();
                    if (empty($order)) {
                        throw new Exception('没有找到关联订单：trade_no[' . $finance_log->trade_no . ']状态[' . KeyMap::getValue('finance_log_status', $finance_log->status) . ']。');
                    }
                    $r = $order->payNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新订单状态。');
                    }
                    break;
                case FinanceLog::TYPE_MERCHANT_EARNEST_MONEY:
                    /** @var Shop $shop */
                    $shop = Shop::find()->where(['earnest_money_fid' => $finance_log->id])->one();
                    $r = $shop->payNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新商户入驻申请状态。');
                    }
                    break;
                case FinanceLog::TYPE_AGENT_EARNEST_MONEY:
                    /** @var Agent $agent */
                    $agent = Agent::find()->where(['earnest_money_fid' => $finance_log->id])->one();
                    $r = $agent->payNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新商户入驻申请状态。');
                    }
                    break;
                case FinanceLog::TYPE_ORDER_REFUND: // 订单退款
                    /** @var OrderRefund $orderRefund */
                    $orderRefund = OrderRefund::find()->where(['fid' => $finance_log->id])->one();
                    $orderRefund->refundPayNotify($status == FinanceLog::STATUS_SUCCESS);
                    break;
                case FinanceLog::TYPE_ORDER_CANCEL: // 订单取消
                    /** @var Order $order */
                    $order = Order::find()->where(['cancel_fid' => $finance_log->id])->one();
                    $r = $order->cancelPayNotify($status == FinanceLog::STATUS_SUCCESS);
                    if (!$r) {
                        throw new Exception('无法更新订单状态。');
                    }
                    break;
                default:
                    throw new Exception('无法识别的财务记录类型。');
            }
            $trans->commit();
            return true;
        } catch (Exception $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * 刷新状态
     * @throws Exception
     */
    public function refreshStatus()
    {
        switch ($this->pay_method) {
            case FinanceLog::PAY_METHOD_YHK:
                $uid = 0;
                switch ($this->type) {
                    case FinanceLog::TYPE_USER_RECHARGE:
                        /** @var UserRecharge $recharge */
                        $recharge = UserRecharge::find()->andWhere(['fid' => $this->id])->one();
                        if (!empty($recharge)) {
                            $uid = $recharge->uid;
                        }
                        break;
                    case FinanceLog::TYPE_ORDER_PAY:
                        /** @var Order $order */
                        $order = Order::find()->andWhere(['fid' => $this->id])->one();
                        if (!empty($order)) {
                            $uid = $order->uid;
                        }
                        break;
                    case FinanceLog::TYPE_MERCHANT_EARNEST_MONEY:
                        /** @var Shop $shop */
                        $shop = Shop::find()->andWhere(['earnest_money_fid' => $this->id])->one();
                        if (!empty($shop)) {
                            $uid = MerchantConfig::getConfig($shop->mid, 'register_from_uid', 0);
                        }
                        break;
                    case FinanceLog::TYPE_AGENT_EARNEST_MONEY:
                    case FinanceLog::TYPE_ORDER_REFUND:
                    case FinanceLog::TYPE_ORDER_CANCEL:
                    default:
                }
                if ($uid > 0) {
                    $api = new PinganApi();
                    $result = $api->UnionAPI_OrderQuery($this->trade_no, Yii::$app->id . '_' . $uid);
                    if (empty(trim($result['errorCode']))) {
                        if ($result['status'] == '01' && $this->status != FinanceLog::STATUS_SUCCESS) {
                            return FinanceLog::payNotify($result['orderId'], $result['amount'], FinanceLog::STATUS_SUCCESS, json_encode($result));
                        }
                    } else {
                        throw new Exception($result['errorMsg']);
                    }
                }
                return false;
            case FinanceLog::PAY_METHOD_WX_SCAN:
            case FinanceLog::PAY_METHOD_WX_APP:
                $api = new WeixinAppApi();
                $result = $api->orderQuery($this->trade_no);
                if ($result['trade_state'] == 'SUCCESS') { // 支付成功
                    if ($this->status != FinanceLog::STATUS_SUCCESS) {
                        return FinanceLog::payNotify($this->trade_no, $this->money, FinanceLog::STATUS_SUCCESS, json_encode($result));
                    }
                    return true;
                } else {
                    throw new Exception($result['trade_state_desc']);
                }
            case FinanceLog::PAY_METHOD_WX_MP: // 微信公众号支付
                $api = new WeixinMpApi();
                $result = $api->orderQuery($this->trade_no);
                if ($result['trade_state'] == 'SUCCESS' && $this->status != FinanceLog::STATUS_SUCCESS) { // 支付成功
                    return FinanceLog::payNotify($this->trade_no, $this->money, FinanceLog::STATUS_SUCCESS, json_encode($result));
                } else {
                    throw new Exception($result['trade_state_desc']);
                }
            case FinanceLog::PAY_METHOD_WX_H5: // 微信H5支付
                $api = new WeixinH5Api();
                $result = $api->orderQuery($this->trade_no);
                if ($result['trade_state'] == 'SUCCESS' && $this->status != FinanceLog::STATUS_SUCCESS) { // 支付成功
                    return FinanceLog::payNotify($this->trade_no, $this->money, FinanceLog::STATUS_SUCCESS, json_encode($result));
                }
                return false;
            case FinanceLog::PAY_METHOD_ZFB:
                try {
                    $api = new AlipayApi();
                    $result = $api->AlipayTradeQuery($this->trade_no);
                    if ($result->code === '10000') {
                        if ($result->trade_status == 'TRADE_SUCCESS' && $this->status != FinanceLog::STATUS_SUCCESS) {
                            return FinanceLog::payNotify($result->out_trade_no, $result->total_amount, FinanceLog::STATUS_SUCCESS, json_encode($result));
                        }
                    } else {
                        throw new Exception($result->sub_msg);
                    }
                } catch (\Exception $e) {
                    throw new Exception($e->getMessage(), $e->getCode(), $e);
                }
                return false;
            case FinanceLog::PAY_METHOD_ZFB_APP:
                try {
                    $api = new AlipayApi();
                    $result = $api->AlipayTradeQuery($this->trade_no);
                    if ($result->code === '10000') {
                        if ($result->trade_status == 'TRADE_SUCCESS' && $this->status != FinanceLog::STATUS_SUCCESS) {
                            return FinanceLog::payNotify($result->out_trade_no, $result->total_amount, FinanceLog::STATUS_SUCCESS, json_encode($result));
                        }
                    } else {
                        throw new Exception($result->sub_msg);
                    }
                } catch (\Exception $e) {
                    throw new Exception($e->getMessage(), $e->getCode(), $e);
                }
                return false;
            case FinanceLog::PAY_METHOD_ALLINPAY:
                $api = new AllInPayApi();
                $result = $api->queryOne($this->trade_no, $this->create_time);
                if (!empty($result['ERRORCODE'])) {
                    throw new Exception($result['ERRORMSG']);
                } else {
                    if (empty($result['errorCode']) && $result['payResult'] == 1) {
                        if ($this->status != FinanceLog::STATUS_SUCCESS) {
                            return FinanceLog::payNotify($result['orderNo'], round($result['payAmount'] / 100, 2),  FinanceLog::STATUS_SUCCESS, json_encode($result));
                        }
                        return true;
                    } else {
                        throw new Exception($api->errorCodeMessage($result['errorCode']));
                    }
                }
                break;
            case FinanceLog::PAY_METHOD_ALLINPAY_H5:
                $api = new AllInPayH5Api();
                $json = $api->query($this->trade_no, $this->create_time);
                parse_str($json, $post);
                if ($post['payResult'] == 1) {
                    return FinanceLog::payNotify($post['orderNo'], round($post['payAmount'] / 100, 2), FinanceLog::STATUS_SUCCESS, json_encode($post));
                } else {
                    throw new Exception($post['ERRORMSG']);
                }
                break;
            case FinanceLog::PAY_METHOD_ALLINPAY_ALI:
                $api = new AllInPayAliApi();
                $result = $api->query($this->trade_no);
                if ($result['retcode'] == 'SUCCESS') {
                    if ($result['trxstatus'] == '0000') {
                        if ($this->status != FinanceLog::STATUS_SUCCESS) {
                            return FinanceLog::payNotify($result['reqsn'], round($result['trxamt'] / 100, 2),  FinanceLog::STATUS_SUCCESS, json_encode($result));
                        }
                        return true;
                    } else {
                        throw new Exception($result['errmsg']);
                    }
                } else {
                    throw new Exception($result['retmsg']);
                }
            case FinanceLog::PAY_METHOD_YE:
            case FinanceLog::PAY_METHOD_COD:
                return true;
            default:
                return false;
        }
        return false;
    }

    /**
     * 定时任务主动刷新状态
     * @param $id integer 支付编号
     * @return array
     */
    public static function task_auto_refresh($id)
    {
        Yii::warning('刷新财务状态[' . $id . ']', 'task');
        $finance = FinanceLog::findOne(['id' => $id]);
        if ($finance->status != FinanceLog::STATUS_WAIT) {
            Yii::warning('刷新财务状态时发现记录[' . $finance->id . ']状态为[' . $finance->status . ']。', 'task');
            return [
                '_del' => true, // 删除当前任务
            ];
        }
        try {
            $finance->refreshStatus();
        } catch (Exception $e) {
            Yii::error('刷新财务状态[' . $finance->id . ']时出现错误', 'task');
            Yii::error($e->getMessage(), 'task');
            Yii::error($e->getTraceAsString(), 'task');
        }
        // 计算下一次执行时间
        $last = time() - $finance->create_time;
        $timeList = [1, 2, 3, 5, 10, 30, 60, 180, 300, 600]; // 定时检测时间点
        $next = 0;
        foreach ($timeList as $time) {
            if ($last < $time * 60) {
                $next = $finance->create_time + $time * 60;
                break;
            }
        }
        if ($next == 0) {
            return [
                '_del' => true, // 删除当前任务
            ];
        }
        $task = new Task();
        $task->u_type = Task::U_TYPE_MANAGER;
        $task->uid = 1;
        $task->name = '主动刷新支付状态';
        $task->next = $next;
        $task->todo = json_encode([
            'class' => FinanceLog::class,
            'method'=>'task_auto_refresh',
            'params' => $finance->id
        ]);
        $task->status = Task::STATUS_WAITING;
        $task->save();
        return [
            '_del' => true, // 删除当前任务
        ];
    }
}
