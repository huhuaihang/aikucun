<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\FaqCategory
 * @var $cat_list String 分类id拼接字符串
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改常见问题分类';
$this->params['breadcrumbs'][] = '常见问题分类管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'name');?>
<?php echo $form->field($model, 'pid')->hiddenInput();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('faq_category_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<?php $form->end();?>
<script>
    function page_init(){
        var val = $('[name="FaqCategory[id]"]').val();
        if (val > 0) {
            edit_category();
        } else {
            insert_category();
        }
    }

    /**
     * 添加常见问题分类
     */
    function insert_category(){
        $('[name="FaqCategory[pid]"]').after(
            '<div id="faq_category">' +
            '    <select name="cat_live1" data-live="1"></select>' +
            '    <select name="cat_live2" data-live="2"></select>' +
            '</div>'
        );
        $('#faq_category select').on('change', function(){
            var $this = $(this),
                live = parseInt($this.attr('data-live')) + 1,
                $id = $this.val() || 0,
                $str  = '<option value="0">请选择</option>';
            if ($id == 0) {
                live -= 1;
                $str  = '<option value="0">顶级分类</option>';
            }
            $.getJSON('<?php echo Url::to(['/admin/faq/get-faq-cat']);?>', {'pid':$id}, function (json) {
                if (callback(json)) {
                    var list = json.list;
                    if(live == 2){
                        $str = '';
                    }
                    $.each(list, function(i,v){
                        $str += '<option value=' + v.id + '>' + v.name + '</option>';
                    });
                    $('#faq_category select[name="cat_live'+ live +'"]').html($str);
                    $('[name="FaqCategory[pid]"]').val($id);
                    var change_select =  $('#faq_category select[name="cat_live'+ live +'"]');
                    if (change_select.val() != 0) {
                        change_select.change();
                    }
                }
            });
        });
        $('#faq_category select[name="cat_live1"]').change();
    }

    /**
     * 编辑常见问题分类
     */
    function edit_category(){
        $('[name="FaqCategory[pid]"]').after(
            '<div id="faq_category">' +
            '    <select name="cat_live1" data-live="1"></select>' +
            '</div>'
        );
        var $id = $('[name="FaqCategory[id]"]').val(),
            str = '',
            pid = $('[name="FaqCategory[pid]"]').val();
        $.getJSON('<?php echo Url::to(['/admin/faq/faq-category-parent']);?>', {'id':$id}, function (json) {
            if (callback(json)) {
                var list = json.cat_list;
                if (list.length > 0) {
                    $.each(list, function(i,v){
                        if (v.id == pid) {
                            str += '<option selected="selected" value=' + v.id + '>' + v.name + '</option>';
                        } else {
                            str += '<option value=' + v.id + '>' + v.name + '</option>';
                        }
                        $('[name="cat_live1"]').html(str);
                    });
                } else {
                    $('[name="cat_live1"]').html("<option value='0'>顶级分类</option>");
                }
            }
        });
        $('[name="cat_live1"]').on('change', function(){
            $('[name="FaqCategory[pid]"]').val($(this).val());
        });
    }
</script>
