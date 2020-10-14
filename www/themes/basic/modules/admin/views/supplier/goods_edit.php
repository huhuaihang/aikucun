<?php

use app\assets\ApiAsset;
use app\assets\FileUploadAsset;
use app\assets\LayerAsset;
use app\models\GoodsSku;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $goods \app\models\Goods
 */

ApiAsset::register($this);
FileUploadAsset::register($this);
LayerAsset::register($this);

$this->title = '编辑商品';
$this->params['breadcrumbs'][] = '供货商管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(['options' => ['id' => 'supplier_goods_edit']]);?>
<?php echo $form->field($goods, 'title')->textInput(['disabled' => true]);?>
<?php echo $form->field($goods, 'price')->textInput(['disabled' => true]);?>
<?php echo $form->field($goods, 'supplier_price');?>
<table class="table" id="sku_list">
    <thead>
    <tr>
        <th>规格</th>
        <th>市场价</th>
        <th>价格</th>
        <th>结算价</th>
    </tr>
    </thead>
    <tbody>
    <?php /** @var GoodsSku $sku */
    foreach ($goods->skuList as $sku) {?>
        <tr>
            <td><?php echo Html::encode($sku->key_name);?></td>
            <td><?php echo $sku->market_price;?></td>
            <td><?php echo $sku->price;?></td>
            <td><input type="text" name="SkuSupplierPrice[<?php echo $sku->id;?>]" value="<?php echo $sku->supplier_price;?>" title="SKU供货商结算价" style="max-width:100px;" /></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
