<?php

use app\assets\FileUploadAsset;
use app\assets\MaskedInputAsset;
use app\models\Util;
use app\widgets\FileUploadWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $log app\models\SupplierFinancialSettlementLog
 * @var $settlementList app\models\SupplierFinancialSettlement[]
 */

FileUploadAsset::register($this);
MaskedInputAsset::register($this);

$this->title = '修改供货商结算记录';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($log, 'id');?>
<?php echo $form->field($log, 'money');?>
<?php echo $form->field($log, 'bank_info')->textarea(['rows' => '7']);?>
<?php echo $form->field($log, 'proof_file')
    ->hint((function ($list) {
        array_walk($list, function (&$item) {
            $item = Html::img(Util::fileUrl($item, false, '_100x100'));
        });
        return implode('', $list) . ' ';
    })($log->getProofFileList()))
    ->widget(FileUploadWidget::class, ['url'=>Url::to(['/admin/finance/upload', 'dir'=>'finance']), 'callback'=>'uploadCallback']);?>
<script>
    function uploadCallback(uri, url) {
        console.log(uri);
        console.log(url);

        var $proof_file = $('[name="SupplierFinancialSettlementLog[proof_file]"]');
        var value = $proof_file.val();
        if (value == '') {
            value = '[]';
        }
        var list = JSON.parse(value);
        list.push(uri);
        $proof_file.val(JSON.stringify(list));
        //$('.field-supplierfinancialsettlementlog-proof_file .hint-block').append('<img src="' + url + '" width="100" />');
        $('.field-supplierfinancialsettlementlog-proof_file .hint-block').append('<img src="/uploads/' + uri + '" width="100" />');
    }
</script>
<?php echo $form->field($log, 'remark')->textarea();?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button type="submit" class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<?php echo $this->render('_supplier_financial_settlement_list', ['settlementList' => $settlementList]);?>
