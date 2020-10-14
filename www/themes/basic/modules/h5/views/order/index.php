<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '我的订单';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/user']);?>"><img src="/images/11_1.png"></a>
        </div>
        <div class="mall-header-title">我的订单</div>
    </header>
    <div class="container">
        <div class="b_order_cont">
            <div class="zhanwei"></div>
            <!--订单列表分类-->
            <ul class="b_myorder1">
                <li><a<?php if (Yii::$app->request->get('search_status', 0) == 0) {echo ' class="b_bordertip"';}?> href="<?php echo Url::to(['/h5/order']);?>">全部</a></li>
                <!--<li><a<?php if (Yii::$app->request->get('search_status') == Order::STATUS_CREATED) {echo ' class="b_bordertip"';}?> href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_CREATED]);?>">待付款</a></li>-->
                <li><a<?php if (Yii::$app->request->get('search_status') == Order::STATUS_PAID) {echo ' class="b_bordertip"';}?> href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_PACKED]);?>">待发货</a></li>
                <li><a<?php if (Yii::$app->request->get('search_status') == Order::STATUS_DELIVERED) {echo ' class="b_bordertip"';}?> href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_DELIVERED]);?>">待收货</a></li>
                <li><a<?php if (Yii::$app->request->get('search_status') == Order::STATUS_RECEIVED) {echo ' class="b_bordertip"';}?> href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_RECEIVED]);?>">待评价</a></li>
            </ul>
            <!--订单列表-->
            <div class="b_order_box" id="app" ref="wrapper">
                <div v-if="order_list.length == 0">
                    <div class="b_no_orderpic" >
                        <img src="/images/no_order_pic.png"/>
                    </div>
                    <p class="b_noop_tip">还没有相关的订单呐~</p>
                </div>
                <ul v-else class="b_myorder2 show">
                    <li v-for="(order, order_index) in order_list">
                        <a :href="'<?php echo Url::to(['/h5/order/view']);?>?order_no='+order.no">
                            <div class="b_order_shop clearfix">
                                <div class="b_homeico">
                                    <img src="/images/b_homedian_03.png"/>
                                </div>
                                <h5>{{order.shop.name}}</h5>
<!--                                <span class="b_line_up">线上</span>-->
                                <p class="b_success" v-if="order.status == <?php echo Order::STATUS_COMPLETE;?>">
<!--                                        <span>已完成</span>-->
                                    </p>
                                    <p class="b_success" v-if="order.status == <?php echo Order::STATUS_CANCEL;?> && order.cancel_fid != ''">
                                        <span>{{order.status_str}}已退款</span>
                                    </p>
                                    <p class="b_un-success" v-else>
                                        <span>{{order.status_str}}</span>
                                    </p>
                            </div>
                            <div class="b_order_detail clearfix" v-for="orderItem in order.item_list">
                                <div class="b_good_img">
                                    <img :src="orderItem.goods.main_pic"/>
                                </div>
                                <div class="b_good_name">
                                    <p><img v-if="orderItem.goods.is_supplier==1" src="/images/zhifa.png" alt="">{{orderItem.goods.title}}</p>
                                    <span>{{orderItem.sku_key_name}}</span>
                                </div>
                                <div class="b_good_price">
                                    <p>￥{{orderItem.price}}</p>
                                    <span>X{{orderItem.amount}}</span>
                                </div>

                            </div>
