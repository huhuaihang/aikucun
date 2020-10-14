<?php

use app\assets\FileUploadAsset;
use app\assets\MaskedInputAsset;
use app\models\KeyMap;
use app\models\Util;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $log app\models\SupplierFinancialSettlementLog
 * @var $settlementList app\models\SupplierFinancialSettlement[]
 */

FileUploadAsset::register($this);
MaskedInputAsset::register($this);

$this->title = '供货商结算记录详情';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tbody>
    <tr>
        <th>供货商</th>
        <td><?php echo Html::encode($log->supplier->name);?></td>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $log->id;?></td>
    </tr>
    <tr>
        <th>金额</th>
        <td><?php echo $log->money;?></td>
    </tr>
    <tr>
        <th>银行信息</th>
        <td><pre><?php echo Html::encode($log->bank_info);?></pre></td>
    </tr>
    <tr>
        <th>打款证明</th>
        <td><?php foreach ($log->getProofFileList(true) as $image) {
                echo Html::a(Html::img($image, ['width' => 100]), $image);
            }?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($log->remark);?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('supplier_financial_settlement_log_status', $log->status);?></td>
    </tr>
    </tbody>
</table>
<?php echo $this->render('_supplier_financial_settlement_list', ['settlementList' => $settlementList]);?>
