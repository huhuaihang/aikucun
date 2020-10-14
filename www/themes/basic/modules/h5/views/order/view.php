<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\KeyMap;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $order \app\models\Order
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '订单详情';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png"></a>
        </div>
        <div class="mall-header-title">订单详情</div>
        <div class="mall-header-right"><a href="javascript:void(0)"><img src="/images/order_shar.png"></a></div>
    </header>
    <div class="container" v-if="order['no'] != ''">
        <div class="dingdan_stay">
            <div class="div1">
                <p>
                    <span>{{order['address']['name']}}</span>
                    <span>{{order['address']['mobile']}}</span>
                </p>
                <p class="p2">{{order['address']['city'] | cityFormat}} {{order['address']['address']}}</p>
            </div><!--div1-->
            <!--<div class="off-line_div1">
                <div class="left">
                    <p class="p1">取票号</p>
                    <p class="p2">8015 8123 1651</p>
                </div>
                <div class="right">
                    <img src="/images/erweima.jpg">
                </div>
            </div>--><!--off-line_div1-->
            <div class="text">
                <div class="div2">
                    <span class="span1">
                        <img :src="order['shop']['logo']">
                        <b>{{order['shop']['name']}}</b>
                    </span>
<!--                    <span class="span2">线上</span>-->
                    <span class="span3">{{order_status_map[order.status]}}</span>
                </div>
                <div class="div3" v-for="item in order.item_list">
                    <dl>
                        <dt><img :src="item.goods.main_pic"></dt>
                        <dd class="dd1"><span class="span1"><img v-if="item.goods.is_supplier==1" src="/images/zhifa.png" alt="" class="xiang_zhi">{{item.goods.title}}</span><span class="span2">￥{{item.price}}</span></dd>
                        <dd class="dd2"><span class="span1">{{item.sku_key_name}}</span><span class="span2">x{{item.amount}}</span></dd>
                        <dd v-if="order.status == <?php echo Order::STATUS_RECEIVED;?> && order.is_coupon==0 && order.is_pack == 0 && order.pack_coupon_status == 0" class="dd3"><a :href="'<?php echo Url::to(['/h5/order/require-after-sale-service']);?>?oiid='+item.id">申请售后</a></dd>
                    </dl>
                    <div v-if="Object.keys(order.gift).length>0">
                        <div class="b_order_shop clearfix">
                            <div class="b_homeico">
                                <img src="/images/gift.png" style="width: 20px;height: 20px;"/>
                            </div>
                            <h5>{{order.gift.title}}</h5>

                        </div>
                        <div class="b_order_detail clearfix" >

                            <div class="b_good_img">
                                <img :src="order.gift.main_pic"  style="width: 60px;"/>
                            </div>
                            <div class="b_good_name">
                                <p>{{order.gift.name}}</p>
                                <span></span>
                            </div>
                            <div class="b_good_price">
                                <p>￥{{order.gift.price}}
                                    <span >x{{order.gift.amount}}</span>
                                </p>

                            </div>
                        </div>
                    </div>

                </div><!--div3-->
                <div class="div4">
                    <p v-if="order.fid == 0">支付方式：没有选择 </p> <p v-else>{{pay_method_map[order['finance_log']['pay_method']]}}</p>
                    <p>商品合计：￥{{order.goods_money}}</p>
                    <p v-if="order.is_coupon == 1">优惠券抵扣：{{order.coupon_money}}</p>
                    <p v-if="order.is_score == 0 && order.is_coupon == 0 ">已为您节省：￥{{order.self_buy_money}}</p>
                    <p v-if="order.is_score == 1">积分：{{order.score}}</p>
                    <p>运费：￥{{order.deliver_fee}}</p>
                </div><!--div4-->
                <div class="div5"><span>合计</span><span>￥{{order.amount_money}}</span></div>
                <div class="div6">
                    <p>订单编号：{{order.no}}</p>
                    <p>下单时间：{{order.create_time | timeFormat}}</p>
                </div>
                <div class="div6_bottom" v-if="order.status == 1 || order.status == 2 || order.user_remark != ''">
                    <p>
                        <span class="span1">买家留言：</span>
                        <a href="javascript:void(0);" class="span2 edit-remark" @click="saveRemark()"><img src="/images/address_edit_03.png"></a>
                    </p>
                    <div class="textarea">
                        <textarea v-if="order.status >= <?php echo Order::STATUS_PACKING;?>" disabled="disabled" v-model="order.user_remark"></textarea>
                        <textarea v-else placeholder="请输入128个以内的文字" @keyup='isWordOverrun()' v-model="order.user_remark"></textarea>
                    </div>
                </div>
            </div>
            <div class="div7">
                <dl class="left">
                    <dt><a :href="'<?php echo Url::to(['/h5/message/chat']);?>?sid='+order.shop.id+'&order_no='+order.no"><img src="/images/dingdan_18.png"></a></dt>
                    <dd><a :href="'<?php echo Url::to(['/h5/message/chat']);?>?sid='+order.shop.id+'&order_no='+order.no">联系卖家</a></dd>
                </dl>
                <dl class="right">
                    <dt><a :href="'tel:'+order.shop.service_tel"><img src="/images/dingdan_21.png"></a></dt>
                    <dd><a :href="'tel:'+order.shop.service_tel">拨打电话</a></dd>
                </dl>
            </div><!--div7-->
            <div class="dingdan_bottom">
                <ul v-if="order.status == <?php echo Order::STATUS_CREATED;?>">
                    <li><a href="javascript:void(0)" @click="selectAddress()">修改地址</a></li>
                    <li class="color"><a :href="'<?php echo Url::to(['/h5/order/pay']);?>?order_no='+order.no">去支付</a></li>
