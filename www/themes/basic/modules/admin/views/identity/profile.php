<?php

use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\ManagerProfileForm
 */

$this->title = '用户设置';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin([
    'options' => ['id' => 'user-profile-form', 'class' => 'form-horizontal'],
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-sm-5\">{input}</div>\n<div class=\"col-xs-4\">{error}</div>",
        'labelOptions' => ['class' => 'col-sm-3 control-label no-padding-right'],
        'inputOptions' => ['class' => 'col-xs-12'],
    ],
]); ?>
<div class="tabbable">
    <ul class="nav nav-tabs padding-16">
        <li class="active">
            <a data-toggle="tab" href="#edit-basic">
                <i class="green ace-icon fa fa-pencil-square-o bigger-125"></i>
                基本信息
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#edit-settings">
                <i class="purple ace-icon fa fa-cog bigger-125"></i>
                用户设置
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#edit-password">
                <i class="blue ace-icon fa fa-key bigger-125"></i>
                修改密码
            </a>
        </li>
    </ul>
    <div class="tab-content profile-edit-tab-content">
        <div id="edit-basic" class="tab-pane in active">
            <h4 class="header blue bolder smaller">通用信息</h4>
            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($model, 'username'); ?>
                    <div class="space-4"></div>
                    <?php echo $form->field($model, 'nickname'); ?>
                </div>
            </div>

            <div class="space"></div>
            <h4 class="header blue bolder smaller">联系方式</h4>

            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($model, 'mobile'); ?>
                    <div class="space-4"></div>
                    <?php echo $form->field($model, 'email'); ?>
                </div>
            </div>
        </div>

        <div id="edit-settings" class="tab-pane">
            <div class="space-10"></div>
            <!--
            <div>
                <label class="inline">
                    <input type="checkbox" name="form-field-checkbox" class="ace" />
                    <span class="lbl"> Make my profile public</span>
                </label>
            </div>

            <div class="space-8"></div>

            <div>
                <label class="inline">
                    <input type="checkbox" name="form-field-checkbox" class="ace" />
                    <span class="lbl"> Email me new updates</span>
                </label>
            </div>
             -->
        </div>

        <div id="edit-password" class="tab-pane">
            <div class="space-10"></div>

            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($model, 'new_password')->passwordInput(); ?>
                    <div class="space-4"></div>
                    <?php echo $form->field($model, 'confirm_password')->passwordInput(); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="clearfix form-actions">
    <div class="col-xs-12 col-sm-8 text-center">
        <button class="btn btn-info">
            <i class="ace-icon fa fa-check bigger-110"></i>
            保存
        </button>
        <button class="btn" type="reset">
            <i class="ace-icon fa fa-undo bigger-110"></i>
            重置
        </button>
    </div>
</div>
<?php $form->end(); ?>
