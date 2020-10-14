<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * 订单
 * Class Order
 * @package app\models
 *
 * @property integer $id PK
 * @property string $no 订单号
 * @property integer $uid 用户编号
 * @property integer $fid 财务记录编号
 * @property integer $type 类型 升级卡 套餐卡
 * @property string $pack_name 套餐卡名称
 * @property float $money 金额
 * @property integer $amount 数量
 * @property string $remark 备注
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 *
 * @property User $user 关联用户
 * @property FinanceLog $financeLog 关联财务记录
 */
class UserBuyPack extends ActiveRecord
{
    const STATUS_CREATED = 1;    // 已创建 待支付
    const STATUS_PAID = 2;       // 已支付
    const STATUS_PACKING = 3;    // 支付失败
    const STATUS_CANCEL = 9;     // 已取消
    const STATUS_DELETE = 0;     // 已删除

    const TYPE_UPGRADE = 1;      // 升级卡
    const TYPE_SET_MEAL = 2;     // 套餐卡

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['no'], 'string', 'max' => 128],
            [['uid', 'status', 'create_time'], 'required'],
            [['uid', 'status', 'create_time', 'type'], 'integer'],
            ['remark', 'string', 'max' => 512],
            [['money', 'amount'], 'default', 'value' => 0],
            [['money', 'amount'], 'compare', 'compareValue' => 0, 'operator' => '>='],
            ['type', 'default', 'value' => 1],
            [['uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['uid' => 'id']],
            [['pack_name'], 'safe']
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
            'amount' => '数量',
            'pack_name' => '套餐名称',
            'type' => '类型',
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
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->no = UserBuyPack::generateNo($this->uid);
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
     * @return UserBuyPack
     */
    public static function findByNo($order_no)
    {
        /** @var UserBuyPack $order */
        $order = UserBuyPack::find()->where(['no' => $order_no])->one();
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
            $this->status = UserBuyPack::STATUS_CREATED;
        } else {
            if ($this->status == UserBuyPack::STATUS_CREATED || $this->status == UserBuyPack::STATUS_CANCEL) {
                $this->status = UserBuyPack::STATUS_PAID;
            } else {
                Yii::warning($this->no . ' 订单支付结果更新时订单状态为：' . $this->status);
            }
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            $r = $this->save(false);
            if (!$r) {
                throw new Exception('无法保存订单状态。');
            } else {
                $user = $this->user;
                $user->prepare_count = $user->prepare_count + $this->amount;
                if ($this->type == UserBuyPack::TYPE_UPGRADE) {
                    $user->level_id = $user->level_id + 1;
                    $userLevelLog = new UserLevelLog();
                    $userLevelLog->level_id = $user->level_id;
                    $userLevelLog->uid = $user->id;
                    $userLevelLog->remark = '购买升级卡 ' . $this->amount . ' 个礼包 升级成' . UserLevel::findOne($user->level_id)->name;
                    if (!$userLevelLog->save()) {
                        throw new Exception('无法保存升级记录。');
                    }
                }
                $is_up = 0;
                if ($this->type == UserBuyPack::TYPE_SET_MEAL) {
                    //判断  是否  买了之后可以升级
                    if ($user->level_id == 1 && ($user->prepare_count + $user->getTeamActiveCount()) >= 21) {
                        $is_up = 1;
                        $user->level_id = 2;
                    }
                    if ($user->level_id == 2 && ($user->prepare_count + $user->getTeamActiveCount()) >= 100) {
                        $is_up = 1;
                        $user->level_id = 3;
                    }
                    if ($is_up == 1) {
                        $userLevelLog = new UserLevelLog();
                        $userLevelLog->level_id = $user->level_id;
                        $userLevelLog->uid = $user->id;
                        $userLevelLog->remark = '购买套餐卡 ' . $this->pack_name . ' 超过数量 等级升级成' . UserLevel::findOne($user->level_id)->name;
                        if (!$userLevelLog->save()) {
                            throw new Exception('无法保存升级记录。');
                        }
                    }
                }
                if (!$user->save(false)) {
                    throw new Exception('无法保存升级等级。');
                }
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
        }
        return true;
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
        $order_force_cancel_minute = System::getConfig('buy_package_force_cancel_minute');
        if ($order_force_cancel_minute <= 0) {
            return '没有设置自动取消订单的时间。';
        }
        $current_time = time();
        foreach (UserBuyPack::find()
                     ->andWhere(['status' => UserBuyPack::STATUS_CREATED])
                     ->each() as $order) {/** @var UserBuyPack $order */
            if ($order->create_time < $current_time - $order_force_cancel_minute * 60) {
                $order->status = UserBuyPack::STATUS_CANCEL;
                $order->save(false);

                Yii::warning('订单[' . $order->no . ']，创建时间[' . date('Y-m-d H:i:s', $order->create_time) . ']，自动设置订单取消。');
            }
        }
        return '订单自动取消任务执行完成。';
    }



}
