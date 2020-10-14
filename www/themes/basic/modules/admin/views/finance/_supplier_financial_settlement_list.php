<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use app\models\SupplierFinancialSettlement;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $settlementList \app\models\SupplierFinancialSettlement[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'excel']);?>" class="btn btn-info btn-sm">导出</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">编号</th>
        <th>供货商</th>
        <th>订单</th>
        <th>商品</th>
        <th>结算单价</th>
        <th>结算数量</th>
        <th>结算金额</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>结算时间</th>
        <th>备注</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($settlementList as $settlement) {?>
        <tr id="data_<?php echo $settlement->id;?>">
            <td class="center"><?php echo $settlement->id;?></td>
            <td><?php echo Html::encode($settlement->supplier->name);?></td>
            <td><?php echo Html::a($settlement->order->no, ['/admin/order/view', 'order_no' => $settlement->order->no]);?></td>
            <td><?php echo Html::a(Html::encode($settlement->orderItem->title) . '<br />' . Html::encode($settlement->orderItem->sku_key_name), ['/admin/goods/view', 'id' => $settlement->gid]);?></td>
            <td><?php echo $settlement->price;?></td>
            <td><?php echo $settlement->amount;?></td>
            <td><?php echo $settlement->money;?></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo KeyMap::getValue('supplier_financial_settlement_status', $settlement->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($settlement->create_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($settlement->settle_time);?></td>
            <td><?php echo Html::encode($settlement->remark);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    $settlement->status != SupplierFinancialSettlement::STATUS_MONEY_FIXED ?: ['icon' => 'fa fa-check', 'onclick' => 'addToLog(' . $settlement->id . ')', 'btn_class' => 'btn btn-xs btn-info', 'tip' => '添加到结算单', 'color' => 'blue'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script>
    /**
     * 添加到结算记录
     * @param id 供货商结算编号
     */
    function addToLog(id) {
        //Api.get('<?php echo Url::to(['/admin/finance/supplier-financial-settlement-add-to-log']);?>', {id: id}, function (json) {
        $.get('<?php echo Url::to(['/admin/finance/supplier-financial-settlement-add-to-log']);?>', {id: id}, function (json) {
            if (callback(json)) {
                layer.msg('完成。', function () {
                    window.location.reload();
                });
            }
        });
    }
</script>
