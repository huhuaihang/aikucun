<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use kucha\ueditor\UEditor;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\Faq
 * @var $cat_list string 分类id拼接字符串
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改常见问题';
$this->params['breadcrumbs'][] = '常见问题管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'title');?>
<?php echo $form->field($model, 'cid')->hiddenInput();?>
<?php echo $form->field($model, 'content')->widget(UEditor::className(), [
    'clientOptions' => [
        'serverUrl' => Url::to(['ue-upload']),
    ]
]);?>
<?php echo $form->field($model, 'tags');?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('faq_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<?php $form->end();?>
<script>
    function page_init() {
        var cat_list = '<?php echo $cat_list;?>',
            $cat = cat_list.split(',');
        $('[name="Faq[cid]"]').after(
            '<div id="faq_category">' +
            '    <select name="cat_live1" data-live="1"></select>' +
            '    <select name="cat_live2" data-live="2"></select>' +
            '    <select name="cat_live3" data-live="3"></select>' +
            '</div>'
        );
        $('#faq_category select').on('change', function(){
            var $this = $(this),
                live = parseInt($this.attr('data-live')) + 1,
                $id = $this.val() || 0,
                $str = '<option value="0">请选择</option>';
            if ($id == 0) {
                live -= 1;
            }
            if ($id > 0 || live == 1) {
                var option = '';
                if ($id == 0){
                    option = $('#faq_category select[name="cat_live2"]').html();
                }
                if (live == 2 || option.length > 0) {
                    $('#faq_category select[name="cat_live2"]').html($str);
                    $('#faq_category select[name="cat_live3"]').html($str);
                }
                if(option.length > 0) {
                    $('[name="Faq[cid]"]').val('');
                    return false;
                }
                $.getJSON('<?php echo Url::to(['/admin/faq/get-faq-cat']);?>', {'pid': $id}, function (json) {
                    if (callback(json)) {
                        var list = json.list;
                        $.each(list, function (i, v) {
                            if (v.id == $cat[live - 1]) {
                                $str += '<option selected="selected" value=' + v.id + '>' + v.name + '</option>';
                            } else {
                                $str += '<option value=' + v.id + '>' + v.name + '</option>';
                            }
                        });
                        $('#faq_category select[name="cat_live' + live + '"]').html($str);
                        $('[name="Faq[cid]"]').val($id);
                        var change_select = $('#faq_category select[name="cat_live' + live + '"]');
                        if (change_select.val() != 0) {
                            change_select.change();
                        }
                    }
                });
            } else {
                var prev = live - 1,
                    next = live + 1,
                    select_val = $('#faq_category select[name="cat_live' + prev + '"]'),
                    select_next = $('#faq_category select[name="cat_live' + next + '"]');
                $('[name="Faq[cid]"]').val(select_val.val());
                select_next.html($str);
            }
        });
        $('#faq_category select[name="cat_live1"]').change();
    }
</script>
