<?php

use app\models\GoodsViolation;
use app\models\ViolationType;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model GoodsViolation
 */

$this->title = '设置商品违规';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'vid')
                         ->dropDownList(ViolationType::find()
                         ->select(['name','id'])
                         ->indexBy('id')
                         ->column(), ['prompt'=>'请选择违规类型']);?>
<?php echo $form->field($model, 'remark')->textarea(['row' => 3]);?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>