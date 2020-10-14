<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\ManagerRole
 */

$authManager = Yii::$app->authManager;

$this->title = '添加/修改管理员角色';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
    <?php echo Html::activeHiddenInput($model, 'id');?>
    <?php echo $form->field($model, 'name');?>
    <?php echo $form->field($model, 'remark')->textarea();?>
    <div class="row">
        <div class="col-sm-12">
            <?php $assigned_permission_list = $authManager->getChildren('manager_role_' . $model->id);?>
            <?php foreach ($authManager->getPermissions() as $permission) {
                if (strpos($permission->name, '/') !== false) {continue;}?>
                <div class="row">
                    <div class="col-sm-2"><input type="checkbox" name="auth[]" id="<?php echo $permission->name;?>" value="<?php echo $permission->name;?>"<?php if (in_array($permission, $assigned_permission_list)) {echo ' checked="checked"';}?> onclick="$('input[id^=' + this.value + ']').prop('checked', $(this).prop('checked'));" /><label for="<?php echo $permission->name;?>"><?php echo $permission->description;?></label></div>
                    <div class="col-sm-10">
                        <ul class="list-inline">
                            <?php $sub_permission_list = $authManager->getChildren($permission->name);?>
                            <?php foreach ($sub_permission_list as $sub_permission) {?>
                                <li><input type="checkbox" name="auth[]" id="<?php echo $sub_permission->name;?>" value="<?php echo $sub_permission->name;?>"<?php if (in_array($sub_permission, $assigned_permission_list)) {echo ' checked="checked"';}?> /><label for="<?php echo $sub_permission->name;?>"><?php echo $sub_permission->description;?></label></li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
            <?php }?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
            <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
            <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
        </div>
    </div>
<?php $form->end();?>