<!--                    <li class="color"><a href="javascript:void(0)" @click="deleteOrder()">删除订单</a></li>-->
                </ul>
                <ul v-if="order.status == <?php echo Order::STATUS_PAID;?>">
<!--                    <li class="color"><a href="javascript:void(0)" @click="hurryOrder()">催单</a></li>-->
<!--                    <li class="color"><a href="javascript:void(0)" @click="cancelOrder()">取消订单</a></li>-->
                </ul>
                <ul v-if="order.status == <?php echo Order::STATUS_DELIVERED;?>">
                    <li class="color"><a :href="'<?php echo Url::to(['/h5/order/deliver-info']);?>?order_no='+order.no">查看物流</a></li>
                    <li class="color"><a href="javascript:void(0)" @click="receivedOrder()">确认收货</a></li>
                </ul>
                <ul v-if="order.status == <?php echo Order::STATUS_RECEIVED;?>">
                    <li class="color"><a :href="'<?php echo Url::to(['/h5/order/comment']);?>?order_no='+order.no">评价</a></li>
                </ul>
                <ul v-if="order.status == <?php echo Order::STATUS_COMPLETE;?>">
                    <li class="color"><a :href="'<?php echo Url::to(['/h5/order/comment']);?>?order_no='+order.no">追加评价</a></li>
                    <li class="color"  v-if="order.is_coupon == 0 && order.pack_coupon_status == 0"><a :href="'<?php echo Url::to(['/h5/order/confirm']);?>?type=order&order_no='+order.no">再次购买</a></li>
                </ul>
                <ul v-if="order.status == <?php echo Order::STATUS_CANCEL;?>">
                    <li class="color" v-if="order.is_coupon == 0 && order.pack_coupon_status == 0"><a  :href="'<?php echo Url::to(['/h5/order/confirm']);?>?type=order&order_no='+order.no">再次购买</a></li>
<!--                    <li class="color"><a href="javascript:void(0)" @click="deleteOrder()">删除订单</a></li>-->
                </ul>
            </div><!--dingdan_bottom-->
        </div><!--dingdan_stay-->

        <div class="dingdan_stay_share">
            <div class="gb_resLay clearfix">
                <div class="bdsharebuttonbox">
                    <ul class="gb_resItms">
                        <li> <a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin" ></a>微信好友 </li>
                        <li> <a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq" ></a>QQ好友 </li>
                        <li> <a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone" ></a>QQ空间 </li>
                        <li> <a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina" ></a>新浪微博 </li>
                        <li> <a title="分享到朋友圈" href="#" class="bds_renren" data-cmd="renren" ></a>朋友圈 </li>
                    </ul>
                </div>
                <div class="clear"></div>
                <div class="gb_res_t"><span>取消</span><i></i></div>
            </div><!--kePublic-->
        </div><!--dingdan_stay_share-->
    </div>
    <address-list :address_list="address_list" v-on:choose="chooseAddress" id="choose_address" style="display:none;"></address-list>
</div><!--box-->
    <style>
        .layui-layer-btn {
            font-size: 14px;
        }
    </style>
