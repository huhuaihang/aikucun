<?php

use app\models\KeyMap;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\AdLocation
 */

$this->title = '添加/修改广告位置';
$this->params['breadcrumbs'][] = '广告管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
    <?php echo $form->field($model, 'id');?>
    <?php echo $form->field($model, 'type')->dropDownList(KeyMap::getValues('ad_type'));?>
    <?php echo $form->field($model, 'name');?>
    <?php echo $form->field($model, 'max_count');?>
    <?php echo $form->field($model, 'width');?>
    <?php echo $form->field($model, 'height');?>
    <?php echo $form->field($model, 'code')->textarea();?>
    <?php echo $form->field($model, 'remark');?>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
            <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
            <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
        </div>
    </div>
<?php $form->end();?>
