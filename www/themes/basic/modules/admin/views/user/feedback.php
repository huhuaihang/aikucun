<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Feedback;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Feedback[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '用户反馈';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_content" class="sr-only">反馈内容</label>
    <?php echo Html::textInput('search_content', Yii::$app->request->get('search_content'), ['id' => 'search_content', 'class' => 'form-control', 'placeholder' => '反馈内容', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
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
        <th>用户名称</th>
        <th>手机号</th>
        <th>客户端型号</th>
        <th>客户端版本</th>
        <th>反馈内容</th>
        <th>状态</th>
        <th>反馈时间</th>
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
            <td><?php echo html::encode($model->user->nickname);?></td>
            <td><?php echo html::encode($model->user->mobile);?></td>
            <td><?php echo Html::encode($model->client);?></td>
            <td><?php echo Html::encode($model->version);?></td>
            <td><?php echo StringHelper::truncate($model->content, 15);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('feedback_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/user/view-feedback', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    ($model->status != Feedback::STATUS_FINISH) ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ($model->status != Feedback::STATUS_WAIT) ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'delete_feedback(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 删除用户反馈
     * @param id 编号
     */
    function delete_feedback(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/delete-feedback']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    /**
     * 更改用户反馈状态
     * @param id 编号
     */
    function toggleStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/status-feedback']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>