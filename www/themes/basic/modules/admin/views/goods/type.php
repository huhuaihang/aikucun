<?php

use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\GoodsType[]
 */

TableAsset::register($this);

$this->title = '商品类型管理';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/goods/edit-type']);?>">添加</a>
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
        <th>名称</th>
        <th>属性数量</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($model->name);?></td>
            <td><?php echo $model->getAttrCount();?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-type', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-list', 'href' => Url::to(['/admin/goods/attr', 'search_tid' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '属性列表'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
