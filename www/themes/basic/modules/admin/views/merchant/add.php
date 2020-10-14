<?php

use app\assets\CitySelectAsset;
use app\models\KeyMap;
use app\models\Merchant;
use app\models\Shop;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\Merchant
 */

CitySelectAsset::register($this);

$this->title = '添加商户';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;

$merchant = new Merchant();
$shop = new Shop();
?>
<?php $form = ActiveForm::begin();?>
<h3>商户信息</h3>
<?php echo Html::activeHiddenInput($merchant, 'id');?>
<?php echo $form->field($merchant, 'type')->radioList(KeyMap::getValues('merchant_type'));?>
<?php echo $form->field($merchant, 'username');?>
<?php echo $form->field($merchant, 'mobile');?>
<?php echo $form->field($merchant, 'password')->textInput(['value' => '']);?>
<?php echo $form->field($merchant, 'contact_name');?>
<?php echo $form->field($merchant, 'is_person')->radioList(KeyMap::getValues('yes_no'));?>
<h3>店铺信息</h3>
<?php echo $form->field($shop, 'name');?>
<?php echo $form->field($shop, 'area')->hiddenInput();?>
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
    $('[name="Shop[area]"]').after(
        '<div id="citys">' +
        '    <select name="province"></select>' +
        '    <select name="city"></select>' +
        '    <select name="area"></select>' +
        '</div>'
    );
    $('#citys').citys({
        dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
        code: $('[name="Shop[area]"]').val(),
        required: true,
        onChange: function(city) {
            $('[name="Shop[area]"]').val(city['code']);
        }
    });
}
</script>