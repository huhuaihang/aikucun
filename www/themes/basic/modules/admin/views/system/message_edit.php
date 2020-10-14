<?php

use app\models\KeyMap;
use kucha\ueditor\UEditor;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\SystemMessage
 */

$this->title = '添加系统消息';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'title');?>
<?php echo $form->field($model, 'target_type[]')->checkboxList(KeyMap::getValues('system_message_target_type'));?>
<?php echo $form->field($model, 'content')->widget(UEditor::className(), [
    'clientOptions' => [
        'serverUrl' => Url::to(['ue-upload']),
    ]
]);?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
