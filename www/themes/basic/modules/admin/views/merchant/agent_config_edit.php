<?php

use app\assets\CitySelectAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\AgentFee
 */

CitySelectAsset::register($this);

$this->title = '添加/修改代理地区';
$this->params['breadcrumbs'][] = '代理地区';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'area')->hiddenInput();?>
<?php echo $form->field($model, 'earnest_money');?>
<?php echo $form->field($model, 'initial_fee');?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<?php $form->end();?>
<script>
    function page_init() {
        $('[name="AgentFee[area]"]').after(
            '<div id="citys">' +
            '    <select name="province"></select>'+
            '    <select name="city"></select>' +
            '    <select name="area"></select>' +
            '</div>'
        );
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('[name="AgentFee[area]"]').val(),
            required: false,
            onChange: function(city) {
                $('[name="AgentFee[area]"]').val(city['code']);
            }
        });
    }
</script>
