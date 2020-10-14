<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\ShopConfig;
use app\models\Supplier;
use app\models\SupplierConfig;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\OrderRefund
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '填写退货物流';
?>
<?php echo Html::beginForm('', 'post', ['id' => 'logistics_form', 'onsubmit' => 'return saveLogistics();']);?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/order/refund']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">填写退货物流</div>
    </header>
    <?php $sid = $model->orderItem->goods->sid;
    $supplier_id = $model->orderItem->goods->supplier_id;
    ?>
    <div class="container">
        <div class="return_relevant">
            <div class="div1">
                <span class="span1">收货人</span>
                <span class="span2"><?php echo empty($supplier_id) ? ShopConfig::getConfig($sid, 'refund_deliver_user') : SupplierConfig::getConfig($supplier_id, 'refund_deliver_user');?></span>
            </div>
            <div class="div1">
                <span class="span1">联系电话</span>
                <span class="span2"><?php echo empty($supplier_id) ? ShopConfig::getConfig($sid, 'refund_deliver_mobile') : SupplierConfig::getConfig($supplier_id, 'refund_deliver_mobile');?></span>
            </div>
            <div class="div1">
                <span class="span1">退货地址</span>
                <span class="span2"><?php echo empty($supplier_id) ? ShopConfig::getConfig($sid, 'refund_deliver_address') : SupplierConfig::getConfig($supplier_id, 'refund_deliver_address');?></span>
            </div>
            <?php if (!empty(ShopConfig::getConfig($sid, 'refund_deliver_remark'))) {?>
            <div class="div1">
                <span class="span1">附加信息</span>
                <span class="span2"><?php echo ShopConfig::getConfig($sid, 'refund_deliver_remark');?></span>
            </div>
            <?php } ?>
        </div>
        <div class="return_relevant">
            <div class="div1">
                <span class="span1">物流公司</span>
                <span class="span2">
                    <?php echo Html::activeTextInput($model, 'express_name', ['placeholder' => '物流公司名称'])?>
                </span>
            </div>
            <div class="div1">
                <span class="span1">物流单号</span>
                <span class="span2">
                    <?php echo Html::activeTextInput($model, 'express_no', ['placeholder' => '物流单号'])?>
                </span>
            </div>
            <div class="div1">
                <span class="span1">联系电话</span>
                <span class="span2">
                    <?php echo Html::activeInput('number', $model, 'contact_mobile', ['placeholder' => '联系电话'])?>
                </span>
            </div>
        </div>
        <dl class="return_relevant_tishi">
            <dt><img src="/images/write_return1.jpg"></dt>
            <dd>请根据上述的信息将商品寄退给商家并填写</dd>
            <dd>物流信息，商家确认收货后会进行退款操作</dd>
        </dl>
        <div class="write_return_button">
            <?php echo Html::submitButton('提交退货物流信息');?>
        </div>
    </div>
</div>
<?php echo Html::endForm();?>
<style>
    .span2 input {
        font-size: 0.3rem;
        width: 100%;
    }
</style>
<script>
    function saveLogistics() {
        var $form = $('#logistics_form');
        var data = $form.serializeArray();
        data.push({'name':'ajax', 'value':1});
        $.post($form.attr('action'), data, function (json) {
            if (callback(json)) {
                window.location.href = '<?php echo Url::to(['/h5/order/refund-view', 'id' => $model->id]);?>';
            }
        });
        return false;
    }
</script>
