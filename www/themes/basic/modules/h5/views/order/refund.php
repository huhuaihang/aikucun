<?php
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use app\models\OrderRefund;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\OrderRefund[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = "退款售后";
?>

<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">退款售后</div>
    </header>
    <div class="container">
        <div class="b_order_box">
            <ul class="b_myorder2">
                <?php foreach ($list as $model) {?>
                <li>
                    <a href="#">
                        <div class="b_order_shop clearfix">
                            <div class="b_homeico">
                                <img src="/images/b_homedian_03.png"/>
                            </div>
                            <h5 class="b_line_up"> <?php echo $model->orderItem->goods->shop->name;?></h5>
                            <p class="p2"  style="    font-size: .3rem;color: #cc1000;line-height: 0.9rem;font-family: 'Microsoft Yahei'; margin-left: .3rem;"><?php echo KeyMap::getValue('order_refund_status', $model->status)?></p>
                            <p class="b_un-success">
    <!--                            <span>待付款</span>-->
                            </p>
                        </div>
                        <div class="b_order_detail clearfix">
                            <div class="b_good_img">
                                <img src="<?php echo Yii::$app->params['upload_url'], $model->orderItem->goods->main_pic;?>"/>
                            </div>
                            <div class="b_good_name">
                                <p><?php echo $model->orderItem->title?></p>
                                <span><?php echo $model->orderItem->sku_key_name?></span>
                            </div>
                            <div class="b_good_price">
                                <p>￥<?php echo  $model->orderItem->price;?></p>
                                <span>X <?php echo $model->amount;?></span>
                            </div>
                        </div>
                        <div class="b_order_total">
                            <p>共<span class="b_good_num"><?php echo $model->amount;?></span>件商品 合计：￥<span class="b_good_tprice"><?php echo $model->money;?></span><span class="b_trans_fee">(运费:￥<?php echo $model->orderItem->order->deliver_fee;?>)</span></p>
                        </div>
                    </a>
                    <div class="b_order_after b_order_after1 clearfix">
                        <?php echo KeyMap::getValue('order_refund_type', $model->type);?>
                        <?php if ($model->status == OrderRefund::STATUS_COMPLETE) {?>
                        <a class="b_buy_again" href="javascript:void(0);" onclick="deleteRefund(<?php echo $model->id;?>)">删除订单</a>
                        <?php } ?>
                        <?php if ($model->status == OrderRefund::STATUS_ACCEPT) {?>
                        <a class="b_buy_again" href="<?php echo Url::to(['/h5/order/refund-related', 'id' => $model->id])?>">退货相关</a>
                        <?php } ?>
                        <a class="b_refund" href="<?php echo Url::to(['/h5/order/refund-view', 'id' => $model->id])?>">查看详情</a>
                    </div>
                </li>
                <?php }?>
            </ul>
        </div>
    </div>
    <!--订单列表-->
</div>
<script>
    function deleteRefund($id) {
        layer.confirm('确定要删除吗？', {
            title: '确认',
            btn: ['确定', '取消']
        }, function(){
            $.getJSON('<?php echo Url::to(['/h5/order/delete-refund']);?>', {'id':$id}, function (json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }
</script>
