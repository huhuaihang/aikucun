<?php
use app\assets\FileUploadAsset;
use app\assets\MaskedInputAsset;
use app\models\AdLocation;
use app\models\KeyMap;
use app\models\Util;
use app\widgets\FileUploadWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\Ad
 */

FileUploadAsset::register($this);
MaskedInputAsset::register($this);

$this->title = '添加/修改广告';
$this->params['breadcrumbs'][] = '广告管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
    <?php echo Html::activeHiddenInput($model, 'id');?>
    <?php echo $form->field($model, 'name');?>
    <?php echo $form->field($model, 'lid')->dropDownList(ArrayHelper::map(AdLocation::find()->all(), 'id', function($item) {return $item['name'] . ' ' . ($item['type'] == AdLocation::TYPE_IMAGE ? ' ' . $item['width'] . 'x' . $item['height'] : '') . ' ' . KeyMap::getValue('ad_type', $item['type']) ;}));?>
    <?php echo $form->field($model, 'txt');?>
    <?php echo $form->field($model, 'img')
        ->hint(!empty($model->img) ? Html::img(Util::fileUrl($model->img), ['width'=>100]) : ' ')
        ->widget(FileUploadWidget::className(), ['url'=>Url::to(['/admin/ad/upload', 'dir'=>'da']), 'callback'=>'uploadCallback']);?>
    <script>
    function uploadCallback(url) {
        $('#ad-img').val(url);
        $('.field-ad-img .hint-block').html('<img src="<?php echo Yii::$app->params['upload_url'];?>' + url + '" width="100" />');
    }
    </script>
    <?php echo $form->field($model, 'url');?>
    <?php echo $form->field($model, 'start_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->start_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99 99:99:99']);?>
    <?php echo $form->field($model, 'end_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->end_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99 99:99:99']);?>
    <?php echo $form->field($model, 'sort');?>
    <?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('ad_status'));?>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
            <button type="submit" class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
            <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
        </div>
    </div>
<?php $form->end();?>
<script>
    function page_init() {
        $('[name="Ad[lid]"]').change(function () {
            if (/图片$/.test($(this).find('option[value=' + $(this).val() + ']').text())) {
                $('[name="Ad[img]"]').parent().show();
            } else {
                $('[name="Ad[img]"]').parent().hide();
            }
            $('[name="Ad[txt]"]').attr('placeholder', /商品$/.test($(this).find('option[value=' + $(this).val() + ']').text()) ? '请填写商品编号' : '');
        }).change();
    }
</script>
