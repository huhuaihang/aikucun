<?php

use app\assets\TableAsset;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\BankReconciliationAlipay[]
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '支付宝对账';
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
        <th>支付宝交易号<br />商户订单号</th>
        <th>业务类型</th>
        <th>商品名称</th>
        <th>创建时间<br />完成时间</th>
        <th>门店编号<br />门店名称<br />操作员<br />终端号</th>
        <th>对方账户</th>
        <th>订单金额<br />商家实收</th>
        <th>支付宝红包<br />集分宝<br />支付宝优惠<br />商家优惠</th>
        <th>券核销</th>
        <th>商家红包<br />卡消费金额</th>
        <th>退款单号</th>
        <th>服务费<br />分润</th>
        <th>备注</th>
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
            <td><?php echo $model->alipay_trade_id, '<br />', $model->out_trade_no;?></td>
            <td><?php echo $model->biz_type;?></td>
            <td><?php echo $model->subject;?></td>
            <td><?php echo $model->create_time, '<br />', $model->finish_time;?></td>
            <td><?php echo $model->shop_no, '<br />', $model->shop_name, '<br />', $model->shop_user, '<br />', $model->shop_term_no;?></td>
            <td><?php echo $model->user_account;?></td>
            <td><?php echo $model->total_amount, '<br />', $model->merchant_receive_amount;?></td>
            <td><?php echo $model->alipay_red, '<br />', $model->alipay_score, '<br />', $model->alipay_preference, '<br />', $model->merchant_preference;?></td>
            <td><?php echo $model->coupon_name, '<br />', $model->coupon_amount;?></td>
            <td><?php echo $model->merchant_red, '<br />', $model->card_amount;?></td>
            <td><?php echo $model->refund_trade_no;?></td>
            <td><?php echo $model->charge, '<br />', $model->commission;?></td>
            <td><?php echo $model->remark;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
