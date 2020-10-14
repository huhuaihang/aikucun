<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\GoodsCategory;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $merchant_fee int
 * @var $goods_cate string 经营类目
 * @var $merchant_fee_list \app\models\MerchantFee[]
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '设置商户保证金';
$this->params['breadcrumbs'][] = '商户保证金';
$this->params['breadcrumbs'][] = $this->title;
?>
<table  class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">经营类目</th>
    </tr>
    <tr>
        <th>申请类目</th>
        <td><?php echo $goods_cate;?></td>
    </tr>
    <tr>
        <th>类目价目表</th>
        <td>
            <?php if (!empty($merchant_fee_list)) {
                foreach ($merchant_fee_list as $merchantFee) {
                    echo GoodsCategory::findOne($merchantFee->cid)->name . ':' . $merchantFee->earnest_money . '<br>';
                }
            }?>
        </td>
    </tr>
    <tr>
        <th>应交保证金</th>
        <td><input type="text" name="merchant_fee" id="merchant_fee" value="<?php echo $merchant_fee;?>"> </td>
    </tr>
    <input type='hidden' id="merchant_id" value="<?php echo $merchant_id;?>">
</table>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button class="btn btn-primary" onclick='saveMerchantFee(<?php echo $merchant_id;?>)'><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
    </div>
</div>
<script>
    function saveMerchantFee(id){
        if (!confirm('确定要设置为通过吗？')) {
            return false;
        }
        var merchant_fee = $('#merchant_fee').val();
        var reg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/;
        if (!reg.test(merchant_fee)) {
            layer.msg('请核对保证金金额。');
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/accept-data2']);?>', {'id':id, 'merchant_fee':merchant_fee}, function (json) {
            if (callback(json)) {
                layer.msg('设置成功。');
                window.location.href = '<?php echo Url::to(['/admin/merchant/join']);?>';
            }
        });
    }
</script>
