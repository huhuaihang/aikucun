<?php

use app\assets\TableAsset;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\BankReconciliationPingan[]
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '平安银行对账';
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
        <th>状态</th>
        <th>支付完成日期</th>
        <th>订单手续费</th>
        <th>商户号</th>
        <th>订单号</th>
        <th>币种</th>
        <th>订单金额</th>
        <th>款项描述</th>
        <th>支付时间</th>
        <th>订单有效期</th>
        <th>备注</th>
        <th>订单本金清算</th>
        <th>手续费清算</th>
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
            <td><?php echo $model->status;?></td>
            <td><?php echo $model->date;?></td>
            <td><?php echo $model->charge;?></td>
            <td><?php echo $model->masterId;?></td>
            <td><?php echo $model->orderId;?></td>
            <td><?php echo $model->currency;?></td>
            <td><?php echo $model->amount;?></td>
            <td><?php echo $model->objectName;?></td>
            <td><?php echo $model->paydate;?></td>
            <td><?php echo $model->validtime;?></td>
            <td><?php echo $model->remark;?></td>
            <td><?php echo $model->settleflg, '<br />', $model->settletime;?></td>
            <td><?php echo $model->chargeflg, '<br />', $model->chargetime;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
