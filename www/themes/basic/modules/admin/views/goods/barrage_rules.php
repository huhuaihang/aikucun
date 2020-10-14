<?php

use app\assets\ApiAsset;
use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $rule_list \app\models\GoodsBarrageRules[]
 */

ApiAsset::register($this);
TableAsset::register($this);

$this->title = '商品弹幕规则管理';
$this->params['breadcrumbs'][] = '弹幕规则';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/goods/edit-barrage']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>id</th>
        <th>名称</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($rule_list as $barrage) {?>
        <tr id="data_<?php echo $barrage->id;?>">
            <td><?php echo $barrage->id;?></td>
            <td><?php echo Html::encode($barrage->title);?></td>
<!--            <td>--><?php //echo Html::encode($service->desc);?><!--</td>-->
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-barrage', 'id' => $barrage->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsBarrage(' . $barrage->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>

<script>
    /**
     * 删除商品服务
     * @param id 商品服务编号
     */
    function deleteGoodsBarrage(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/goods/delete-barrage']);?>', {'id': id}, function (json) {
            $('#data_' + id).remove();
        });
    }
</script>
