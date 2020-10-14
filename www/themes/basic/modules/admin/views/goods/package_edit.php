<?php

use app\assets\LayerAsset;
use app\models\GoodsTraceVideo;
use app\models\KeyMap;
use app\widgets\batchupload\FileUpload;
use app\widgets\FileUploadWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Util;

/**
 * @var $this yii\web\View
 * @var $model app\models\Package
 */

LayerAsset::register($this);

$this->title = '添加/修改套餐卡';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;


?>

<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput( $model , 'id');?>
<?php echo $form->field( $model , 'name');?>
<?php echo $form->field( $model , 'count');?>
<?php echo $form->field( $model , 'price');?>
<?php echo $form->field( $model , 'package_price');?>
<?php echo $form->field( $model , 'remark')->textarea();?>
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
