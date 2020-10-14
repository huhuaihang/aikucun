<?php

use app\assets\ApiAsset;
use app\assets\TableAsset;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $category_list \app\models\GoodsCategory[]
 */

ApiAsset::register($this);
TableAsset::register($this);

$this->title = '商品分类管理';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/goods/edit-category']);?>">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>名称</th>
        <th>图片</th>
        <th>地址别名</th>
        <th>是否显示</th>
        <th>商品数量</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($category_list as $level1) {?>
        <tr id="data_<?php echo $level1->id;?>">
            <td><span class="toggle1 fa fa-plus-square" data-id="<?php echo $level1->id;?>"></span><a href="<?php echo Url::to(['/admin/goods/list', 'search_cid' => $level1->id]);?>"><?php echo Html::encode($level1->name);?></a></td>
            <td><?php if (!empty($level1->image)) {?><img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'].$level1->image;?>"><?php }?></td>
            <td><?php echo Html::encode($level1->url);?></td>
            <td><?php echo KeyMap::getValue('goods_category_status', $level1->status);?></td>
            <td><?php echo $level1->getGoodsCount();?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-category', 'id' => $level1->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsCategory(' . $level1->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
        <?php foreach ($level1->childList as $level2) {?>
            <?php if ($level2->status != GoodsCategory::STATUS_DEL) {?>
            <tr id="data_<?php echo $level2->id;?>" data-id="<?php echo $level2->id;?>" data-pid="<?php echo $level1->id;?>" style="display:none;">
                <td style="text-indent:20px;"><span class="toggle2 fa fa-plus-square" data-id="<?php echo $level2->id;?>"></span><a href="<?php echo Url::to(['/admin/goods/list', 'search_cid' => $level2->id]);?>"><?php echo Html::encode($level2->name);?></a></td>
                <td><?php if (!empty($level2->image)) {?><img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'].$level2->image;?>"><?php }?></td>
                <td><?php echo Html::encode($level2->url);?></td>
                <td><?php echo KeyMap::getValue('goods_category_status', $level2->status);?></td>
                <td><?php echo $level2->getGoodsCount();?></td>
                <td><?php echo ManagerTableOp::widget(['items' => [
                        ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-category', 'id' => $level2->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                        ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsCategory(' . $level2->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                    ]]);?></td>
            </tr>
            <?php }?>
            <?php foreach ($level2->childList as $level3) {?>
                <?php if ($level3->status != GoodsCategory::STATUS_DEL) {?>
                <tr id="data_<?php echo $level3->id;?>" data-pid="<?php echo $level2->id;?>" style="display:none">
                    <td style="text-indent:60px;"><a href="<?php echo Url::to(['/admin/goods/list', 'search_cid' => $level3->id]);?>"><?php echo Html::encode($level3->name);?></a></td>
                    <td><?php if (!empty($level3->image)) {?><img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'].$level3->image;?>"><?php }?></td>
                    <td><?php echo Html::encode($level3->url);?></td>
                    <td><?php echo KeyMap::getValue('goods_category_status', $level3->status);?></td>
                    <td><?php echo $level3->getGoodsCount();?></td>
                    <td><?php echo ManagerTableOp::widget(['items' => [
                            ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-category', 'id' => $level3->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                            ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsCategory(' . $level3->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                        ]]);?></td>
                </tr>
                <?php }?>
                <?php foreach ($level3->childList as $level4) {?>
                    <?php if ($level4->status != GoodsCategory::STATUS_DEL) {?>
                    <tr id="data_<?php echo $level4->id;?>" data-pid="<?php echo $level3->id;?>">
                        <td style="text-indent:60px;"><a href="<?php echo Url::to(['/admin/goods/list', 'search_cid' => $level4->id]);?>"><?php echo Html::encode($level4->name);?></a></td>
                        <td><?php if (!empty($level4->image)) {?><img style="width:50px;height:50px;" src="<?php echo Yii::$app->params['upload_url'].$level4->image;?>"><?php }?></td>
                        <td><?php echo Html::encode($level4->url);?></td>
                        <td><?php echo KeyMap::getValue('goods_category_status', $level4->status);?></td>
                        <td><?php echo $level3->getGoodsCount();?></td>
                        <td><?php echo ManagerTableOp::widget(['items' => [
                                ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/goods/edit-category', 'id' => $level4->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                                ['icon' => 'fa fa-trash', 'onclick' => 'deleteGoodsCategory(' . $level4->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                            ]]);?></td>
                    </tr>
                    <?php }?>
                <?php }?>
            <?php }?>
        <?php }?>
    <?php }?>
</table>

<script>
    function page_init(){
        $('.toggle1').on('click', function() {
            if ($(this).hasClass("fa-minus-square")) {
                var id = $(this).data('id');
                $('tr[data-pid="' + id + '"]').each(function (i, v) {
                    var leve2_id = $(this).data('id');
                    $(this).hide();
                    $('tr[data-pid="' + leve2_id + '"]').each(function (i, v) {
                        $(this).hide();
                    });
                });
                $(this).addClass('fa-plus-square');
                $(this).removeClass('fa-minus-square');
            } else {
                var id = $(this).data('id');
                $('tr[data-pid="' + id + '"]').each(function (i, v) {
                    $(this).show();
                });
                $(this).addClass('fa-minus-square');
                $(this).removeClass('fa-plus-square');
            }
        });

        $('.toggle2').on('click', function() {
            if ($(this).hasClass("fa-minus-square")) {
                var leve2_id = $(this).data('id');
                $('tr[data-pid="' + leve2_id + '"]').each(function (i, v) {
                    var leve3_id = $(this).data('id');
                    $(this).hide();
                    $('tr[data-pid="' + leve3_id + '"]').each(function (i, v) {
                        $(this).hide();
                    });
                });
                $(this).addClass('fa-plus-square');
                $(this).removeClass('fa-minus-square');
            } else {
                var leve2_id = $(this).data('id');
                $('tr[data-pid="' + leve2_id + '"]').each(function (i, v) {
                    var leve3_id = $(this).data('id');
                    $(this).show();
                    $('tr[data-pid="' + leve3_id + '"]').each(function (i, v) {
                        $(this).show();
                    });
                });
                $(this).addClass('fa-minus-square');
                $(this).removeClass('fa-plus-square');
            }
        });
    }

    /**
     * 删除商品分类
     * @param id 商品分类编号
     */
    function deleteGoodsCategory(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/goods/category-delete']);?>', {'id': id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
