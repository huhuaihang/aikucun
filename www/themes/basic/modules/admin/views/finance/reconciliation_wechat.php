<?php

use app\assets\TableAsset;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\BankReconciliationWeixin[]
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '微信对账';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>交易时间</th>
        <th>公众账号ID<br />用户标识</th>
        <th>商户号<br />子商户号<br />设备号</th>
        <th>微信订单号<br />商户订单号</th>
        <th>交易类型</th>
        <th>交易状态</th>
        <th>付款银行<br />货币种类</th>
        <th>总金额<br />企业红包金额（元）</th>
        <th>微信退款单号<br />商户退款单号</th>
        <th>退款金额（元）<br />企业红包退款金额（元）</th>
        <th>退款类型<br />退款状态</th>
        <th>商品名称</th>
        <th>商户数据包</th>
        <th>手续费（元）<br />费率</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->trade_time);?></td>
            <td><?php echo $model->app_id, '<br />', $model->user_open_id;?></td>
            <td><?php echo $model->mch_id, '<br />', $model->sub_mch_id, '<br />', $model->client_no;?></td>
            <td><?php echo $model->weixin_trade_id, '<br />', $model->out_trade_no;?></td>
            <td><?php echo $model->trade_type;?></td>
            <td><?php echo $model->trade_status;?></td>
            <td><?php echo $model->pay_bank, '<br />', $model->currency;?></td>
            <td><?php echo $model->order_amount, '<br />', $model->merchant_red;?></td>
            <td><?php echo $model->refund_trade_id, '<br />', $model->refund_out_trade_no;?></td>
            <td><?php echo $model->refund_amount, '<br />', $model->refund_merchant_red;?></td>
            <td><?php echo $model->refund_type, '<br />', $model->refund_status;?></td>
            <td><?php echo $model->subject;?></td>
            <td><?php echo $model->merchant_data;?></td>
            <td><?php echo $model->charge, '<br />', $model->charge_ratio;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
