<?php

use app\assets\ApiAsset;
use app\assets\FileUploadAsset;
use app\assets\LayerAsset;
use app\models\KeyMap;
use app\models\OrderRefund;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $order_item \app\models\OrderItem
 * @var $order_refund \app\models\OrderRefund
 */

ApiAsset::register($this);
LayerAsset::register($this);
FileUploadAsset::register($this);

$this->title = "填写退货信息";
?>
<div class="box">
    <?php echo Html::beginForm('', 'post', ['id' => 'refund_form', 'onsubmit' => 'return saveRefund();']);?>
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title" style="width: 80%">填写退货信息</div>
        <div class="mall-header-right" >
            <?php echo Html::submitButton('提交');?>
        </div>
    </header>
    <div class="container">
        <ul class="b_good_info">
            <li class="b_magt1">
                <div class="b_order_shop clearfix">
                    <h5 class=""><?php echo $order_item->goods->shop->name;?></h5>
                </div>
                <div class="b_order_detail clearfix">
                    <div class="b_good_img">
                        <img src="<?php echo Yii::$app->params['upload_url'] . $order_item->goods->main_pic;?>"/>
                    </div>
                    <div class="b_good_name">
                        <p><?php echo $order_item->title;?></p>
                        <span><?php echo $order_item->sku_key_name;?></span>
                    </div>
                    <div class="b_good_price">
                        <p>￥<?php echo $order_item->price;?></p>
                        <div class="shop-arithmetic">
<!--                            <a href="javascript:void(0);" class="minus">-</a>-->
                            <span class="num" ><?php echo $order_item->amount;?></span>
<!--                            <a href="javascript:void(0);" class="plus">+</a>-->
                            <?php echo Html::activeHiddenInput($order_refund, 'amount', ['value' => $order_item->amount]);?>
                        </div>
                    </div>
                </div>
                <div class="b_order_total">
                    <p class="b_color_red">退款金额：<span class="refund_money"><?php echo Html::activeInput('', $order_refund, 'money',['value' => $order_item->getRefundMoney()])?></span></p>
                </div>
            </li>
            <li>
                <div class="b_tuikuan clearfix">
                    <p>退款类型：</p>
                    <?php echo Html::activeDropDownList($order_refund, 'type', [OrderRefund::TYPE_MONEY => KeyMap::getValue('order_refund_type', OrderRefund::TYPE_MONEY), OrderRefund::TYPE_GOODS_MONEY => KeyMap::getValue('order_refund_type', OrderRefund::TYPE_GOODS_MONEY)])?>
                    <div class="b_arrow_r">
                        <img src="/images/b_arrow_right_03.png"/>
                    </div>
                </div>
            </li>
            <li class="b_leave_message b_magt1">
                <div class="b_tuikuan clearfix">
                    <p>退款原因</p>
                    <select class="refund_reason">
                        <option value="0">--请选择--</option>
                        <option>收到商品破损</option>
                        <option>与商品描述不符</option>
                        <option>商品错发、漏发</option>
                        <option>商品质量问题</option>
                        <option>未按照约定时间发货</option>
                        <option>不喜欢</option>
                        <option>其他</option>
                    </select>
                    <div class="b_arrow_r">
                        <img src="/images/b_arrow_right_03.png"/>
                    </div>
                </div>
                <div class="b_letter">
                    <?php echo Html::activeTextarea($order_refund, 'reason', ['class' => 'b_feedback', 'placeholder' => '请输入文字描述，以便客服为你尽快处理']);?>
                </div>
                <div class="b_upload_area">
                    <p>上传照片：</p>
                    <div class="b_upload_btn">
                        <?php echo Html::activeHiddenInput($order_refund, 'image_list');?>
                        <input type="file"  name="files[]" data-url="<?php echo Url::to(['/h5/order/upload', 'dir' => 'order-refund'])?>" />
                    </div>
                </div>
            </li>
        </ul>
        <div class="b_clasue_area clearfix">
            <div class="b_yes_no">
                <img src="/images/address_selected_03.png"/>
            </div>
            <p class="b_clause">同意</p>
            <a href="<?php echo Url::to(['/h5/about/agreement', 'name' => 'refund_agreement']);?>">《退款服务条款》</a>
        </div>
    </div>
    <?php echo Html::endForm();?>
</div>
<style>
    .refund_money {
        vertical-align:baseline;
        display: inline-block;
        width: 70%;
    }
    .refund_money input {
        width: 100%;
        height: 100%;
        font-size: .4rem;
    }
    p.b_color_red {
        text-align: left;
    }
</style>
<script>
    function page_init(){

        var amount = $('.num').text(),
            max = "<?php echo $order_item->amount;?>";
        var price="<?php echo $order_item->price;?>";
        $('.plus').on('click', function () {
            if (amount < max) {
                amount++;
                $('.num').text(amount);
                $("[name='OrderRefund[money]']").val(price*amount);
            } else {
                layer.msg('数量最多为' + max);
            }
        });
        $('.minus').on('click', function () {
            if(amount > 1) {
                amount--;
                $('.num').text(amount);
                $("[name='OrderRefund[money]']").val(price*amount);
            }else {
                layer.msg('数量最少为1');
            }
            $("[name='OrderRefund[amount]']").val(amount);

        });

        $('.refund_reason').on('change', function () {
            var val = $(this).val(),
                textarea = $('.b_feedback');
            if (val !== '其他' && val !== '0') {
                textarea.val(val);
            } else {
                textarea.val('');
            }
        });



        // 检测输入金额是否超出最大金额数
        var max_money = <?php echo $order_item->order->amount_money?>;
        $("[name='OrderRefund[money]']").on('keyup', function(){
            var num = $(this).val();
            if (num > max_money) {
                layer.msg("您输入的金额超出最大退款金额，请重新输入");
                $("[name='OrderRefund[money]']").val(max_money)
            }
        });

        // 上传图片
        $('input[type="file"]').fileupload({
            done: function (e, data) {
                var json = data.result;
                if (callback(json)) {
                    var url = json.files[0].url,
                        src= json.base+url,
                        image_ele = $("[name='OrderRefund[image_list]']"),
                        image_list = image_ele.val();
                    url = image_list.length > 0? image_list + ',' + url : url
                    image_ele.val(url);
                    $("[name='OrderRefund[image_list]']").val()
                    $(this).parent('.b_upload_btn').before("<img src="+src+" width='50'>");
                }
            }
        });

        var toggle = true;
        $(".b_yes_no").click(function(){
            if(toggle){
                $(this).children("img").attr("src","/images/address_noselect_03.png");
                toggle = false;
            }else{
                $(this).children("img").attr("src","/images/address_selected_03.png");
                toggle = true;
            }
        });
    }

    function saveRefund() {
        if($('.refund_reason').val() == 0){
            layer.msg('请选择退款原因');
            return false;
        }
        var money = $("[name='OrderRefund[money]']").val();
        var max_money = <?php echo $order_item->order->amount_money?>;
       if(money>max_money)
       {
           layer.msg("您输入的金额超出最大退款金额，请重新输入");
           return false;
       }
        var $form = $('#refund_form');
        var data = $form.serializeArray();
        data.push({'name':'ajax', 'value':1});
        $.post($form.attr('action'), data, function (json) {
            if (callback(json)) {
                window.location.href = '<?php echo Url::to(['/h5/order/refund']);?>';
            }
        });
        return false;
    }

</script>
