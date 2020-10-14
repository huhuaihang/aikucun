<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Goods;
use app\models\KeyMap;
use app\models\Util;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Goods[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '商品列表';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_name" class="sr-only">ShopName</label>
    <?php echo Html::textInput('search_shop', Yii::$app->request->get('search_shop'),
        ['id' => 'search_shop', 'class' => 'form-control', 'placeholder' => '商户名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_username" class="sr-only">BrandName</label>
    <?php echo Html::textInput('search_brand', Yii::$app->request->get('search_brand'), ['id' => 'search_brand', 'class' => 'form-control', 'placeholder' => '品牌名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Category</label>
    <?php echo Html::textInput('search_category', Yii::$app->request->get('search_category'), ['id' => 'search_category', 'class' => 'form-control', 'placeholder' => '分类名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Type</label>
    <?php echo Html::textInput('search_type', Yii::$app->request->get('search_type'), ['id' => 'search_type', 'class' => 'form-control', 'placeholder' => '类型名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('goods_status'), ['prompt' => '商品状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_title" class="sr-only">Title</label>
    <?php echo Html::textInput('search_title', Yii::$app->request->get('search_title'), ['id' => 'search_title', 'class' => 'form-control', 'placeholder' => '商品名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>
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
        <th>商品主图</th>
        <th>商品名称</th>
        <th>商户名称</th>
        <th>商品类型</th>
        <th>商品品牌</th>
        <th>商品分类</th>
        <th>商品状态</th>
        <th>是否违规</th>
        <th>是否首页推荐</th>
        <th>类型</th>
        <th>添加时间</th>
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
            <td><?php echo Html::img(Util::fileUrl($model->main_pic, false, '_40x40'));?></td>
            <td><?php echo Html::a(Html::encode($model->title), Url::to(['/h5/goods/view', 'id' => $model->id]), ['target' => '_blank']); ?>
                <br>
                <?php
                if ($model->sale_type == Goods::TYPE_SUPPLIER) {
                    echo '<i class="fa fa-flag red">一件代发</i>';
                }
                ?>
            </td>
            <td><?php echo Html::encode($model->shop->name);?></td>
            <td><?php echo empty($model->tid) ? "" : Html::encode($model->goods_type->name);?></td>
            <td><?php echo empty($model->bid) ? ' ' : Html::encode($model->goods_brand->name);?></td>
            <td><?php echo empty($model->cid) ? "" : Html::encode($model->goods_category->name);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('goods_status', $model->status)?></span></td>
            <td><span class="label label-default"><?php echo empty($model->goods_violation) ? '正常' : KeyMap::getValue('goods_violation_status', $model->goods_violation->status);?></td>
            <td><?php echo Html::a(KeyMap::getValue('yes_no', $model->is_index), 'javascript:void(0)', ['onclick'=>'toggleIndex(' . $model->id . ')', 'class'=>[Goods::YES=>'label label-success', Goods::NO=>'label label-warning'][$model->is_index]]);?></td>
            <td><?php echo KeyMap::getValue('goods_type', $model->type)?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    !(Yii::$app->manager->can('goods/violation') && $model->status == Goods::STATUS_ON) ?: ['icon' => 'fa fa-bolt', 'href' => Url::to(['/admin/goods/violation', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '设置违规', 'color' => 'yellow'],
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/goods/view', 'id' => $model->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '商品详细'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 设置商品是否推荐
     */
    function toggleIndex(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/goods/recommend'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
