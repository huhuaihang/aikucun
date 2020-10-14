<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\System;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '确认订单';
?>
<style>
    .layui-layer-content{padding: .3rem;font-size: 16px;}
    .layui-layer-dialog .layui-layer-content{font-size: 16px;}
    .layui-layer-content li{margin-bottom: .2rem;}
    .layui-layer-btn0{border-color: #cc1000 !important;background-color: #cc1000 !important;}
    .layui-layer-btn1{border:none !important;background-color: #999 !important; color: #fff !important;}
    .layui-layer-btn2{border:none !important;background-color: #999 !important; color: #fff !important;}
    .layui-layer-title{background: #cc1000 !important; color: #fff !important; width: 100%; text-align: center; padding: 0 !important;}
    .b_liuyan{ width: 100%; height: .8rem; background-color: #fff; display: block; padding: 0 .48rem; box-sizing: border-box; margin-bottom: 2rem;}
    .b_liuyan span{ width: 25%; height: .8rem; display: block; float: left; font-size: .28rem; font-family: 'Microsoft Yahei'; line-height: .8rem; color: #444;}
    .b_liuyan input{ width: 75%; height: .8rem; display: block; float: right;font-size: .28rem; font-family: 'Microsoft Yahei'; line-height: .8rem; color: #333; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;}
</style>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">确认订单</div>
    </header>
    <!--确认订单-->
    <div class="container">
        <a class="b_bang_phone clearfix b_magt1" href="<?php echo Url::to(['/h5/user/edit-address']);?>" v-if="address_list.length == 0">
            <div class="mizi">
                <p class="p1">没有设置默认地址。</p>
                <p class="p2"></p>
            </div>
            <input type="hidden" name="address_id" id="address_id">
        </a>
        <a class="b_bang_phone clearfix b_magt1" href="javascript:void(0)" @click="selectAddress()" v-if="address_list.length > 0">
            <div class="mizi">
                <p class="p1">{{deliver_address['name']}}<span>{{deliver_address['mobile']}}</span></p>
                <p class="p2">{{deliver_address['city'].join(' ')}} {{deliver_address['address']}}</p>
            </div>
            <input type="hidden" name="address_id" id="address_id" :value="deliver_address['id']">
        </a>
        <div class="b_magt1">
            <div class="b_order_shop clearfix">
                <div class="b_homeico">
                    <img src="/images/b_homedian_03.png"/>
                </div>
                <h5>{{shop['name']}}</h5>
            </div>
            <div v-for="(item, item_index) in item_list">
                <div class="b_order_detail clearfix">
                    <div class="b_good_img">
                        <img :src="item['goods']['main_pic']"/>
                    </div>
                    <div class="b_good_name">
                        <p style=""><img v-if="item['goods']['is_supplier']==1" src="/images/zhifa.png" alt="">{{item['goods']['title']}}</p>
                        <span>{{item['sku']['key_name']}}</span>
                    </div>
                    <div class="b_good_price">
                        <p v-if="item['sku']['price']">￥{{item['sku']['price']}}</p>
                        <p v-else>￥{{item['goods']['price']}}</p>
                    </div>
                    <ul class="b_pprice b_pprice_add">
                        <li>
                            <div class="b_minus_plus clearfix" style="border: none">

<!--                                <span class="b_minus" @click="addAmount(item_index, -1)">-</span>-->
                                <span class="b_number" style="margin-left: .9rem">  ×{{item['amount']}}</span>
<!--                                <span class="b_plus" @click="addAmount(item_index, 1)">+</span>-->
                            </div>
                        </li>
                    </ul>
                </div>
                  <!-- 活动赠品-->
                <div  v-if="Object.keys(gift_info).length>0">
                    <div class="b_order_shop clearfix">
                        <div class="b_homeico" style="width:25px;height: 25px;margin-top: .2rem">
                            <img src="/images/gift.png"/>
                        </div>
                        <h5>{{gift_info.title}}</h5>
                    </div>

                    <div class="b_order_detail clearfix">
                    <div class="b_good_img">

                        <img :src="gift_info.main_pic"/>
                    </div>
                    <div class="b_good_name">
                        <p style="">{{gift_info.name}}</p>
                        <p  style="color: #ff2222">￥{{gift_info.price}}</p>
                    </div>
                    <div class="b_good_price">

                    </div>
                        <ul class="b_pprice b_pprice_add">
                            <li>
                                <div class="b_minus_plus clearfix" style="border: none">

<!--                                    <span class="b_number" style="margin-left: .9rem">  ×1</span>-->

                                </div>
                            </li>
                        </ul>
                </div>
                </div>
                <input type="hidden" class="goods_id" name="goods_id[]" :value="item['goods']['id']">
                <input type="hidden" class="amount" name="amount[]" :value="item['amount']">
                <input type="hidden" class="goods_list" :value="item['goods']['id']+'-'+item['amount']">
            </div>
        </div>
        <ul class="b_pprice b_magt1">
            <li v-if="coupon_money > 0">
                <p class="p1">活动优惠金额</p>
                <p class="p2">{{coupon_money}}</p>
            </li>
            <li v-if="is_use_score == 1">
                <p class="p1">积分抵扣<span style="color: #999;">(100积分=<?php echo System::getConfig('score_ratio');?>元)</span></p>
                <p class="p2">{{total_score}}</p>
            </li>
            <li>
                <p class="p1">小计</p>
                <p class="p2">￥{{getGoodsAmountMoney() - score_amount_price - all_self_price - coupon_money | moneyFormat}}</p>
            </li>
<!--            <li v-if="is_use_score == 1">-->
<!--                <p class="p1">已为您节省</p>-->
<!--                <p class="p2">￥{{score_amount_price}}</p>-->
<!--            </li>-->
            <li v-if="is_use_score == 0 && coupon_money == 0 && gift_id ==''">
                <p class="p1">已为您节省</p>
                <p class="p2"> ￥{{getlsPrice()}}</p>
            </li>
            <!--
            <li>
                <p class="p1">积分抵现</p>
                <p class="p2 b_color_red">-￥1.00</p>
            </li>
            -->
            <li>
                <p class="p1">配送费用</p>
                <p class="p2 b_color_red fee">{{deliver_fee | moneyFormat}}</p>
            </li>
            <li>
                <p class="p2">共计：<span class="goods_money">￥{{getGoodsAmountMoney() + deliver_fee - score_amount_price - all_self_price - coupon_money| moneyFormat}}</span></p>
            </li>
        </ul>
        <label class="b_liuyan clearfix">
            <span>买家留言：</span>
            <input type="text" v-model="remark" name="user_remark" placeholder="选填，对本次交易的说明（或与卖家达成的协议）"/>
        </label>
        <div class="b_fukuan">
            <p class="amount_money">应付:￥{{getGoodsAmountMoney() + deliver_fee - score_amount_price - all_self_price - coupon_money | moneyFormat}}</p>
            <button @click="saveOrder()" class="pay" v-show="deliver_ok">付款</button>
            <button style="background-color: #DCDCDC" class="gay" v-show="!deliver_ok">付款</button>
        </div>
    </div>
    <address-list :address_list="address_list" v-on:choose="chooseAddress" id="choose_address" style="display:none;"></address-list>
</div>
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
        data: function () {
            return {
                address_list: [],
                shop: {},
                item_list: [],
                deliver_address: {},
                deliver_fee: 0,
                remark: '',
                deliver_ok: false,
                all_self_price: 0,
                score_amount_price: 0,
                coupon_money:0,
                self_amount_price: 0,
                gift_id:'<?php echo  Yii::$app->request->get('gift_id'); ?>',
                gift_info:[],
                total_score: 0,
                is_use_score: '<?php echo $is_use_score = Yii::$app->request->get('is_use_score'); ?>'
            };
        },
        methods: {
            /**
             * 获取地址列表
             */
            getAddress: function () {
                apiGet('<?php echo Url::to(['/api/user/address-list']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.address_list = json['address_list'];
                        app.deliver_address = json['address_list'].length > 0 ? json['address_list'][0] : {};
                        app.getOrder();
                    }
                });
            },
            /**
             * 获取订单内容列表
             */
            getOrder: function () {
                apiGet('<?php echo Url::to(['/api/order/confirm']);?>', Util.request.get(), function (json) {
                  if(json['error_code'] === 30001)
                  {
                     setTimeout(function () {
                       window.location.href="<?php echo Url::to(['/h5/order']);?>";
                     },2000);
                  }
                    if (callback(json)) {
                        app.shop = json['shop'];
                        app.item_list = json['item_list'];
                        app.score_amount_price = json['score_amount_money'];
                        app.self_amount_price = json['self_amount_money'];
                        app.total_score = json['total_score'];
                        app.coupon_money = json['coupon_money'];
                        app.gift_info =  json['gift_info'];
                        console.log(json)
                        app.getDeliverFee();
                    }
                });
            },
            /**
             * 获取物流费用
             */
            getDeliverFee: function () {
                var goods_list = [];
                app.item_list.forEach(function (item) {
                    goods_list.push({
                        gid: item['goods']['id'],
                        amount: item['amount']
                    });
                });
                if (app.deliver_address['area'] == undefined) {
                    app.deliver_address['area'] = '110101';
                }
                var json = {
                    area: app.deliver_address['area'],
                    goods_list: goods_list
                };
                apiPost('<?php echo Url::to(['/api/goods/get-multi-goods-express']);?>', json, function (json) {
                    if (callback(json)) {
                        app.deliver_fee = json['fee'];
                        app.deliver_ok = true;
                    } else {
                        app.deliver_ok = false;
                    }
                });
            },
//             /**
//              * 获取商品价格
//              * @param goods 商品信息
//              * @param sku 规格信息
//              * @return float
//              */
//             getFinalPrice: function (goods, sku) {
//                 var price = goods['price'];
//                 var price = goods['price'] - goods['self_price'];
//                 if (goods['slef_price'] != undefined && goods['slef_price'] != null) {
//                     app.all_self_price = app.all_self_price + goods['self_price'];
//                 }
//                 if (sku != undefined && sku != null && sku['key_name'] != undefined) {
//                     price = sku['price'];
// //                    price = sku['price'] - sku['self_price'];
//                     if (sku['self_price'] != undefined && sku['self_price'] != null) {
//                         app.all_self_price = sku['self_price'];
//                     }
//                 }
//                 //price = price.toFixed(2);
//                 return price;
//             },
            /**
             * 获取商品价格
             * @param goods 商品信息
             * @param sku 规格信息
             * @return float
             */
            getFinalPrice: function (goods, sku) {
                var price = goods['price'];
                var price = goods['price'] - goods['self_price'];

                if (sku != undefined && sku != null && sku['key_name'] != undefined) {

                    price = sku['price'];
                    price = sku['price'] - sku['self_price'];

                }
                var price=price.toFixed(2)
                return price;
            },
            /**
             * 获取立省价格
             * @param goods 商品信息
             * @param sku 规格信息
             * @return float
             */
            getlsPrice: function () {
                var all_self_price=0;

                this.item_list.forEach(function (item) {

                    var goods=item['goods'];
                    var sku=item['sku'];
                    console.log(goods);
                    if (sku['id'] != '' && sku['id']!=undefined ) {
                        price = sku['price'];
                        price = sku['price'] - sku['self_price'];
                        if (sku['self_price'] != undefined && sku['self_price'] != null) {

                            all_self_price += sku['self_price']*item['amount'];
                        }
                        goods['self_price']=0;
                    }

                    if (goods['self_price'] != undefined && goods['self_price'] != null) {
                        all_self_price += goods['self_price']*item['amount'];
                    }

                });

                all_self_price = parseFloat(all_self_price).toFixed(2)
                return all_self_price;

            },

            /**
             * 获取商品总价
             */
            getGoodsAmountMoney: function () {
                var amountMoney = 0;

                this.item_list.forEach(function (item) {
                    amountMoney += app.getFinalPrice(item['goods'], item['sku']) * item['amount'];
                });
                return amountMoney;
            },
//             /**
//              * 获取商品总价
//              */
//             getGoodsAmountMoney: function () {
//                 var amountMoney = 0;
//                 this.item_list.forEach(function (item) {
//                     amountMoney += app.getFinalPrice(item['goods'], item['sku']) * item['amount'];
//                 });
// //                if (amountMoney <= 0) {
// //                    layer.msg('规格金额不对。', function () {});
// //                }
//                 return amountMoney;
//             },
            /**
             * 增加数量
             * @param item_index 位置索引
             * @param add integer 需要增加的数量
             */
            addAmount: function (item_index, add) {
                var amount = this.item_list[item_index]['amount'];
                amount = parseInt(amount) + parseInt(add);
                if (amount <= 0) {
                    amount = 1
                }
                Vue.set(this.item_list[item_index], 'amount', amount);
            },
            selectAddress: function () {
                layer.open({
                    type: 1,
                    'title':'收货地址列表',
                    'content': $('#choose_address'),
                    'btn': ['确定', '新增收货地址', '取消'],
                    btn1: function (index) {
                        app.getDeliverFee();
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
            chooseAddress: function (address) {
                app.deliver_address = address;
            },
            saveOrder: function () {
                if (this.deliver_address == undefined || this.deliver_address.id == undefined) {
                    layer.msg('必须有一个收货地址。', function () {});
                    return;
                }
                if (this.remark.length > 125) {
                    layer.msg('留言內容不能超过125个字', function () {});
                }
                var params = {};
                switch (Util.request.get('type')) {
                    case 'cart': // 购物车
                        var cart = [];
                        this.item_list.forEach(function (item) {
                            var gid = item['goods']['id'];
                            var amount = item['amount'];
                            var sku_key_name = item['sku'] == undefined || item['sku']['key_name'] == undefined ? '' : item['sku']['key_name'];
                            cart.push(gid  + '^^' + amount + '^^' + sku_key_name);
                        });
                        params['type'] = 'cart';
                        params['cart'] = cart.join('$$$');
                        break;
                    case 'goods': // 直接购买商品
                        params['type'] = 'goods';
                        params['gid'] = this.item_list[0]['goods']['id'];
                        params['sku_key_name'] = this.item_list[0]['sku'] == undefined || this.item_list[0]['sku']['key_name'] == undefined ? '' : this.item_list[0]['sku']['key_name'];
                        params['amount'] = this.item_list[0]['amount'];
                        params['gift_id'] = this.gift_id;
                        break;
                    case 'order': // 订单重复购买
                        var cart = [];
                        this.item_list.forEach(function (item) {
                            var gid = item['goods']['id'];
                            var amount = item['amount'];
                            var sku_key_name = item['sku'] == undefined || item['sku']['key_name'] == undefined ? '' : item['sku']['key_name'];
                            cart.push(gid  + '^^' + amount + '^^' + sku_key_name);
                        });
                        params['type'] = 'cart';
                        params['cart'] = cart.join('$$$');
                        break;
                }
                params['sid'] = this.shop.id;
                params['remark'] = this.remark;
                params['address_id'] = this.deliver_address['id'];
                params['is_use_score'] = this.is_use_score;
                if (this.deliver_address['id'] == undefined) {
                    layer.msg('必须有一个收货地址。', function () {});
                    return;
                }
                layer.open({
                    'title':'确认地址提示',
                    'content': '请确认您的收货地址是否正确',
                    'btn': ['确定', '修改收货地址', '取消'],
                    btn1: function (index) {
                        apiGet('<?php echo Url::to(['/api/order/save']);?>', params, function (json) {
                            if (callback(json)) {
                                window.location.href = '<?php echo Url::to(['/h5/order/pay']);?>?order_no=' + json['order']['no'];
                            }
                        });
                        layer.close(index);
                    },
                    btn2: function (index) {
                        app.selectAddress();
                        layer.close(index)
                    },
                    btn3: function (index) {
                        layer.close(index);
                    }
                });

            },

        },
        filters: {
            moneyFormat: function (money) {
                return money.toFixed(2);
            }
        },
        created: function () {
            this.getAddress();
        }
    });
</script>