<script>
    Vue.component('address-list', {
        template:
        '<ul>' +
        '    <li v-for="(address, address_index) in address_list">' +
        '        <input style="-webkit-appearance:radio;" type="radio" :id="\'a\'+address.id" v-model="choose_aid" class="address" :value="address.id" @click="emitChoose()">' +
        '        <label :for="\'a\'+address.id"><span class="title">{{address.name}} {{address.mobile}}</span></label><br />' +
        '        <span class="area">{{address.city.join(\' \')}} {{address.address}}</span>' +
        '    </li>' +
        '</ul>',
        data: function () {
            return {
                choose_aid: 0
            };
        },
        props: ['address_list'],
        updated: function () {
            if (this.choose_aid == 0) {
                this.choose_aid = this.address_list[0]['id'];
            }
        },
        methods: {
            emitChoose: function () {
                var self = this;
                self.$nextTick(function () {
                    var address = {};
                    this.address_list.forEach(function (_address) {
                        if (_address.id == self.choose_aid) {
                            address = _address;
                        }
                    });
                    this.$emit('choose', address);
                });
            }
        }
    });
    var app = new Vue({
        el: '#app',
        data: {
            order_status_map: <?php echo json_encode(KeyMap::getValues('order_status'));?>,
            pay_method_map: <?php echo json_encode(KeyMap::getValues('finance_log_pay_method'));?>,
            address_list: [],
            order: {
                address: {},
                shop: {},
                finance_log: {}
            } //订单信息
        },
        methods: {
            getOrder: function () {
                apiGet('<?php echo Url::to(['/api/order/detail']);?>', {order_no:'<?php echo Yii::$app->request->get('order_no');?>'}, function (json) {
                    if (callback(json)) {
                        app.order = json['order'];
                        app.getAddress();
                    }
                });
            },
            getAddress: function () {
                apiGet('<?php echo Url::to(['/api/user/address-list']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.address_list = json['address_list'];
                    }
                });
            },
            deleteOrder: function () {
                layer.confirm('确定要删除订单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index) {
                    apiGet('<?php echo Url::to(['/api/order/delete']);?>', {'order_no':app.order.no}, function (json) {
                        if (callback(json)) {
                            layer.close(layer_index);
                            window.location.href='<?php echo Url::to(['/h5/order'])?>';
                        }
                    });
                });
            },
            cancelOrder: function () {
                layer.confirm('确定要取消订单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/cancel']);?>', {'order_no':app.order.no}, function (json) {
                        layer.close(layer_index);
                        window.location.href='<?php echo Url::to(['/h5/order'])?>';
                    });
                });
            },
            receivedOrder: function () {
                layer.confirm('确定要确认收货吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/received']);?>', {'order_no':app.order.no}, function (json) {
                        if (callback(json)) {
                            layer.close(layer_index);
                            window.location.href='<?php echo Url::to(['/h5/order'])?>';
                        }
                    });
                });
            },
            hurryOrder: function () {
                layer.confirm('确定要催单吗？', {
                    title: '确认',
                    btn: ['确定', '取消']
                }, function(layer_index){
                    layer.close(layer_index);
                    apiGet('<?php echo Url::to(['/api/order/hurry']);?>', {'order_no':app.order.no}, function (json) {
                        if (callback(json)) {
                            layer.msg('催单成功', {icon: 6});
                        }
                    });
                });
            },
            saveRemark: function () {
                var remark = this.order.user_remark,
                    option = {'remark' : remark, 'order_no' : app.order.no};
                apiGet('<?php echo Url::to(['/api/order/save-remark'])?>', option, function(json){
                    if (callback(json)) {
                        layer.msg('买家留言已修改。', function () {});
                    }
                });
            },
            chooseAddress: function (address) {
                app.order.address = address;
            },
            selectAddress: function (){
                layer.open({
                    type: 1,
                    'content': $('#choose_address'),
                    'btn': ['确定', '新增收货地址', '取消'],
                    btn1: function (index) {
                        var address_id = $('.address:radio:checked').val();
                        app.updateOrderAddress(address_id);
                        layer.close(index);
                    },
                    btn2: function (index) {
                        window.location.href = '<?php echo Url::to(['/h5/user/edit-address']);?>?back=' + encodeURIComponent(window.location.href);
                        layer.close(index)
                    },
                    btn3: function (index) {
                        layer.close(index);
                    }
                });
            },
            updateOrderAddress: function (address_id) {
                apiGet('<?php echo Url::to(['/api/order/update-order-address']);?>', {'order_no':app.order.no, 'address_id':address_id}, function (json) {
                    if (callback(json)) {
                        app.order.deliver_fee = json['deliver_fee'];
                        app.order.amount_money = json['amount_money'];
                    }
                });
            },
            isWordOverrun: function () {
                if (this.order.user_remark.length > 128) {
                    this.order.user_remark = this.order.user_remark.substring(0, 128);
                }
            }
        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();
                var h = date.getHours();
                var m = date.getMinutes();
                var s = date.getSeconds();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }
                if (h < 10) {
                    h = '0' + h;
                }
                if (m < 10) {
                    m = '0' + m;
                }
                if (s < 10) {
                    s = '0' + s;
                }
                return y + '-' + M + '-' + d + ' ' + h + ':' + m + ':' + s;
            },
            cityFormat: function (city) {
                if (city !== undefined) {
                    return city.join(' ');
                }
                return '';
            }
        },
        created: function () {
            this.getOrder();
        }
    });
</script>
