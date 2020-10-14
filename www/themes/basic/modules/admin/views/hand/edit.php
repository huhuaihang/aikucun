<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use app\widgets\FileUploadWidget;
use kucha\ueditor\UEditor;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\assets\MaskedInputAsset;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\NewHand
 */
MaskedInputAsset::register($this);
ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改新手入门';
$this->params['breadcrumbs'][] = '新手入门管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'title');?>
<?php echo $form->field($model, 'start_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->start_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99 99:99:99']);?>
<?php echo $form->field($model, 'main_pic')->widget(FileUploadWidget::className(), [
    'url' => Url::to(['/admin/goods/upload', 'dir' => 'notice']),
    'callback' => 'uploadCallback',
])->hint('图片尺寸 最好是正方形 100*100');?>
<?php if(!empty($model->main_pic)){ ?>
    <img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'] . $model->main_pic;?>">
<?php }?>
<script>
    function uploadCallback(url) {
        $('[name="NewHand[main_pic]"]').val(url);
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
