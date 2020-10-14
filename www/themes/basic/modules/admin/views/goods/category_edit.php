<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\widgets\FileUploadWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\GoodsCategory
 * @var string $cate_list 商品分类 族谱树
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改商品分类';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'pid')->hiddenInput();?>
<?php echo $form->field($model, 'name');?>
<?php echo $form->field($model, 'url');?>
<?php echo $form->field($model, 'image')->widget(FileUploadWidget::className(), [
    'url' => Url::to(['/admin/goods/upload', 'dir' => 'goods_category']),
    'callback' => 'uploadCallback',
])->hint('图片尺寸 最好是正方形 100*100');?>
<?php if(!empty($model->image)){ ?>
    <img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'] . $model->image;?>">
<?php }?>
<script>
    function uploadCallback(url) {
        $('[name="GoodsCategory[image]"]').val(url);
        $('.fileinput-button').after(' <img style="width:100px;height:100px;" src="<?php echo Yii::$app->params['upload_url'];?>/'+url+'">');
    }
</script>
<?php echo $form->field($model, 'is_choicest')->radioList(KeyMap::getValues('yes_no'));?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('goods_category_status'));?>
<?php echo $form->field($model, 'sort')->hint('数值越大越靠前');?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-info" type="submit"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<?php
if (empty($model->id)) {
    $live = 3;
} else {
    $arr  = GoodsCategory::familyTree('', $model->id);
    $live = count($arr) -1;
    $ids = array_column($arr, 'id');
    $str = implode(',', $ids);
}
$live = empty($live)? 1 : $live;
?>
<script>
    function page_init() {
        var $str = '',
            cate = "<?php echo empty($str)? '' : $str;?>",
            cate = cate.split(',');
        for (var i = 1; i <= <?php echo $live;?>; i++) {
            $str += '<select id="level_'+ i +'" data-level="'+ i +'"></select>'
        }
        if ($str.length == 0) $str = '<select id="level_1" data-level="1"></select>';
        $('[name="GoodsCategory[pid]"]').after(
            '<div id="cate">' + $str +'</div>'
        );
        expandChildren(0, 1, cate);
        $("#cate").on('change', 'select', function(){
            var live = $(this).data('level'),
                val = $(this).val();
            expandChildren(val, live +1);
            var pid = val == 0? $(this).prev().val() : val;
            if (pid) {
                $('[name="GoodsCategory[pid]"]').val(pid);
            }
        });
    }

    /**
     * 获取商品下级分类
     * @param null/integer pid
     * @param  integer level
     */
    function expandChildren(pid, level, cate) {
        var $pid = "<?php echo $model->pid;?>";
        if (!$pid && <?php echo $live?> == 1) {
            $('#level_' + level).html('<option value="">顶级分类</option>');
            return;
        }
        if (!pid && level > 1) {
            $('#level_' + level).html('<option value="">请选择</option>');
            $('#level_' + (level + 1)).html('<option value="">请选择</option>');
            return;
        }
        $.getJSON('<?php echo Url::to(['/admin/goods/category-list']);?>', {'pid':pid}, function (json) {
            if (callback(json)) {
                $('#level_' + level).html('<option value="">请选择</option>');
                $.each(json['category_list'], function (index,category) {
                    if ($.inArray(category['id'], cate) >= 0) {
                        var html = '<option value="' + category['id'] + '" selected="selected" >' + category['name'] + '</option>';
                    } else {
                        var html = '<option value="' + category['id'] + '">' + category['name'] + '</option>';
                    }
                    $('#level_' + level).append(html);
                });
                if (level <= <?php echo $live;?>) {
                    var _this = $('#level_' + level),
                        live = _this.data('level'),
                        val =  _this.val() ;
                    expandChildren(val, live + 1, cate);
                    var pid = (val == 0)? _this.prev().val() : val;
                    if (pid) {
                        $('[name="GoodsCategory[pid]"]').val(pid);
                    }
                }
            }
        });
    }
</script>
