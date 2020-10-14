<?php

use app\models\KeyMap;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $version app\models\SystemVersion
 */

$this->title = '添加/修改版本';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($version, 'id');?>
<?php echo $form->field($version, 'api_version');?>
<?php echo $form->field($version, 'ios_version');?>
<?php echo $form->field($version, 'android_version');?>
<?php echo $form->field($version, 'is_support')->radioList(KeyMap::getValues('yes_no'));?>
<?php echo $form->field($version, 'aes_key');?>
<?php echo $form->field($version, 'aes_iv');?>
<?php echo $form->field($version, 'android_download_source')->radioList(KeyMap::getValues('android_download_source'));?>
<?php echo $form->field($version, 'android_download_url');?>
<?php echo $form->field($version, 'update_info')->textarea();?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
