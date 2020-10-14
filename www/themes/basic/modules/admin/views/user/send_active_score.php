<?php

use app\assets\TableAsset;
use yii\helpers\Url;
use yii\helpers\Html;
use app\assets\LayerAsset;
/**
 * @var $this \yii\web\View
 * @var $list []
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);
LayerAsset::register($this);
$this->registerJsFile('/js/tagsinput.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/tagsinput.css',['position' => $this::POS_END]);
$this->title = '手动发放活动积分';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .bootstrap-tagsinput .badge{
        font-size: 18px;
    }
</style>
<?php echo Html::beginForm();?>
<div class="form-group field-mobile_list required">

    <label class="control-label" for="score">活动名称</label>
    <input id="name" class="form-control" name="name" aria-required="true">
    <label class="control-label" for="to_mobile">接收人员手机号码</label>
    <input  class="form-control tagsinput"  id="tagsinputval" data-role="tagsinput" name="to_mobile" aria-required="true" />
    <div class="help-block">多个手机号码用逗号</div>
<!--    <input id="to_uid" class="form-control" name="to_uid" aria-required="true" value="--><?php //echo $mobile;?><!--">-->
    <label class="control-label" for="score">发放积分</label>
    <input id="score" class="form-control" name="score" aria-required="true">

</div>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary" type="button" onclick="send()"><i class="ace-icon fa fa-check bigger-110"></i>发送</button>
        <button type="reset"  onclick="reload()" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110" ></i>刷新页面</button>
    </div>
</div>
<?php echo Html::endForm();?>
<script>
    /**
     * 提交表单
     */
    function send() {
        $('.btn-primary').attr("disabled","true"); //设置变灰按钮
        var form_data = $("form").serializeArray();
        console.log(form_data)
        $.post('<?php echo Url::to(['/admin/user/send-active-score']);?>', {'data':form_data}, function (json) {
               if(json['message'])
               {
                   layer.msg(json['message'], {icon: 2});
                   $('.btn-primary').removeAttr("disabled"); //设置变灰按钮
               }else{

                    if(json['error_mobile'].length > 0)
                    {
                        layer.msg('操作失败', {icon: 3});
                        var str='';
                        json['error_mobile'].forEach(function (item) {
                            str+='<p style="color: red">'+'手机号'+item['mobile']+'积分发放失败['+item['message']+']'+'</p>';
                        })
                        $('.help-block').html(str);
                        $('.btn-primary').removeAttr("disabled");
                    }else{
                        layer.msg('发放成功', {icon: 1});
                        setTimeout(function(){  window.location.reload(); }, 1000);
                    }

               }

               console.log(json)

        });
    }

    function reload() {
        setTimeout(function(){  window.location.reload(); }, 100);
    }

</script>