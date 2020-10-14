<?php

use app\assets\TableAsset;
use app\models\City;
use app\models\FinanceLog;
use app\models\KeyMap;
use app\models\OrderDeliver;
use app\models\OrderDeliverItem;
use app\models\OrderItem;
use app\models\OrderLog;
use app\models\OrderRefund;
use app\models\System;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * @var $this \yii\web\View
 * @var $order \app\models\Order
 */

TableAsset::register($this);

$this->title = '订单详情';
$this->params['breadcrumbs'][] = '订单管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="4">基本信息</th>
    </tr>
    <tr>
        <th>订单号</th>
        <td><?php echo $order->no;?></td>
        <th>订单状态</th>
        <td><?php echo  $order->status. Keymap::getValue('order_status', $order->status);?></td>
    </tr>
    <tr>
        <th>用户</th>
        <td><?php echo Html::encode($order->user->nickname);?></td>
        <th>下单时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($order->create_time);?></td>
    </tr>
    <?php if (!empty($order->fid)) {
        $finance_log = $order->financeLog;?>
        <tr>
            <th>支付方式</th>
            <td><?php echo KeyMap::getvalue('finance_log_pay_method', $finance_log->pay_method);?></td>
            <th>付款时间</th>
            <td><?php if ($finance_log->status != FinanceLog::STATUS_SUCCESS) {
                    echo '未付款';
                } else {
                    echo Yii::$app->formatter->asDatetime($finance_log->update_time);
                }?></td>
        </tr>
        <tr>
            <th>交易号</th>
            <td><?php echo $finance_log->trade_no;?></td>
            <th>交易状态</th>
            <td><?php echo KeyMap::getValue('finance_log_status', $finance_log->status);?></td>
        </tr>
    <?php }?>
    <?php if (!empty($order->cancel_fid)) {
        $cancel_finance_log = $order->cancelFinanceLog;?>
        <tr>
            <th>退款支付方式</th>
            <td><?php echo KeyMap::getvalue('finance_log_pay_method', $cancel_finance_log->pay_method);?></td>
            <th>付款时间</th>
            <td><?php if ($cancel_finance_log->status != FinanceLog::STATUS_SUCCESS) {
                    echo '未付款';
                } else {
                    echo Yii::$app->formatter->asDatetime($cancel_finance_log->update_time);
                }?></td>
        </tr>
        <tr>
            <th>退款交易号</th>
            <td><?php echo $cancel_finance_log->trade_no;?></td>
            <th>交易状态</th>
            <td><?php echo KeyMap::getValue('finance_log_status', $cancel_finance_log->status);?></td>
        </tr>
    <?php }?>
    <tr>
        <th colspan="4">收货信息</th>
    </tr>
    <tr>
        <th>收货人</th>
        <td><?php echo Html::encode($order->getDeliverInfoJson('name')), '<br />';
            echo $order->getDeliverInfoJson('mobile');?></td>
        <th>地区</th>
        <td><?php $city = City::findByCode($order->getDeliverInfoJson('area'));
            echo implode(' ', $city->address());?></td>
    </tr>
    <tr>
        <th>详细地址</th>
        <td colspan="3"><?php echo Html::encode($order->getDeliverInfoJson('address'));?></td>
    </tr>
    <?php if (!empty($order->user_remark)) {?>
        <tr>
            <th>买家留言</th>
            <td colspan="3"><?php echo Html::encode($order->user_remark);?></td>
        </tr>
    <?php }?>
    <?php if (!empty($order->merchant_remark)) {?>
        <tr>
            <th>卖家备注</th>
            <td colspan="3"><?php echo Html::encode($order->merchant_remark);?></td>
        </tr>
    <?php }?>
    <tr>
        <th colspan="4">商品信息</th>
    </tr>
    <tr>
        <td colspan="4">
            <table class="table">
                <thead>
                <tr>
                    <th>商品名称</th>
                    <th>规格</th>
                    <th>数量</th>
                    <th>单价</th>
                    <th>金额</th>
                    <?php if (!empty($order->discount_money)) {?>
                        <th>限时抢购优惠金额</th>
                    <?php }?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order->itemList as $item) {?>
                    <tr>
                        <td><?php echo Html::encode($item->title);?></td>
                        <td><?php echo Html::encode(str_replace('_', ' ', $item->sku_key_name));?></td>
                        <td><?php echo $item->amount;?></td>
                        <td><?php echo $item->price;?></td>
                        <td><?php echo number_format($item->price * $item->amount, 2);?></td>
                        <?php if (!empty($order->discount_money)) {?>
                            <td><?php echo $item->mark_money;?></td>
                        <?php }?>
                    </tr>
                    <?php if(!empty($order->gift_id)){?>
                        <tr>
                            <td ><font style="color: #ff2222">【赠品】</font><?php echo Html::encode($order->gift->name);?></td>
                            <td></td>
                            <td>1</td>
                            <td><?php echo $order->gift->price;?></td>
                            <td>0</td>

                        </tr>
                    <?php }?>
                <?php }?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <th>合计</th>
                    <th><?php echo $order->goods_money;?></th>
                    <?php if (!empty($order->discount_money)) {?>
                    <td>
                     【限时抢购优惠： <?php echo $order->discount_money;?>】
                    </td>
                    <?php }?>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <?php $item_list = OrderItem::find()->where(['oid' => $order->id])->all();
    $oiid = ArrayHelper::getColumn($item_list, 'id');
    $refund = OrderRefund::find()->where(['id' => $oiid])->all();
    if (!empty($refund)) {?>
        <tr>
            <th colspan="4">售后信息</th>
        </tr>
        <tr>
            <td colspan="4">
                <table class="table">
                    <thead>
                    <tr>
                        <th>商品名称</th>
                        <th>规格</th>
                        <th>退货数量</th>
                        <th>金额</th>
                        <th>退货原因</th>
                        <td>退货上传图片</td>
                        <td>退货状态</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($order->itemList as $item) {
                        if (empty($item->orderRefund)) {
                            continue;
                        }
                        $order_refund = OrderRefund::findOne($item->orderRefund->id);?>
                        <tr>
                            <td><?php echo Html::encode($order_refund->orderItem->title);?></td>
                            <td><?php echo Html::encode(str_replace('_', ' ', $order_refund->orderItem->sku_key_name));?></td>
                            <td><?php echo $order_refund->amount;?></td>
                            <td><?php echo $order_refund->money;?></td>
                            <td><?php echo Html::encode($order_refund->reason);?></td>
                            <td>
                                <?php foreach (JSON::decode($order_refund->image_list) as $val) { ?>
                                    <img src="<?php echo Yii::$app->params['upload_url']. $val?>" width="100">
                                <?php } ?>
                            </td>
                            <td><?php echo KeyMap::getValue('order_refund_status', $order_refund->status)?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php }?>
    <tr>
        <th colspan="4">费用信息</th>
    </tr>
    <tr>
        <td colspan="4" align="right">
            商品总金额：<?php echo $order->goods_money;?>+
            配送费用：<?php echo $order->deliver_fee;?>
            <?php if ($order->is_score == 1) {?> -
                积分兑换订单：消耗<?php echo $order->score;?>积分 抵扣 <?php echo round($order->score * System::getConfig('score_ratio') / 100, 2);?>元
            <?php }?>
            <?php if (!empty($order->self_buy_money)) {?>
            -自购省： <?php echo $order->self_buy_money;?>
            <?php }?>
            <?php if (!empty($order->coupon_money)) {?>
                -优惠券： <?php echo $order->coupon_money;?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <td colspan="4" align="right">= 订单总金额：<?php echo $order->amount_money;?></td>
    </tr>
    <tr>
        <td colspan="4" align="right"><?php echo Html::encode($order->merchant_remark);?></td>
    </tr>
    <?php $deliver_list = OrderDeliver::find()->andWhere(['oid' => $order->id])->all(); /** @var OrderDeliver[] $deliver_list */?>
    <?php if (!empty($deliver_list)) {?>
        <tr>
            <th colspan="4">发货单</th>
        </tr>
        <tr>
            <td colspan="4">
                <table class="table">
                    <thead>
                    <tr>
                        <th>编号</th>
                        <th>内容</th>
                        <th>状态</th>
                        <th>创建时间</th>
                        <th>物流</th>
                        <th>快递单号</th>
                        <th>发货时间</th>
                        <th>物流跟踪</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($deliver_list as $deliver) {?>
                        <tr>
                            <td><?php echo $deliver->id;?></td>
                            <td>
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>商品</th>
                                        <th>规格</th>
                                        <th>数量</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($deliver->itemList as $item) { /** @var OrderDeliverItem $item **/?>
                                        <tr>
                                            <td><?php echo Html::encode($item->orderItem->title);?></td>
                                            <td><?php echo Html::encode($item->orderItem->sku_key_name);?></td>
                                            <td><?php echo $item->amount;?></td>
                                        </tr>
                                    <?php }?>
                                    </tbody>
                                </table>
                            </td>
                            <td><span class="label label-default"><?php echo KeyMap::getValue('order_deliver_status', $deliver->status);?></span></td>
                            <td><?php echo Yii::$app->formatter->asDatetime($deliver->create_time);?></td>
                            <td><?php if (!empty($deliver->eid)) {echo Html::encode($deliver->express->name);}?></td>
                            <td><?php echo Html::encode($deliver->no);?></td>
                            <td><?php if (!empty($deliver->send_time)) {echo Yii::$app->formatter->asDatetime($deliver->send_time);}?></td>
                            <td><?php if (!empty($deliver->trace)) {
                                    $trace_list = json_decode($deliver->trace, true);
                                    if (is_array($trace_list)) {
                                        foreach ($trace_list as $trace) {
                                            echo $trace['ftime'] . chr(10);
                                            echo $trace['context'] . "<br>";
                                        }
                                    }
                                }?></td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
            </td>
        </tr>
    <?php }?>
    <tr>
        <th colspan="4">操作</th>
    </tr>
    <tr>
        <td colspan="4">
            <table class="table">
                <thead>
                <tr>
                    <th>编号</th>
                    <th>用户</th>
                    <th>时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach (OrderLog::find()->andWhere(['oid' => $order->id])->orderBy('time ASC')->each() as $log) {?>
                    <tr>
                        <td><?php echo $log->id;?></td>
                        <td><?php echo KeyMap::getValue('order_log_u_type', $log->u_type);?></td>
                        <td><?php echo Yii::$app->formatter->asDatetime($log->time);?></td>
                        <td><?php echo Html::encode($log->content);?></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </td>
    </tr>
</table>
