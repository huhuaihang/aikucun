<?php

use app\assets\MaskedInputAsset;
use app\models\KeyMap;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\MasterUserAccountLog
 */

MaskedInputAsset::register($this);

$this->title = '添加/修改店主结算单';
$this->params['breadcrumbs'][] = '店主管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'money');?>
<?php echo $form->field($model, 'time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<?php //echo $form->field($model, 'jan');?>
<?php //echo $form->field($model, 'feb');?>
<?php //echo $form->field($model, 'mar');?>
<?php //echo $form->field($model, 'apr');?>
<?php //echo $form->field($model, 'may');?>
<?php //echo $form->field($model, 'jun');?>
<?php //echo $form->field($model, 'jul');?>
<?php //echo $form->field($model, 'aug');?>
<?php //echo $form->field($model, 'sep');?>
<?php //echo $form->field($model, 'oct');?>
<?php //echo $form->field($model, 'nov');?>
<?php //echo $form->field($model, 'dec');?>
<?php //echo $form->field($model, 'time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);;?>
<?php echo $form->field($model, 'remark');?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('master_account_log_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script src="/js/laydate/laydate.js"></script>
<script>
//    laydate.render({
//        elem: '#useraccountlog-time' //指定元素
//    });
</script>
