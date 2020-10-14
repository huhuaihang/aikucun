<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\FinanceLog[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '财务列表';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_trade_no" class="sr-only">交易号</label>
    <?php echo Html::textInput('search_trade_no', Yii::$app->request->get('search_trade_no'), ['id' => 'search_trade_no', 'class' => 'form-control', 'placeholder' => '交易号', 'style' => 'max-width:200px;']);?>
</div>
<div class="form-group">
    <label for="search_type" class="sr-only">类型</label>
    <?php echo Html::dropDownList('search_type', Yii::$app->request->get('search_type'), KeyMap::getValues('finance_log_type'), ['prompt' => '交易类型', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_pay_method" class="sr-only">支付方式</label>
    <?php echo Html::dropDownList('search_pay_method', Yii::$app->request->get('search_pay_method'), KeyMap::getValues('finance_log_pay_method'), ['prompt' => '支付方式', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">状态</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('finance_log_status'), ['prompt' => '状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">时间</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'placeholder' => '开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'placeholder' => '结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br>
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
        <th>用户</th>
<!--        <th>交易号</th>-->
        <th>类型</th>
        <th>金额</th>
        <th>支付方式</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>更新时间</th>
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
<!--            <td>--><?php //$user = $model->getUser();if (empty($user)) {echo '<i>没有找到关联用户</i>';} else {echo Html::a(Html::encode($user->nickname) . '<br />' . $user->mobile, ['/admin/user/view', 'id' => $user->id]);}?><!--</td>-->
            <td><?php echo $model->trade_no;?></td>
            <td><?php echo KeyMap::getValue('finance_log_type', $model->type);?></td>
            <td><?php echo $model->money;?></td>
            <td><?php echo KeyMap::getValue('finance_log_pay_method', $model->pay_method);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('finance_log_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->update_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-refresh', 'onclick' => 'refreshStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-info', 'tip' => '刷新状态', 'color' => 'blue'],
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/finance/view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '详情', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    function refreshStatus(id) {
        $.getJSON('<?php echo Url::to(['/admin/finance/refresh-status']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                layer.msg('状态已刷新。', function () {window.location.reload();});
            }
        });
    }
</script>