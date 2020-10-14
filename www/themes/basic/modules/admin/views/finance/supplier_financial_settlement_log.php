<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\SupplierFinancialSettlementLog;
use app\models\Util;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $logList \app\models\SupplierFinancialSettlementLog[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '供货商结算记录';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_supplier" class="sr-only">供货商</label>
    <?php echo Html::textInput('search_supplier', Yii::$app->request->get('search_supplier'), ['id' => 'search_supplier', 'class' => 'form-control', 'placeholder' => '商户']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">日期</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '开始日期', 'style' => 'max-width:90px;']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '结束日期', 'style' => 'max-width:90px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'excel']);?>" class="btn btn-info btn-sm">导出</a>
</div>
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
        <th>供货商</th>
        <th>金额</th>
        <th>银行</th>
        <th>凭证</th>
        <th>创建时间</th>
        <th>是否已给发票</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($logList as $log) {?>
        <tr id="data_<?php echo $log->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $log->id;?>"/>
                    <span class="lbl"><?php echo $log->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($log->supplier->name);?></td>
            <td><?php echo $log->money;?></td>
            <td><?php echo nl2br(Html::encode($log->bank_info));?></td>
            <td><?php foreach ($log->getProofFileList() as $file) {echo Html::img(Util::fileUrl($file, false, '_100x100'));}?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($log->create_time);?></td>
            <td><?php echo ($log->is_bill == 1) ? '是' : '否';?></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo KeyMap::getValue('supplier_financial_settlement_log_status', $log->status);?></span></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    $log->status == SupplierFinancialSettlementLog::STATUS_SETTLE ? false : ['icon' => 'fa fa-pencil', 'btn_class' => 'btn btn-xs btn-success', 'color' => 'green', 'tip' => '修改', 'href' => Url::to(['/admin/finance/edit-supplier-financial-settlement-log', 'id' => $log->id])],
                    $log->status != SupplierFinancialSettlementLog::STATUS_WAIT ? false : ['icon' => 'fa fa-lock', 'btn_class' => 'btn btn-xs btn-warning', 'color' => 'yellow', 'tip' => '设置锁定', 'onclick' => 'setLock(' . $log->id . ')'],
                    $log->status != SupplierFinancialSettlementLog::STATUS_LOCK ? false : ['icon' => 'fa fa-check', 'btn_class' => 'btn btn-xs btn-warning', 'color' => 'yellow', 'tip' => '设置已付款', 'onclick' => 'setPay(' . $log->id . ')'],
                    $log->is_bill != 0 ? false : ['icon' => 'fa fa-check', 'btn_class' => 'btn btn-xs btn-success', 'color' => 'red', 'tip' => '设置已给发票', 'onclick' => 'setBill(' . $log->id . ')'],
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/finance/supplier-financial-settlement-log-view', 'id' => $log->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详细'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 设置结算记录为已锁定
     * @param id 结算记录编号
     */
    function setLock(id) {
        if (!confirm('确定要锁定此结算单吗？')) {
            return false;
        }
        //Api.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-lock']);?>', {id: id}, function (json) {
        $.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-lock']);?>', {id: id}, function (json) {
            layer.msg('设置成功。', function () {window.location.reload();});
        });
    }

    /**
     * 设置结算记录为已付款
     * @param id 结算记录编号
     */
    function setPay(id) {
        if (!confirm('设置已付款需要确保金额和凭证都已设置。\n是否确定设置此结算记录为已付款状态？')) {
            return false;
        }
        //Api.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-pay']);?>', {id: id}, function (json) {
        $.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-pay']);?>', {id: id}, function (json) {
            layer.msg('设置成功。', function () {window.location.reload();});
        });
    }

    /**
     * 设置结算记录为已开发票
     * @param id 结算记录编号
     */
    function setBill(id) {
        if (!confirm('设置已开发票。\n是否确定设置此结算记录为已开发票状态？')) {
            return false;
        }
        //Api.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-pay']);?>', {id: id}, function (json) {
        $.get('<?php echo Url::to(['/admin/finance/set-supplier-financial-settlement-log-bill']);?>', {id: id}, function (json) {
            layer.msg('设置成功。', function () {window.location.reload();});
        });
    }
</script>
