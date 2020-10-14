<?php

use app\assets\LayerAsset;
use app\models\GoodsTraceVideo;
use app\models\KeyMap;
use app\widgets\FileUploadWidget;
use app\widgets\FileVideoUploadWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $traceVideo app\models\GoodsTraceVideo
 * @var $ossCoverName string OSS封面文件名
 * @var $ossVideoName string OSS视频文件名
 * @var $ossPolicy array OSS授权
 */

LayerAsset::register($this);

$this->title = '添加/修改视频';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($traceVideo, 'id');?>
<?php echo $form->field($traceVideo, 'name');?>
<?php echo $form->field($traceVideo, 'desc')->textarea();?>
<?php echo $form->field($traceVideo, 'cid')->dropDownList(KeyMap::getValues('goods_trace_video_type'));?>
<?php echo $form->field($traceVideo, 'cover_image')->widget(FileVideoUploadWidget::class, [
    'url' => $ossPolicy['host'],
    'method' => 'POST',
    'isAliyunOss' => true,
    'callback' => 'uploadCoverCallback',
    'formData' => [
        ['name' => 'key', 'value' => $ossPolicy['dir'] . $ossCoverName],
        ['name' => 'policy', 'value' => $ossPolicy['policy']],
        ['name' => 'OSSAccessKeyId', 'value' => $ossPolicy['accessid']],
        ['name' => 'success_action_status', 'value' => '200'],
        ['name' => 'signature', 'value' => $ossPolicy['signature']],
    ],
])->hint($traceVideo->cover_image . ' ');?>
<?php echo $form->field($traceVideo, 'video')->widget(FileVideoUploadWidget::class, [
    'url' => $ossPolicy['host'],
    'method' => 'POST',
    'isAliyunOss' => true,
    'callback' => 'uploadVideoCallback',
    'formData' => [
        ['name' => 'key', 'value' => $ossPolicy['dir'] . $ossVideoName],
        ['name' => 'policy', 'value' => $ossPolicy['policy']],
        ['name' => 'OSSAccessKeyId', 'value' => $ossPolicy['accessid']],
        ['name' => 'success_action_status', 'value' => '200'],
        ['name' => 'signature', 'value' => $ossPolicy['signature']],
    ],
])->hint($traceVideo->video . ' ');?>
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
        $('[name="GoodsTraceVideo[cover_image]"]').val(url);
        $('.field-goodstracevideo-cover_image .hint-block').html(url);
        layer.msg('文件已上传。');
    }
    /**
     * 上传视频文件回调
     */
    function uploadVideoCallback(data) {
        var url = data.url;
        var key = '';
        $.each(data.formData, function (i, item) {
            if (item.name == 'key') {
                key = item.value;
                return false;
            }
        });
        url += '/' + key;
        $('[name="GoodsTraceVideo[video]"]').val(url);
        $('.field-goodstracevideo-video .hint-block').html(url);
        layer.msg('文件已上传。');
    }
</script>
