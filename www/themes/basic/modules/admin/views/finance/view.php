<?php

use app\assets\TableAsset;
use app\models\FinanceLog;
use app\models\KeyMap;
use app\models\Order;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $finance \app\models\FinanceLog
 */

TableAsset::register($this);

$this->title = '财务详情';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tbody>
    <tr>
        <th>编号</th>
        <td><?php echo $finance->id;?></td>
    </tr>
    <tr>
        <th>交易号</th>
        <td><?php echo $finance->trade_no;?></td>
    </tr>
    <tr>
        <th>类型</th>
        <td><?php echo KeyMap::getValue('finance_log_type', $finance->type);
            echo '<br />';
            switch ($finance->type) {
                case FinanceLog::TYPE_USER_RECHARGE:
                    break;
                case FinanceLog::TYPE_ORDER_PAY:
                    /** @var Order $order */
                    $order = Order::find()->andWhere(['fid' => $finance->id])->one();
                    if (empty($order)) {
                        echo '<br /><i>没有找到关联订单</i>';
                    } else {
                        echo Html::a('订单详情', ['/admin/order/view', 'order_no' => $order->no], ['class' => 'btn btn-sm btn-success']);
                    }
                    break;
                case FinanceLog::TYPE_MERCHANT_EARNEST_MONEY:
                case FinanceLog::TYPE_AGENT_EARNEST_MONEY:
                case FinanceLog::TYPE_ORDER_REFUND:
                case FinanceLog::TYPE_ORDER_CANCEL:
                default:
            }?></td>
    </tr>
    <tr>
        <th>支付金额</th>
        <td><?php echo $finance->money;?></td>
    </tr>
    <tr>
        <th>支付方式</th>
        <td><?php echo KeyMap::getValue('finance_log_pay_method', $finance->pay_method);?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('finance_log_status', $finance->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($finance->create_time);?></td>
    </tr>
    <tr>
        <th>更新时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($finance->update_time);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td style="word-break:break-all;"><?php echo $finance->remark;?></td>
    </tr>
    </tbody>
</table>