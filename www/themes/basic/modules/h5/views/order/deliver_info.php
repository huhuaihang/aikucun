<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $deliver_list \app\models\OrderDeliver[]
 * @var $trace_list $deliver_list[]['trace']
 */
$this->registerJsFile('/js/clipboard.min.js');
ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '订单物流信息详情';
?>
<style>
    .copy{
        color: #cc0001;
        border: 1px solid #cc0001;
        margin-left: .2rem;
        padding: .15rem .28rem;
        display: block;
        float: right;
        font-size: .3rem;
        line-height: .3rem;
        font-family: 'Microsoft Yahei';
        border-radius: .08rem;
        width: 2rem;
        margin-top: 15px;
    }
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">物流信息</div>
    </header>
    <div class="container">
        <div class="z_logistics_information">
            <div class="div1_top1">
                <div class="div1_top1_box">
                    <div class="limit-buy-nav" id="J-lbn">
                        <a href="javascript:void(0)" class="prev"><img src="/images/logistics_left2.png"></a>
                        <span>
                            <span id="J-lbcp">1</span><span>/<?php echo count($deliver_list); ?></span>
                        </span>
                        <a href="javascript:void(0)" class="next"><img src="/images/logistics_right2.png"></a>
                    </div>
                </div><!--div1_top1_box-->
            </div><!--div1_top1-->
            <div class="limit-buy-bd" id="limit-buy">
                <?php foreach ($deliver_list as $deliver) { ?>
                    <div class="products">
                        <div class="div1_top2">
                            <div class="dl">
                                <?php foreach ($deliver->itemList as $item) { ?>
                                    <a href="<?php echo Url::to(['/h5/goods/view', 'id' => $item->orderItem->gid]); ?>">
                                        <div class="dt">
                                            <img src="<?php echo Yii::$app->params['upload_url'], $item->orderItem->goods->main_pic; ?>">
                                        </div>
                                        <div class="dd dd1">
                                            <span class="span1"><?php echo $item->orderItem->title; ?></span>
                                            <span class="span2">￥<?php echo $item->orderItem->price; ?></span>
                                        </div>
                                        <div class="dd dd2">
                                            <span class="span1"><?php echo Html::encode(str_replace('_', ' ', $item->orderItem->sku_key_name)); ?></span>
                                            <span class="span2">x<?php echo $item->orderItem->getDeliverListAmount($deliver->id) ?></span>
                                        </div>
                                    </a>
                                <?php } ?>
                            </div>
                        </div><!--div1_top2-->
                        <div class="div1">
                            <div class="left">
                                <p><span>快递公司:</span><span><?php echo $deliver->express->name; ?></span></p>
                                <p class="p2"><span>快递单号:</span><span id="codeNum"><?php echo $deliver->no; ?></span></p>
                            </div>
                            <dl class="right">
<!--                                <dt><img src="/images/logistics5.jpg"></dt>-->
                                <dd class="copy" onclick="copyArticle()"  data-clipboard-target="#input">复制单号</dd>
                            </dl>
                        </div>
                        <div class="text_box">
                            <div class="min_box">
                                <?php $trace_list = json_decode($deliver->trace);
                                if (!empty($trace_list) && is_array($trace_list)) {
                                    foreach ($trace_list as $key => $trace) {
                                        ?>
                                        <div class="div2">
                                            <div class="left"><?php if ($key == 0) { ?><img
                                                        src="/images/logistics1.png"><?php } else { ?><img
                                                        src="/images/logistics2.png"><?php } ?></div>
                                            <div class="right">
                                                <p class="p1"><?php echo $trace->context;?></p>
                                                <p class="p2"><?php echo $trace->ftime;?></p>
                                            </div><!--right-->
                                        </div><!--div2-->
                                    <?php } ?>
                                <?php } else {
                                    ?>
                                    <div class="div2">
                                        <div class="left"><img src="/images/logistics1.png"></div>
                                        <div class="right">
                                            <p class="p1">暂无物流信息</p>
                                            <p class="p2"><?php echo date('Y-m-d H:i:s', time()); ?></p>
                                        </div><!--right-->
                                    </div><!--div2-->
                                <?php } ?>
                            </div><!--min_box-->
                        </div><!--text_box-->
                    </div><!--products-->
                <?php }?>
            </div><!--class="limit-buy-bd" id="limit-buy"-->
        </div>
    </div>
</div><!--box-->
<script type="text/javascript">
    function page_init() {
        $("#limit-buy .products").not(":first").hide()
        var lb = $("#limit-buy"),
            lb_cur = 1,
            lb_timer = null;
        t = 1;

        function showLimitBuyProducts() {
            if (lb_cur < 1) {
                lb_cur = 2;
            } else if (lb_cur > <?php echo count($deliver_list);?>) {
                lb_cur = 1;
            }
            $("#J-lbcp").html(lb_cur);
            var products = $("#limit-buy .products").hide().eq(lb_cur - 1).show(),
                ta = products.find("textarea");

            if (ta.length) {
                products.html(ta.val());
            }
        }

        $("#J-lbn .prev, #J-lb .btn-prev").click(function () {
            lb_cur--;
            showLimitBuyProducts();
        });
        $("#J-lbn .next, #J-lb .btn-next").click(function () {
            lb_cur++;
            showLimitBuyProducts();
        });
        $("#J-lb").hover(function () {
            clearInterval(lb_timer);
            lb_timer = null;
            $("#J-lb .btn-prev, #J-lb .btn-next").show();
        }, function () {
            lb_timer = setInterval(function () {
                lb_cur++;
                showLimitBuyProducts();
            }, 10000);
            $("#J-lb .btn-prev, #J-lb .btn-next").hide();
        });

    }
    function copyArticle(event) {
        const range = document.createRange();
        range.selectNode(document.getElementById('codeNum'));

        const selection = window.getSelection();
        if(selection.rangeCount > 0) selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('copy');
        alert("复制成功！");
    }
</script>
