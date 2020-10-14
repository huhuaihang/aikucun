<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\Goods;
use app\models\GoodsType;
use app\models\KeyMap;
use app\models\Util;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Goods[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '商品列表';
$this->params['breadcrumbs'][] = '供货商管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_supplier" class="sr-only">供货商</label>
    <?php echo Html::textInput('search_supplier', Yii::$app->request->get('search_supplier'), ['id' => 'search_supplier', 'class' => 'form-control', 'placeholder' => '供货商']);?>
    <label for="search_name" class="sr-only">商品名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '商品名称']);?>
    <label for="search_id" class="sr-only">商品编号</label>
    <?php echo Html::textInput('search_id', Yii::$app->request->get('search_id'), ['id' => 'search_id', 'class' => 'form-control', 'placeholder' => '商品编号']);?>
    <label for="search_type" class="sr-only">类型</label>
    <?php echo Html::dropDownList('search_type', Yii::$app->request->get('search_type'), [''=>'请选择类型'] + ArrayHelper::map(GoodsType::find()->all(), 'id', 'name'), ['id' => 'search_type', 'class' => 'form-control', 'placeholder' => '类型']);?>
    <label for="search_category" class="sr-only">分类</label>
    <?php echo Html::textInput('search_category', Yii::$app->request->get('search_category'), ['id' => 'search_category', 'class' => 'form-control', 'placeholder' => '分类']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br>
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'excel']);?>" class="btn btn-info btn-sm">导出</a>
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
        <th>商品主图</th>
        <th>商品名称</th>
        <th>商品类型</th>
        <th>商品品牌</th>
        <th>供货商</th>
        <th>商品分类</th>
        <th>商品状态</th>
        <th>是否违规</th>
        <th>类型</th>
        <th>添加时间</th>
        <th>结算价</th>
        <th>零售价</th>
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
            <td><img src="<?php echo Util::fileUrl($model->main_pic, false, '_40x40');?>"></td>
            <td><?php echo Html::a(Html::encode($model->title), Yii::$app->params['site_host'] . '/h5/goods/view?id=' . $model->id, ['target' => '_blank']); ?></td>
            <td><?php echo empty($model->tid) ? "" : Html::encode($model->goods_type->name);?></td>
            <td><?php echo empty($model->bid) ? ' ' : Html::encode($model->goods_brand->name);?></td>
            <td><?php echo Html::encode($model->supplier->name);?></td>
            <td><?php echo empty($model->cid) ? "" : Html::encode($model->goods_category->name);?></td>
            <td><span class="label label-default arrowed-in-right arrowed<?php if ($model->status == Goods::STATUS_ON) {echo ' label-success';}?>"><?php echo KeyMap::getValue('goods_status', $model->status)?></span></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo empty($model->goods_violation) ? '正常' : KeyMap::getValue('goods_violation_status', $model->goods_violation->status);?></td>
            <td><?php echo KeyMap::getValue('goods_type', $model->type)?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo $model->supplier_price;?></td>
            <td><?php echo $model->price;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/supplier/goods-edit', 'id' => $model->id]), 'btn_class' => 'btn btn-success btn-xs', 'tip' => '编辑商品'],
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/goods/view', 'id' => $model->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '商品详细'],
                    ['icon' => 'fa fa-commenting', 'href' => Url::to(['/admin/goods/comment', 'id' => $model->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '评论'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
