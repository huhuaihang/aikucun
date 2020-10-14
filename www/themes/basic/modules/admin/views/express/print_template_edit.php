<?php

use app\models\ExpressPrintParam;
use app\widgets\FileUploadWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $template app\models\ExpressPrintTemplate
 */

$this->title = '添加/修改打印模板';
$this->params['breadcrumbs'][] = '物流快递公司管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($template, 'id');?>
<?php echo $form->field($template, 'eid')->hiddenInput()->hint($template->express->name);?>
<?php echo $form->field($template, 'name');?>
<?php echo $form->field($template, 'background_image')->widget(FileUploadWidget::className(), [
    'url' => Url::to(['/admin/express/upload', 'dir' => 'system']),
    'callback' => 'uploadImageCallback',
])->hint(Html::img(Yii::$app->params['upload_url'] . $template->background_image, ['id' => 'background_image', 'width' => 128]));?>
<script>
    function uploadImageCallback(url) {
        $('[name="ExpressPrintTemplate[background_image]"]').val(url);
        $('#background_image').attr('src', '<?php echo Yii::$app->params['upload_url'];?>' + url);
        $('#template_box')
            .css('background-image', 'url(<?php echo Yii::$app->params['upload_url'];?>' + url + ')')
            .css('background-repeat', 'no-repeat')
            .css('background-size', '1000px 500px');
    }
</script>
<?php echo $form->field($template, 'width')->hint('单位毫米');?>
<?php echo $form->field($template, 'height')->hint('单位毫米');?>
<?php echo $form->field($template, 'offset_top')->hint('单位像素');?>
<?php echo $form->field($template, 'offset_left')->hint('单位像素');?>
<?php echo $form->field($template, 'template')->hiddenInput();?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<script>
    var param_list = <?php echo json_encode(ArrayHelper::getColumn(ExpressPrintParam::find()->all(), 'name'));?>;
    function page_init() {
        $('[name="ExpressPrintTemplate[template]"]')
            .after('<div id="template_box" style="width:1000px;height:500px;border:solid 1px #CCC;position:relative;"></div>')
            .after('<div id="param_box" class="btn-group"></div>');
        param_list.forEach(function (param) {
            $('#param_box').append('<button onclick="printParam(this)" type="button" class="btn btn-xs btn-info">' + param + '</button>');
        });
        $('#template_box')
            .css('background-image', 'url(' + $('#background_image').attr('src') + ')')
            .css('background-repeat', 'no-repeat')
            .css('background-size', '1000px 500px');
        var template = $('[name="ExpressPrintTemplate[template]"]').val();
        if (template === '') {
            template = [];
        } else {
            template = JSON.parse(template);
        }
        for (var i = 0, j = template.length; i < j; i++) {
            var param = template[i]['param'];
            var x = template[i]['x'];
            var y = template[i]['y'];
            $('#template_box').append('<span draggable="true" ondragend="onDragEnd(event)" ondblclick="$(this).remove();" style="border:dashed 2px #F15048;font-weight:bold;position:absolute;top:' + y + 'px;left:' + x + 'px;background-color:#FFF;">' + param + '</span>');
        }
    }

    /**
     * 设置打印变量
     */
    function printParam(o) {
        var param = $(o).html();
        var template = $('[name="ExpressPrintTemplate[template]"]').val();
        if (template === '') {
            template = [];
        } else {
            template = JSON.parse(template);
        }
        for (var i = 0, j = template.length; i < j; i++) {
            if (template[i]['param'] === param) {
                return false;
            }
        }
        if ($.inArray(template, ))
        $('#template_box').append('<span draggable="true" ondragend="onDragEnd(event)" ondblclick="$(this).remove();" style="border:dashed 2px #F15048;font-weight:bold;position:absolute;top:0;left:0;background-color:#FFF;">' + param + '</span>');
        makeTemplateJSON();
    }

    /**
     * 拖放
     */
    function onDragEnd(event) {
        var $target = $(event.target);
        var x = parseInt($target.css('left').replace('px', '')) + event.offsetX;
        var y = parseInt($target.css('top').replace('px', '')) + event.offsetY;
        if (x < 0) {
            x = 0;
        }
        if (y < 0) {
            y = 0;
        }
        if (x > 1000) {
            x = 1000;
        }
        if (y > 500) {
            y = 500;
        }
        $target.css('top', y + 'px').css('left', x + 'px');
        event.preventDefault();
        makeTemplateJSON();
    }

    /**
     * 生成模板JSON
     */
    function makeTemplateJSON() {
        var template = [];
        $('#template_box > span').each(function () {
            var param = $(this).html();
            var x = parseInt($(this).css('left').replace('px', ''));
            var y = parseInt($(this).css('top').replace('px', ''));
            template.push({
                'param': param,
                'x': x,
                'y': y
            });
        });
        $('[name="ExpressPrintTemplate[template]"]').val(JSON.stringify(template));
    }
</script>
