<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\ShopBrand;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\ShopBrand[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '商户品牌管理';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_brand_name" class="sr-only">品牌名</label>
    <?php echo Html::textInput('search_brand_name', Yii::$app->request->get('search_brand_name'), ['id' => 'search_brand_type', 'class' => 'form-control', 'placeholder' => '品牌名', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_shop_name" class="sr-only">店铺名</label>
    <?php echo Html::textInput('search_shop_name', Yii::$app->request->get('search_shop_name'), ['id' => 'search_shop_name', 'class' => 'form-control', 'placeholder' => '店铺名', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_brand_type" class="sr-only">店铺名</label>
    <?php echo Html::dropDownList('search_brand_type', Yii::$app->request->get('search_brand_type'), [''=>'请选择品牌类型'] + KeyMap::getValues('shop_brand_type'), ['id' => 'search_shop_type', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_brand_status" class="sr-only">品牌状态</label>
    <?php echo Html::dropDownList('search_brand_status', Yii::$app->request->get('search_brand_status'), [''=>'请选择品牌状态'] + KeyMap::getValues('shop_brand_status'), ['id' => 'search_brand_status', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
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
        <th>品牌名称</th>
        <th>店铺</th>
        <th>LOGO</th>
        <th>TM或R</th>
        <th>品类</th>
        <th>品牌类型</th>
        <th>商户品牌状态</th>
        <th>有效期</th>
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
            <td><?php echo Html::encode($model->brand->name);?></td>
            <td><?php echo Html::encode($model->shop->name);?></td>
            <td><?php echo Html::img(Yii::$app->params['upload_url'] . $model->brand->logo, ['style' => 'max-width:200px;']);?></td>
            <td><?php echo Html::encode($model->brand->tm_r);?></td>
            <td><?php foreach ($model->brand->typeList as $type) {echo Html::encode($type->name), '<br />';}?></td>
            <td><?php echo KeyMap::getValue('shop_brand_type', $model->type);?></td>
            <td><?php echo KeyMap::getValue('shop_brand_status', $model->status);?></td>
            <td><?php echo Html::encode($model->brand->valid_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/merchant/edit-shop-brand', 'id' => $model->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '查看'],
                    $model->status != ShopBrand::STATUS_WAIT ? false : ['icon' => 'fa fa-check', 'onclick'=> 'accept('.$model->id.')', 'btn_class' => 'btn btn-xs btn-success', 'tip' => '通过', 'color' => 'green'],
                    $model->status != ShopBrand::STATUS_WAIT ? false : ['icon' => 'fa fa-close', 'onclick'=> 'refuse('.$model->id.')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '拒绝', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<script>
    //通过审核
    function accept(id){
        if (!confirm('确定要审核通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/shop-brand-status'])?>', {'id':id, 'status': 'accept'}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    //拒绝审核
    function refuse(id){
        if (!confirm('确定要拒绝吗？')) {
            return false;
        }
        layer.prompt({title: '输入拒绝备注', formType: 2}, function(text, index){
            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/merchant/shop-brand-status'])?>', {'id':id, 'status': 'refuse', 'remark':text}, function(json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }
</script>

