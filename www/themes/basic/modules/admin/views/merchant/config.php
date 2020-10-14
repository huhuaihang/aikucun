<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\GoodsCategory;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\MerchantFee[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '商户保证金';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_cid" class="sr-only">商品分类</label>
    <?php echo Html::hiddenInput('search_cid', Yii::$app->request->get('search_cid'), ['id' => 'search_cid']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/merchant/edit-config']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>商品分类</th>
        <th>保证金</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($model->category->name);?></td>
            <td><?php echo $model->earnest_money;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/merchant/edit-config', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteMerchantFee(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<?php
if (!empty(Yii::$app->request->get('search_cid'))) {
    $arr  = GoodsCategory::familyTree('', Yii::$app->request->get('search_cid'));
    $ids = array_column($arr, 'id');
    $str = implode(',', $ids);
}
?>
<script>
    function page_init() {
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
        $('[name="search_cid"]').after(
            '<div id="cate">' + $str +'</div>'
        );
        expandChildren(0, 1, cate);
        $(document).on('change', 'select', function(){
            var live = $(this).data('level'),
                val = $(this).val();
            expandChildren(val, live +1, cate);
            var pid = val == 0? $(this).prev().val() : val;
            if (pid) {
                $('[name="search_cid"]').val(pid);
            }
        });
    }

    function deleteMerchantFee(id) {
        layer.confirm('您确定要删除当前分类保证金吗？', {
            btn: ['确定', '取消']
        }, function(){
            $.getJSON('<?php echo Url::to('/admin/merchant/delete-config')?>', {'id':id}, function(json){
                if (callback(json)) {
                    layer.msg('删除成功');
                    $('#data_' + id).remove();
                }
            })
        }, function(){
            return true;
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