<!--                            <div v-if="Object.keys(order.gift).length>0">-->
<!--                            <div class="b_order_shop clearfix">-->
<!--                                <div class="b_homeico">-->
<!--                                    <img src="/images/gift.png" style="width: 20px;height: 20px;"/>-->
<!--                                </div>-->
<!--                                <h5>{{order.gift.title}}</h5>-->
<!---->
<!--                            </div>-->
<!--                            <div class="b_order_detail clearfix" >-->
<!---->
<!--                                <div class="b_good_img">-->
<!--                                    <img :src="order.gift.main_pic"/>-->
<!--                                </div>-->
<!--                                <div class="b_good_name">-->
<!--                                    <p>{{order.gift.name}}</p>-->
<!--                                    <span></span>-->
<!--                                </div>-->
<!--                                <div class="b_good_price">-->
<!--                                    <p>￥{{order.gift.price}}</p>-->
<!--                                    <span>X{{order.gift.amount}}</span>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            </div>-->
                            <div class="b_order_total">
                                <p>共<span class="b_good_num">{{goodsAmount(order_index)}}</span>件商品
                                    合计：￥<span class="b_good_tprice">{{order.amount_money}}</span>
                                    <span class="b_trans_fee">(运费:￥{{order.deliver_fee}})</span>
                                </p>
                            </div>
                        </a>
                        <div class="b_order_after clearfix" v-if="order.status == <?php echo Order::STATUS_CREATED;?>">
                            <a class="b_buy_again" :href="'<?php echo Url::to(['/h5/order/pay']);?>?order_no='+order.no" data-pjax="false">去支付</a>
                            <a class="b_refund" href="javascript:void(0)" @click.prevent="deleteOrder(order.no)">删除订单</a>
                        </div>
                        <div class="b_order_after clearfix" v-if="order.status < <?php echo Order::STATUS_DELIVERED;?> && order.status > <?php echo Order::STATUS_CREATED;?> ">
                           <a class="b_buy_again" v-if="order.status == <?php echo Order::STATUS_PAID;?>" href="javascript:void(0)" @click="hurryOrder(order.no)">催单</a>
                           <a class="b_refund" v-if="order.is_pack == 0 && order.is_coupon == 0 && order.pack_coupon_status == 0" href="javascript:void(0)" @click="cancelOrder(order.no)">取消订单</a>
                        </div>
                        <div class="b_order_after clearfix" v-if="order.status == <?php echo Order::STATUS_DELIVERED;?>">
                            <a class="b_buy_again" :href="'<?php echo Url::to(['/h5/order/deliver-info'])?>?order_no='+order.no" data-pjax="false">查看物流</a>
                            <a class="b_buy_again" href="javascript:void(0)" @click="receivedOrder(order.no)">确认收货</a>
                        </div>
                        <div class="b_order_after clearfix" v-if="order.status == <?php echo Order::STATUS_RECEIVED;?>">
                            <a class="b_buy_again" :href="'<?php echo Url::to(['/h5/order/comment']);?>?order_no='+order.no" data-pjax="false">去评价</a>
                        </div>
                        <div class="b_order_after clearfix" v-if="order.status == <?php echo Order::STATUS_COMPLETE;?>">
                            <a class="b_buy_again" v-if="order.is_coupon == 0 && order.pack_coupon_status == 0" :href="'<?php echo Url::to(['/h5/order/confirm']);?>?type=order&order_no='+order.no" data-pjax="false">再次购买</a>
                        </div>
                        <div class="b_order_after clearfix" v-if="order.status == <?php echo Order::STATUS_CANCEL;?>">
                            <a class="b_buy_again" v-if="order.is_coupon == 0 && order.pack_coupon_status == 0" :href="'<?php echo Url::to(['/h5/order/confirm']);?>?type=order&order_no='+order.no" data-pjax="false">再次购买</a>
                            <a class="b_refund" href="javascript:void(0)" @click="deleteOrder(order.no)">删除订单</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            current_page: 1, // 当前页码
            order_list: [], // 订单列表
            scroll: false // 滚动监听器
        },
        methods: {
            loadMore: function () {
                apiGet('<?php echo Url::to(['/api/order/list']);?>', {page: this.current_page, search_status: '<?php echo Yii::$app->request->get('search_status');?>'}, function (json) {
                    if (callback(json)) {
                        json['order_list'].forEach(function (order) {
                            app.order_list.push(order);
                        });
                       // app.gift=
                        console.log(json['order_list'])
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.current_page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.current_page++;
                                            app.loadMore();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },
            goodsAmount: function (order_index) {
                var amount = 0;
                this.order_list[order_index].item_list.forEach(function (item) {
                    amount += item.amount;
                });
                return amount;
            },
            deleteOrder: function (order_no)
            {
                layer.confirm('确定要删除订单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index) {
                    apiGet('<?php echo Url::to(['/api/order/delete']);?>', {'order_no':order_no}, function (json) {
                        if (callback(json)) {
                            layer.close(layer_index);
                            app.order_list.forEach(function (order, index) {
                                if (order_no === order.no) {
                                    app.order_list.splice(index, 1);
                                }
                            });
                            app.$nextTick(function () {
                                app.scroll.refresh();
                            });
                        }
                    });
                });
            },
            cancelOrder: function (order_no) {
                layer.confirm('确定要取消订单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/cancel']);?>', {'order_no':order_no}, function (json) {
                        if (callback(json)) {
                            app.order_list.forEach(function (order, index) {
                                if (order_no === order.no) {
                                    app.order_list.splice(index, 1);
                                }
                            });
                            app.$nextTick(function () {
                                app.scroll.refresh();
                            });
                        }
                    });
                });
            },
            receivedOrder: function (order_no) {
                layer.confirm('确定要确认收货吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/received']);?>', {'order_no':order_no}, function (json) {
                        if (callback(json)) {
                            app.order_list.forEach(function (order, index) {
                                if (order_no === order.no) {
                                    app.order_list.splice(index, 1);
                                }
                            });
                            app.$nextTick(function () {
                                app.scroll.refresh();
                            });
                        }
                    });
                });
            },
            hurryOrder: function (order_no) {
                layer.confirm('确定要催单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/hurry']);?>', {'order_no':order_no}, function (json) {
                        if (callback(json)) {
                            layer.msg('催单成功', {icon: 6});
                        }
                    });
                });
            }
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 125) + 'px';
            this.loadMore();
        }
    });
</script>
