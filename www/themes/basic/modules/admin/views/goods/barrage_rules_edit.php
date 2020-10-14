<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $barrage app\models\GoodsBarrageRules
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改商品弹幕规则';
$this->params['breadcrumbs'][] = '商品弹幕';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($barrage, 'id');?>
<?php echo $form->field($barrage, 'title');?>
<?php //echo $form->field($service, 'desc')->textarea(['style' => 'min-height:300px;']);?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
