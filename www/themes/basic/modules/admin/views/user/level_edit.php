<?php

use app\models\KeyMap;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use app\widgets\FileUploadWidget;
/**
 * @var $this yii\web\View
 * @var $model app\models\UserLevel
 */

$this->title = '添加/修改销售员等级';
$this->params['breadcrumbs'][] = '销售员管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'name');?>
<?php //echo $form->field($model, 'logo')->widget(FileUploadWidget::className(), [
//    'url' => Url::to(['/admin/goods/upload', 'dir' => 'level_logo']),
//    'callback' => 'uploadCallback',
//]);?>
<!--<div class="logo" >-->
<!--    --><?php //if(!empty($model->logo)){ ?>
<!--        <img style="width:100px;" src="--><?php //echo Yii::$app->params['upload_url'] . $model->logo;?><!--">-->
<!--    --><?php //}?>
<!--</div>-->
<?php //echo $form->field($model, 'money');?>
<?php echo $form->field($model, 'description');?>
<?php echo $form->field($model, 'commission_ratio_1')->input('text', ['style' => 'width:200px;'])->hint('%');?>
<?php echo $form->field($model, 'commission_ratio_2')->input('text', ['style' => 'width:200px;'])->hint('%');?>
<?php echo $form->field($model, 'commission_ratio_3')->input('text', ['style' => 'width:200px;'])->hint('%');?>
<?php //echo $form->field($model, 'money_1')->input('text', ['style' => 'width:200px;'])->hint('元');?>
<?php //echo $form->field($model, 'money_2')->input('text', ['style' => 'width:200px;'])->hint('元');?>
<?php //echo $form->field($model, 'money_3')->input('text', ['style' => 'width:200px;'])->hint('元');?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script>

    function uploadCallback(url) {
        $('[name="UserLevel[logo]"]').val(url);
        $('.logo').html(' <img style="width:100px;" src="<?php echo Yii::$app->params['upload_url'];?>/'+url+'">');
       // $('[name="UserLevel[logo]"]').html(' <img style="width:100px;" src="<?php echo Yii::$app->params['upload_url'];?>/'+url+'">');
    }
</script>