<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\LinkPager;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $settlementList \app\models\SupplierFinancialSettlement[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '供货商结算单';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_supplier" class="sr-only">供货商</label>
    <?php echo Html::textInput('search_supplier', Yii::$app->request->get('search_supplier'), ['id' => 'search_supplier', 'class' => 'form-control', 'placeholder' => '供货商']);?>
</div>
<div class="form-group">
    <label for="search_order_no" class="sr-only">订单号</label>
    <?php echo Html::textInput('search_order_no', Yii::$app->request->get('search_order_no'), ['id' => 'search_order_no', 'class' => 'form-control', 'placeholder' => '订单号']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">日期</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '开始日期', 'style' => 'max-width:90px;']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'class' => 'form-control masked', 'data-mask' => '9999-99-99', 'placeholder' => '结束日期', 'style' => 'max-width:90px;']);?>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">状态</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), ['' => '状态'] + KeyMap::getValues('supplier_financial_settlement_status'), ['class' => 'form-control']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<?php echo Html::endForm();?>
<?php echo $this->render('_supplier_financial_settlement_list', ['settlementList' => $settlementList]);?>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
