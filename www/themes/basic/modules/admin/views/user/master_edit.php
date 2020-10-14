<?php

use app\assets\MaskedInputAsset;
use app\models\KeyMap;
use app\models\UserLevel;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\User
 */

MaskedInputAsset::register($this);

$this->title = '添加/修改店主资料';
$this->params['breadcrumbs'][] = '店主管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'real_name');?>
<?php echo $form->field($model, 'wx_no');?>
<?php echo $form->field($model, 'nickname');?>
<?php echo $form->field($model, 'password')->passwordInput(['value' => '']);?>
<?php echo $form->field($model, 'mobile');?>
<?php echo $form->field($model, 'pid');?>
<?php echo $form->field($model, 'team_pid');?>
<?php //echo $form->field($model, 'level_id')->dropDownList(ArrayHelper::map(UserLevel::find()->andWhere(['status' => UserLevel::STATUS_OK])->all(), 'id', 'name'));?>
<?php echo $form->field($model, 'shop_name');?>
<?php if (empty($model->id)) {?>
<?php echo $form->field($model, 'create_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->create_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<?php } else {?>
    <?php echo $form->field($model, 'create_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->create_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<?php //echo Yii::$app->formatter->asDatetime($model->create_time);?>
<?php }?>
<?php //echo $form->field($model, 'gender')->radioList(KeyMap::getValues('gender'));?>
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
    laydate.render({
        elem: '#user-create_time' //指定元素
    });
</script>