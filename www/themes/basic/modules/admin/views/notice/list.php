<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Notice;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Notice[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '公告资讯列表';
$this->params['breadcrumbs'][] = '公告资讯管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/notice/edit']);?>">添加</a>
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
        <th>标题</th>
        <th>主图</th>
        <th>状态</th>
        <th>添加时间</th>
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
            <td><?php echo $model->title;?></td>
            <td><?php if (!empty($model->main_pic)) {?><img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'].$model->main_pic;?>"><?php }?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('notice_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/notice/edit', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ($model->status != Notice::STATUS_SHOW) ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ($model->status != Notice::STATUS_HIDE) ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteNotice(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 删除公告资讯
     * @param id 公告资讯编号
     */
    function deleteNotice(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/notice/delete']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    /**
     * 设置公告资讯状态
     * @param id 公告资讯编号
     */
    function toggleStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/notice/status'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
