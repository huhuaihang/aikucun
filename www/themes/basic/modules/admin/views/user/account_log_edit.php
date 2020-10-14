<?php

use app\assets\FileUploadAsset;
use app\assets\MaskedInputAsset;
use app\models\KeyMap;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\UserAccountLog
 */

FileUploadAsset::register($this);
MaskedInputAsset::register($this);


$this->title = '添加/修改销售员结算单';
$this->params['breadcrumbs'][] = '销售员管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<input id="hidden_upload_file" type="file" name="files[]" data-url="<?php echo Url::to(['/admin/user/upload', 'dir' => 'goods']);?>" style="display:none;" />
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'total_sale_people_count');?>
<?php echo $form->field($model, 'total_sale_count');?>
<?php echo $form->field($model, 'person_sale_count');?>
<?php echo $form->field($model, 'sale_sale_count');?>
<?php echo $form->field($model, 'total_sale_money');?>
<?php echo $form->field($model, 'direct_manager_money');?>
<?php echo $form->field($model, 'infinite_sale_manager_money');?>
<?php echo $form->field($model, 'direct_manager_detail_money');?>
<?php echo $form->field($model, 'two_sale_manager_money');?>
<?php echo $form->field($model, 'sale_bean_detail_money');?>
<?php echo $form->field($model, 'bean_status')->radioList(KeyMap::getValues('bean_status'));?>
<?php echo $form->field($model, 'team_sale_status')->radioList(KeyMap::getValues('team_sale_status'));?>
<div class="row">
    <div class="col-xs-12 col-sm-10"><?php echo $form->field($model, 'detail_pics', [
            'template' => '{label}<div class="col-sm-6">{input}</div><div class="col-xs-4">{hint}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label no-padding-right'],
        ])->hiddenInput();?></div>
</div>
<?php //echo $form->field($model, 'two_infinite_sale_money');?>
<?php //echo $form->field($model, 'dec');?>
<?php //echo $form->field($model, 'time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);;?>
<?php echo $form->field($model, 'remark');?>
<?php echo $form->field($model, 'time')->textInput(['value'=>Yii::$app->formatter->asDatetime($model->time), 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<?php //echo $form->field($model, 'status')->radioList(KeyMap::getValues('account_log_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script src="/js/laydate/laydate.js"></script>
<script>
//    laydate.render({
//        elem: '#useraccountlog-time' //指定元素
//    });

    function page_init(){

        $("#detail_pics_box img").mouseover(function(e){
            alert('tt');
            var landscape="<div id='landscape'><img src='"+this.src+"' alt='' width='400px' height='300px'/></div>";
            $("body").append(landscape);
        }).mouseout(function(){
            $("#landscape").remove();
        });

        $('#hidden_upload_file').fileupload({
            done: function (e, data) {
                var json = data.result;
                if (callback(json)) {
                    $.each(json['files'], function(index, file) {
                        var url = file['url'];
                        var $holder = $('#hidden_upload_file');
                        var callback = $holder.data('callback');
                        if (callback) {
                            callback(url);
                            $holder.data('callback', '');
                        }
                    });
                }
            }
        });

        var $detail_pics = $('[name="UserAccountLog[detail_pics]"]');
        var detail_pics = $detail_pics.val();
        if (detail_pics === '') {
            detail_pics = [];
        } else {
            detail_pics = JSON.parse(detail_pics);
        }
        $detail_pics.after('<span class="red">非必填</span>');
        $detail_pics.after('<br /><button class="btn btn-xs btn-info" type="button" onclick="uploadDetailPic(this)">上传截图</button>');
        $detail_pics.after('<span id="detail_pics_box"></span>');
        $.each(detail_pics, function (i, pic) {
            $('#detail_pics_box').append('<img class="detail" src="<?php echo Yii::$app->params['upload_url'];?>' + pic + '" data-pic="' + pic + '" width="300" height="500" onclick="deleteDetailPic(this)" />');
        });
    }

    /**
     * 上传商品轮播图
     * @param btn 点击的按钮
     */
    function uploadDetailPic(btn) {
        uploadFile(function (url) {
            var $detail_pics = $('[name="UserAccountLog[detail_pics]"]');
            var detail_pics = $detail_pics.val();
            if (detail_pics === '') {
                detail_pics = [];
            } else {
                detail_pics = JSON.parse(detail_pics);
            }
            detail_pics.push(url);
            $detail_pics.val(JSON.stringify(detail_pics));
            $('#detail_pics_box').append('<img src="<?php echo Yii::$app->params['upload_url'];?>' + url + '" data-pic="' + url + '" width="300" height="500" onclick="deleteDetailPic(this)" />');
        });
    }

    /**
     * 删除商品轮播图
     * @param img
     */
    function deleteDetailPic(img) {
        var $img = $(img);
        var $detail_pics = $('[name="UserAccountLog[detail_pics]"]');
        var detail_pics = $detail_pics.val();
        if (detail_pics === '') {
            detail_pics = [];
        } else {
            detail_pics = JSON.parse(detail_pics);
        }
        $.each(detail_pics, function (i, pic) {
            if ($img.data('pic') === pic) {
                detail_pics.splice(i, 1);
                return false;
            }
        });
        $detail_pics.val(JSON.stringify(detail_pics));
        $img.remove();
    }

    /**
     * 上传文件
     * @param callback 上传完成回调函数
     */
    function uploadFile(callback) {
        $('#hidden_upload_file').data('callback', callback).click();
    }

</script>
<style>

    .detail_pics_box img:hover{
        transform: scale(1.2);
    }
</style>