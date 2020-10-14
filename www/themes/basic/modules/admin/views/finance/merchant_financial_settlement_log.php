<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\MerchantFinancialSettlementLog;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\MerchantFinancialSettlementLog[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '商户结算记录';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_merchant" class="sr-only">商户</label>
    <?php echo Html::textInput('search_merchant', Yii::$app->request->get('search_merchant'), ['id' => 'search_merchant', 'class' => 'form-control', 'placeholder' => '商户']);?>
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
        <th>商户</th>
        <th>金额</th>
        <th>银行</th>
        <th>凭证</th>
        <th>创建时间</th>
        <th>状态</th>
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
            <td><?php echo Html::encode($model->merchant->username);?></td>
            <td><?php echo $model->money;?></td>
            <td><?php echo nl2br(Html::encode($model->bank_info));?></td>
            <td><?php echo Html::img(Yii::$app->params['upload_url'] . $model->proof_file, ['width' => 100]);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('merchant_financial_settlement_log_status', $model->status);?></span></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    $model->status != MerchantFinancialSettlementLog::STATUS_WAIT ? false : ['icon' => 'fa fa-pencil', 'btn_class' => 'btn btn-xs btn-success', 'color' => 'green', 'tip' => '修改', 'href' => Url::to(['/admin/finance/edit-merchant-financial-settlement-log', 'id' => $model->id])],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
