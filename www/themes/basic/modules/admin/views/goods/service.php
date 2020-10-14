<?php

use app\assets\ApiAsset;
use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $serviceList \app\models\GoodsService[]
 */

ApiAsset::register($this);
TableAsset::register($this);

$this->title = '商品服务管理';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/goods/edit-service']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>名称</th>
        <th>描述</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($serviceList as $service) {?>
        <tr id="data_<?php echo $service->id;?>">
            <td><?php echo Html::encode($service->name);?></td>
            <td><?php echo Html::encode($service->desc);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-service', 'id' => $service->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsService(' . $service->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>

<script>
    /**
     * 删除商品服务
     * @param id 商品服务编号
     */
    function deleteGoodsService(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        Api.get('<?php echo Url::to(['/admin/goods/delete-service']);?>', {'id': id}, function (json) {
            $('#data_' + id).remove();
        });
    }
</script>
