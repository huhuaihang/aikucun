<?php

use app\assets\LayerAsset;
use app\models\KeyMap;
use app\assets\MaskedInputAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;


/**
 * @var $this yii\web\View
 * @var $goodSource app\models\GoodsSource
 * @var $ossCoverName string OSS封面文件名
 * @var $ossVideoName string OSS视频文件名
 * @var $ossPolicy array OSS授权
 */
MaskedInputAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改素材';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;


?>

<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput( $goodSource , 'id');?>
<?php echo $form->field( $goodSource , 'name');?>
<?php echo $form->field($goodSource, 'start_time')->textInput(['value'=>Yii::$app->formatter->asDatetime($goodSource->start_time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99 99:99:99']);?>
<?php echo $form->field( $goodSource , 'desc')->textarea();?>
<?php echo $form->field( $goodSource , 'cid')->dropDownList(KeyMap::getValues('goods_source_type'));?>

<?php echo $form->field($goodSource, 'img_list')->widget('app\widgets\batchupload\FileUpload')?>

<div class="row" id="pics_preview">

</div>

<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script>
    /**
     * 上传封面文件回调
     */
    function uploadCoverCallback(data) {

        var url = data.url;
        var key = '';

        $.each(data.formData, function (i, item) {
            if (item.name == 'key') {
                key = item.value;
                return false;
            }
        });
        url += '/' + key;
        alert(url);
        var html = '<div class="col-xs-6 col-md-3" style="position: relative"><span class="del_pic" onclick="delpic(this)"></span>';
        html += '<input type="hidden" value="'+url+'" name="GoodsSource[img_list][]" ><a class="thumbnail" title="点击查看大图" href="'+url+'" target="_blank"><img class="img-responsive" src="'+url+'"></a>';
        html += '</div>'
        $('#pics_preview').append(html );

        $('[name="GoodsSource[img_list]"]').val(url);
        $('.field-goodssource-img_list .hint-block').html(url);


        layer.msg('文件已上传。');



    }
    // /**
    //  * 上传视频文件回调
    //  */
    // function uploadVideoCallback(data) {
    //     var url = data.url;
    //     var key = '';
    //     $.each(data.formData, function (i, item) {
    //         if (item.name == 'key') {
    //             key = item.value;
    //             return false;
    //         }
    //     });
    //     url += '/' + key;
    //     $('[name="GoodsTraceVideo[video]"]').val(url);
    //     $('.field-goodstracevideo-video .hint-block').html(url);
    //     layer.msg('文件已上传。');
    // }
</script>
