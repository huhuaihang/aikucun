<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use app\widgets\FileUploadWidget;
use kucha\ueditor\UEditor;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\Notice
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改公告资讯';
$this->params['breadcrumbs'][] = '公告资讯管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'title');?>
<?php //echo $form->field($model, 'main_pic')->widget(FileUploadWidget::className(), [
//    'url' => Url::to(['/admin/goods/upload', 'dir' => 'notice']),
//    'callback' => 'uploadCallback',
//])->hint('图片尺寸 最好是正方形 100*100');?>
<?php echo $form->field($model, 'desc')->textarea();?>
<?php if(!empty($model->main_pic)){ ?>
    <img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'] . $model->main_pic;?>">
<?php }?>
<script>
    function uploadCallback(url) {
        $('[name="Notice[main_pic]"]').val(url);
        $('.fileinput-button').after(' <img style="width:100px;height:100px;" src="<?php echo Yii::$app->params['upload_url'];?>/'+url+'">');
    }
</script>
<?php echo $form->field($model, 'content')->widget(UEditor::className(), [
    'clientOptions' => [
        'serverUrl' => Url::to(['ue-upload']),
    ]
]);?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('notice_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<?php $form->end();?>
