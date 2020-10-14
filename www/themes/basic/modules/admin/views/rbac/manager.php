<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Manager;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\Manager[]
 * @var $pagination yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '管理员列表';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_username" class="sr-only">Username</label>
    <?php echo Html::textInput('search_username', Yii::$app->request->get('search_username'), ['id' => 'search_username', 'class' => 'form-control', 'placeholder' => '用户名', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_nickname" class="sr-only">Nickname</label>
    <?php echo Html::textInput('search_nickname', Yii::$app->request->get('search_nickname'), ['id' => 'search_nickname', 'class' => 'form-control', 'placeholder' => '昵称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Mobile</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号码', 'style' => 'max-width:100px;']);?>
</div>

<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/rbac/edit-manager']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace"/>
                <span class="lbl"></span>
            </label>
        </th>
        <th>用户名</th>
        <th>手机</th>
        <th>邮箱</th>
        <th>角色</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) { ?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::a(Html::encode($model->username), '');?></td>
            <td><?php echo Html::encode($model->mobile);?></td>
            <td><?php echo Html::a(Html::encode($model->email), '');?></td>
            <td><?php echo Html::encode($model->role->name);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('manager_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/rbac/edit-manager', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-history', 'href' => Url::to(['/admin/rbac/log', 'search_mid' => $model->id]), 'btn_class' => 'btn btn-xs btn-default', 'tip' => '操作日志'],
                    $model->status != Manager::STATUS_STOPED ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleManagerStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    $model->status != Manager::STATUS_ACTIVE ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleManagerStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteManager(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 删除管理员
     */
    function deleteManager(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/rbac/delete-manager']);?>', {'id': id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    /**
     * 切换管理员状态
     */
    function toggleManagerStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/rbac/set-status']);?>', {'id': id}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
