<?php

use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $search_tid integer 当前显示的属性所属分类编号
 * @var $model_list \app\models\GoodsAttr[]
 * @var $tid_map array 可选类型
 */

TableAsset::register($this);

$this->title = '商品属性管理';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_tid" class="control-label">当前类型</label>
    <?php echo Html::dropDownList('search_tid', $search_tid, $tid_map, ['id' => 'search_tid', 'class' => 'form-control', 'onchange' => '$(this).parent().parent().submit()']);?>
</div>
<br />
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/goods/edit-attr', 'tid' => $search_tid]);?>">添加</a>
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
        <th>商品类型</th>
        <th>可选值列表</th>
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
            <td<?php if ($model->is_sku == 1) {echo ' style="font-weight:bold;"';}?>><?php echo Html::encode($model->name);?></td>
            <td><?php echo Html::encode($model->goodsType->name);?></td>
            <td><?php echo implode(',', $model->getValuesList());?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-attr', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
