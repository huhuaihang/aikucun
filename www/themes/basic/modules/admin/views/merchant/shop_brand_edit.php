<?php

use app\models\KeyMap;
use app\models\ShopBrand;
use app\widgets\FileUploadWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\ShopBrand
 */

$this->title = '审核商户品牌';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo Html::activeHiddenInput($model->brand, 'id');?>
<?php echo $form->field($model->brand, 'name');?>
<?php echo $form->field($model->brand, 'owner');?>
<?php if (!empty($model->brand->logo)) {
    echo Html::img(Yii::$app->params['upload_url'] . $model->brand->logo);
}?>
<?php echo $form->field($model->brand, 'logo')->widget(FileUploadWidget::className(), [
    'url' => Url::to(['/admin/goods/upload', 'dir' => 'brand']),
    'callback' => 'uploadCallback'
]);?>
<?php echo $form->field($model->brand, 'tm_r')->radioList(['TM' => 'TM', 'R' => 'R']);?>
<?php echo $form->field($model->brand, 'valid_time')->textInput(['value' => $model->brand->valid_time]);?>
<?php echo $form->field($model, 'type')->dropDownList(KeyMap::getValues('shop_brand_type'));?>
<?php echo $form->field($model, 'file_list')->hiddenInput();?>
<?php echo $form->field($model, 'status')->radioList([ShopBrand::STATUS_VALID => '通过', ShopBrand::STATUS_REJECTED => '拒绝']);?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script>
    function page_init(){
        var $file_list = $('[name="ShopBrand[file_list]"]');
        var file_list = $file_list.val();
        if (file_list === '') {
            file_list = [];
        } else {
            file_list = JSON.parse(file_list);
        }
        $file_list.after('<span id="detail_pics_box"></span>');
        $.each(file_list, function (i, pic) {
            $('#detail_pics_box').append('<img src="<?php echo Yii::$app->params['upload_url'];?>' + pic + '" data-pic="' + pic + '" width="100" height="100" onclick="deleteDetailPic(this)" />');
        });
    }
    function uploadCallback(url) {
        $('[name="GoodsBrand[logo]"]').val(url);
    }
</script>
