<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\GoodsCategory;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\MerchantFee
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '添加/修改商户保证金';
$this->params['breadcrumbs'][] = '商户保证金';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo $form->field($model, 'cid')->hiddenInput();?>
<?php echo $form->field($model, 'earnest_money');?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<?php $form->end();?>
<?php
if (!empty($model->cid)) {
    $arr  = GoodsCategory::familyTree('', $model->cid);
    $ids = array_column($arr, 'id');
    $str = implode(',', $ids);
}
?>
<script>
    function page_init(){
        var $str = '',
            cate = "<?php echo empty($str)? '' : $str;?>";
        if (cate) {
            cate = cate.split(',');
        } else {
            cate = [];
        }
        for (var i = 1; i <= 4; i++) {
            $str += '<select id="level_'+ i +'" data-level="'+ i +'"></select>'
        }
        $('[name="MerchantFee[cid]"]').after(
            '<div id="cate">' + $str +'</div>'
        );
        expandChildren(0, 1, cate);
        $(document).on('change', 'select', function(){
            var live = $(this).data('level'),
                val = $(this).val();
            expandChildren(val, live +1, cate);
            var pid = val == 0? $(this).prev().val() : val;
            if (pid) {
                $('[name="MerchantFee[cid]"]').val(pid);
            }
        });
    }

    /**
     * 获取商品下级分类
     * @param null/integer pid
     * @param  integer level
     */
    function expandChildren(pid, level, cate) {
        if (!pid && level > 1) {
            $('#level_' + level).html('<option value="">请选择</option>');
            $('#level_' + (level + 1)).html('<option value="">请选择</option>');
            $('#level_' + (level + 2)).html('<option value="">请选择</option>');
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
                if (level <= 4) {
                    $("#cate select[id=level_"+ level +"]").change();
                }
            }
        });
    }
</script>