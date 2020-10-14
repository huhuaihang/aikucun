<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\ManagerRole[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '管理角色列表';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
    <div class="form-group">
        <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/rbac/edit-role']);?>">添加</a>
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
            <th>角色名称</th>
            <th>描述</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($model_list as $model) {?>
            <tr id="data_<?php echo $model->id;?>">
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $model->id;?>" /><span class="lbl"><?php echo $model->id;?></span></label></td>
                <td><?php echo Html::encode($model->name);?></td>
                <td><?php echo Html::encode($model->remark);?></td>
                <td><?php echo ManagerTableOp::widget(['items'=>[
                    ['icon'=>'fa fa-pencil', 'href'=>Url::to(['/admin/rbac/edit-role', 'id'=>$model->id]), 'btn_class'=>'btn btn-xs btn-success', 'tip'=>'修改', 'color'=>'green'],
                    ['icon'=>'fa fa-trash', 'onclick'=>'deleteManagerRole(' . $model->id . ')', 'btn_class'=>'btn btn-xs btn-danger', 'tip'=>'删除', 'color'=>'red'],
                ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<script>
/**
 * 删除管理员角色
 */
function deleteManagerRole(id) {
    if (!confirm('确定要删除吗？')) {
        return false;
    }
    $.getJSON('<?php echo Url::to(['/admin/rbac/delete-role']);?>', {'id':id}, function(json) {
        if (callback(json)) {
            $('#data_' + id).remove();
        }
    });
}
</script>
