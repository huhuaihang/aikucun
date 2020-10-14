<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\MerchantFinancialSettlement[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '商户结算单';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_merchant" class="sr-only">商户</label>
    <?php echo Html::textInput('search_merchant', Yii::$app->request->get('search_merchant'), ['id' => 'search_merchant', 'class' => 'form-control', 'placeholder' => '商户']);?>
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
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), ['' => '状态'] + KeyMap::getValues('merchant_financial_settlement_status'), ['class' => 'form-control']);?>
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
        <th>订单号</th>
        <th>订单金额</th>
        <th>退款金额</th>
        <th>商户实收金额</th>
        <th>服务费</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>结算时间</th>
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
            <td><?php echo Html::encode($model->merchant->username);?></td>
            <td><?php echo $model->order->no;?></td>
            <td><?php echo $model->order_money;?></td>
            <td><?php echo $model->refund_money;?></td>
            <td><?php echo $model->merchant_receive_money;?></td>
            <td><?php echo $model->charge;?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('merchant_financial_settlement_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->settle_time);?></td>
            <td><?php echo Html::encode($model->remark);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
