<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Supplier;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Supplier[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '供货商列表';
$this->params['breadcrumbs'][] = '供货商管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_status" class="sr-only">手机号</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/supplier/edit']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>编号</th>
        <th>公司名称</th>
        <th>手机号</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr class="data_<?php echo $model->id;?>">
            <td><?php echo $model->id;?></td>
            <td><?php echo Html::encode($model->name);?></td>
            <td><?php echo Html::encode($model->mobile);?></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo KeyMap::getValue('supplier_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/supplier/edit', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改'],
                    $model->status != Supplier::STATUS_STOP ?: ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    $model->status != Supplier::STATUS_OK ?: ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script >
    /**
     * 设置供货商状态
     */
    function toggleStatus(id) {
        //Api.get('<?php echo Url::to(['/admin/supplier/status']);?>', {'id':id}, function(json) {
        $.get('<?php echo Url::to(['/admin/supplier/status']);?>', {'id':id}, function(json) {
            window.location.reload();
        });
    }
</script>