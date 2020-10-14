<?php

use app\models\KeyMap;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\Merchant
 */

$this->title = '修改商户';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'username');?>
<?php echo $form->field($model, 'mobile');?>
<?php echo $form->field($model, 'password')->textInput(['value' => '']);?>
<?php echo $form->field($model, 'contact_name');?>
<?php echo $form->field($model, 'email');?>
<?php echo $form->field($model, 'remark')->textarea();?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('merchant_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
