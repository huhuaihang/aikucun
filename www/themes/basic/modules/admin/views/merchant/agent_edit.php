<?php

use app\assets\CitySelectAsset;
use app\models\KeyMap;
use app\models\Manager;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\Agent
 */

CitySelectAsset::register($this);

$this->title = '添加/修改代理商';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'username');?>
<?php echo $form->field($model, 'password')->textInput(['value' => '']);?>
<?php echo $form->field($model, 'mobile');?>
<?php echo $form->field($model, 'contact_name');?>
<?php echo $form->field($model, 'area')->hiddenInput();?>
<?php echo $form->field($model, 'email');?>
<?php echo $form->field($model, 'remark')->textarea();?>
<?php echo $form->field($model, 'status')->radioList([Manager::STATUS_ACTIVE=>KeyMap::getValue('manager_status', Manager::STATUS_ACTIVE), Manager::STATUS_STOPED=>KeyMap::getValue('manager_status', Manager::STATUS_STOPED)]);?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script>
    function page_init() {
        $('[name="Agent[area]"]').after(
            '<div id="citys">' +
            '    <select name="province"></select>' +
            '    <select name="city"></select>' +
            '    <select name="area"></select>' +
            '</div>'
        );
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('[name="Agent[area]"]').val(),
            required: false,
            onChange: function(city) {
                $('[name="Agent[area]"]').val(city['code']);
            }
        });
    }
</script>
