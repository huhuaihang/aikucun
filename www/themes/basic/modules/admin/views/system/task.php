<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Task;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\Task[]
 * @var $pagination yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '定时任务';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-sm">搜索</button>
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
            <th>名称</th>
            <th>下次执行时间</th>
            <th>定时</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($model_list as $model) {?>
            <tr id="data_<?php echo $model->id;?>">
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $model->id;?>" /><span class="lbl"><?php echo $model->id;?></span></label></td>
                <td><?php echo KeyMap::getValue('task_u_type', $model->u_type) . '：(', $model->uid, ')';?></td>
                <td><?php echo Html::encode($model->name);?></td>
                <td><button type="button" class="btn btn-warning btn-xs" title="点击设置为立即执行" onclick="clearTaskNext(<?php echo $model->id;?>)"><?php echo Yii::$app->formatter->asDatetime($model->next);?></button></td>
                <td><?php echo Html::encode($model->cron);?></td>
                <td><button type="button" class="btn btn-xs btn-<?php echo [Task::STATUS_WAITING=>'success', Task::STATUS_DOING=>'primary', Task::STATUS_FINISHED=>'info', Task::STATUS_PAUSED=>'default'][$model->status];?>" title="点击重置状态" onclick="resetTaskStatus(<?php echo $model->id;?>)"><?php echo KeyMap::getValue('task_status', $model->status);?></button></td>
                <td><?php echo ManagerTableOp::widget(['items'=>[
                    ['rbac'=>'system/task', 'icon'=>'fa fa-info-circle', 'href'=>Url::to(['/admin/system/task-view', 'id'=>$model->id]), 'btn_class'=>'btn btn-default btn-xs', 'tip'=>'详情'],
                    ['rbac'=>'system/task', 'icon'=>'fa fa-trash', 'onclick'=>'deleteTask(' . $model->id . ')', 'btn_class'=>'btn btn-xs btn-danger', 'tip'=>'删除', 'color'=>'red'],
                ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination'=>$pagination]);?>
<script>
    /**
     * 清除下次执行时间，执行后任务会在一分钟内开始执行
     */
    function clearTaskNext(id) {
        if (!confirm('确认要清除下次执行时间吗？\n清除后任务会在一分钟内开始执行。')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/system/clear-task-next']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
    /**
     * 重置任务状态
     */
    function resetTaskStatus(id) {
        if (!confirm('确定要重置状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/system/reset-task-status']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
    /**
     * 删除定时任务
     */
    function deleteTask(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/system/delete-task']);?>', {'id':id}, function(json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
