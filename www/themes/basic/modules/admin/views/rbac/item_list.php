<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$authManager = Yii::$app->authManager;

$this->title = '管理权限列表';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
    <div class="form-group">
        <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/rbac/edit-item']);?>">添加</a>
    </div>
<?php echo Html::endForm();?>
<table id="simple-table" class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>上级权限</th>
            <th>下级权限</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($authManager->getPermissions() as $permission) {
        if (strpos($permission->name, '/') !== false) {continue;}
        $sub_permission_list = $authManager->getChildren($permission->name);?>
        <tr>
            <th rowspan="<?php echo count($sub_permission_list) + 1;?>"><?php echo $permission->description;?><br />
                <button type="button" class="btn btn-xs btn-info update_menu" data-pname="<?php echo $permission->name;?>">更新菜单</button>
                <a class="btn btn-success btn-xs" href="<?php echo Url::to(['/admin/rbac/edit-item', 'parent' => $permission->name]);?>">添加权限</a>
            </th>
        </tr>
        <?php foreach ($sub_permission_list as $sub_permission) {?>
            <tr>
                <td><?php echo $sub_permission->name, '（', $sub_permission->description, '）';?></td>
                <td>&nbsp;</td>
            </tr>
        <?php }?>
    <?php }?>
    </tbody>
</table>
<script>
    function page_init() {
        /**
         * 更新管理菜单
         */
        $('.update_menu').click(function() {
           $.getJSON('<?php echo Url::to(['/admin/rbac/update-menu']);?>', {'permission_name':$(this).data('pname')}, function (json) {
               if (callback(json)) {
                   layer.msg('菜单已更新。');
               }
           });
        });
    }
</script>
