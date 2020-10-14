<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $shop_cart_list array(sid => \app\models\UserCart[])
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '购物车';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">购物车</div>
    </header>
    <div class="container" id="app">
        <div class="b_market b_magt1">
            <div v-if="items.length===0" class="cart_11">
                <img src="/images/cart11.png" alt="">
                <p>去添加点什么吧~~</p>
                <a href="<?php echo Url::to(['/h5']);?>">马上购物</a>
            </div>
            <div class="shop-group-item" v-for="(item, shop_index) in items">
                <div class="shop-name">
                    <input type="checkbox" class="check goods-check shopCheck" v-model="checked_sid" :value="item.shop.id">
                    <h4><a :href="'<?php echo Url::to(['/h5/shop/view']);?>?id='+item.shop.id">{{item.shop.name}}</a></h4>
                </div>
                <ul class="goods_item">
                    <li v-for="(cart, cart_index) in item.cart_list">
                        <div class="shop-info">
                            <a href="#">
                                <input type="checkbox" class="check goods-check goodsCheck" v-model="checked_id" :value="cart.gid+'^^'+cart.sku_key_name">
                            </a>
                            <div class="shop-info-img">
                                <a :href="'<?php echo Url::to(['/h5/goods/view']);?>?id='+cart.gid">
                                    <img :src="cart.goods.main_pic" />
                                </a>
                            </div>
                            <div class="shop-info-text">
                                <p class="edit_order" @click="editCart(cart.gid, cart.sku_key_name)">编辑</p>
                                <h4><img v-if="cart.goods.is_supplier==1" src="/images/zhifa.png" alt=""><a :href="'<?php echo Url::to(['/h5/goods/view']);?>?id='+cart.gid">{{cart.goods.title}}</a></h4>
                                <div class="shop-brief">{{cart.sku_key_name}}</div>
                                <div class="shop-price">
                                    <div class="shop-pices">￥<b class="price">{{cart.price}}</b></div>
                                    <div class="shop-arithmetic">
                                        <a @click="addAmount(shop_index, cart_index, -1)" class="minus">-</a>
                                        <span class="num" >{{cart.amount}}</span>
                                        <a @click="addAmount(shop_index, cart_index, 1)" class="plus">+</a>
                                    </div>
                                </div>
                                <div class="edit_delate clearfix" v-show="on_edit_items['cart.'+cart.gid+'^^'+cart.sku_key_name]">
                                    <p class="shanchu" @click="deleteCart(shop_index, cart_index)">删除</p>
                                    <p class="wancheng" @click="closeEdit(cart.gid, cart.sku_key_name)">完成</p>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="shopPrice">本店总计：￥<span class="shop-total-amount ShopTotal">{{shopTotal(shop_index) | moneyFormat}}</span></div>
            </div>
        </div>
        <div class="payment-bar" v-if="items.length>0">
            <div class="all-checkbox"><input type="checkbox" class="check goods-check" v-model="check_all">全选</div>
            <div class="shop-total">
                <p>总价：<span class="total" id="AllTotal">{{cartTotal() | moneyFormat}}</span></p>
                <span>优惠：0元</span>
            </div>
            <a @click="check()" class="settlement">结算</a>
        </div>
    </div>
    <?php echo $this->render('../layouts/_bottom_nav');?>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            items: [],
            cart_money: 0,
            checked_sid: [],
            on_edit_items: [],
            checked_id: [],
            check_all: false
        },
        watch: {
            checked_sid: function (new_checked_sid) {
                // 限制同时只能选中一个店铺
                if (new_checked_sid.length > 1) {
                    layer.msg('结算只能选择同一店铺下的商品。');
                    new_checked_sid.splice(1, 1);
                    return;
                }
                // 自动选中店铺下面的所有商品
                if (this.checked_sid.length > 0) {
                    this.checked_id = [];
                    this.items.forEach(function (item) {
                        if (item['shop']['id'] == new_checked_sid) {
                            item['cart_list'].forEach(function (cart) {
                                app.checked_id.push(cart.gid+'^^'+cart.sku_key_name);
                            });
                        }
                    });
                }
                // 取消选中店铺下的商品
                if (new_checked_sid.length === 0) {
                    this.checked_id = [];
                    this.check_all = false;
                }
            },
            check_all: function (check_all) {
                if (check_all) {
                    this.checked_sid = [this.items[0]['shop']['id']];
                    this.checked_id = [];
                    this.items[0]['cart_list'].forEach(function (cart) {
                        app.checked_id.push(cart['gid']+'^^'+cart['sku_key_name']);
                    });
                } else {
                    this.checked_sid = [];
                    this.checked_id = [];
                }
            },
            checked_id: function (new_checked_id) {
                if (new_checked_id.length <= 1) {
                    return;
                }
                // 不能同时选中两个店铺的商品
                var checked_sid = 0;
                this.items.forEach(function (item) {
                    item['cart_list'].forEach(function (cart) {
                        if (checked_sid === 0 && app.checked_id.indexOf(cart.gid+'^^'+cart.sku_key_name) > -1) {
                            checked_sid = item['shop']['id'];
                        }
                    });
                });
                if (checked_sid > 0) {
                    var new_sid = 0;
                    var new_id = new_checked_id[new_checked_id.length - 1];
                    this.items.forEach(function (item) {
                        item['cart_list'].forEach(function (cart) {
                            if (cart.gid+'^^'+cart.sku_key_name == new_id) {
                                new_sid = item['shop']['id'];
                            }
                        });
                    });
                    if (new_sid != checked_sid) {
                        layer.msg('不能同时选中两个店铺的商品。');
                        this.checked_id.pop();
                    }
                }
            }
        },
        methods: {
            /**
             * 加载数据
             */
            loadData: function () {
                apiGet('<?php echo Url::to(['/api/cart/list']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.cart_money = 0;
                        app.checked_sid = [];
                        app.on_edit_items = [];
                        app.checked_id = [];
                        app.check_all = false;
                        app.items = json['shop_cart_list'];
                    }
                });
            },
            /**
             * 计算店铺金额总计
             */
            shopTotal: function (shop_index) {
                var total = 0;
                this.items[shop_index]['cart_list'].forEach(function (cart) {
                    total += cart.price * cart.amount;
                });
                return total;
            },
            /**
             * 计算选中金额总计
             */
            cartTotal: function () {
                var total = 0;
                this.items.forEach(function (item) {
                    item['cart_list'].forEach(function (cart) {
                        if (app.checked_id.indexOf(cart.gid+'^^'+cart.sku_key_name) > -1) {
                            total += cart.price * cart.amount;
                        }
                    });
                });
                return total;
            },
            /**
             * 编辑
             */
            editCart: function (gid, sku_key_name) {
                Vue.set(this.on_edit_items, 'cart.'+gid+'^^'+sku_key_name, true);
            },
            /**
             * 关闭编辑
             */
            closeEdit: function (gid, sku_key_name) {
                Vue.set(this.on_edit_items, 'cart.'+gid+'^^'+sku_key_name, false);
            },
            /**
             * 删除
             */
            deleteCart: function (shop_index, cart_index) {
                apiGet('<?php echo Url::to(['/api/cart/delete'])?>', {
                    gid: this.items[shop_index]['cart_list'][cart_index]['gid'],
                    sku_key_name: this.items[shop_index]['cart_list'][cart_index]['sku_key_name']
                }, function (json) {
                    if (callback(json)) {
                        app.loadData();
                    }
                });
            },
            /**
             * 增加数量
             */
            addAmount: function (shop_index, cart_index, amount) {
                var _amount = parseInt(this.items[shop_index]['cart_list'][cart_index]['amount']) + parseInt(amount);
                if (_amount <= 0) {
                    layer.confirm('删除这个商品吗？', {
                        btn: ['确定', '取消']
                    }, function (layer_index) {
                        app.deleteCart(shop_index, cart_index);
                        layer.close(layer_index);
                    }, function () {
                        return true;
                    });
                    return;
                }
                apiGet('<?php echo Url::to(['/api/cart/add']);?>', {
                    gid: this.items[shop_index]['cart_list'][cart_index]['gid'],
                    sku_key_name: this.items[shop_index]['cart_list'][cart_index]['sku_key_name'],
                    amount: amount
                }, function (json) {
                    if (callback(json)) {
                        Vue.set(app.items[shop_index]['cart_list'][cart_index], 'amount', json['amount']);
                    }
                });
            },
            /**
             * 结算
             */
            check: function () {
                if (this.checked_id.length <= 0) {
                    return;
                }
                var sid = 0;
                var order_cart = [];
                var supplier=0;
                this.items.forEach(function (item) {
                    item['cart_list'].forEach(function (cart) {

                        if(cart.goods.is_supplier==1)
                        {
                            supplier++;
                        }
                        if (app.checked_id.indexOf(cart.gid+'^^'+cart.sku_key_name) > -1) {
                            sid = item['shop']['id'];
                            order_cart.push(cart.gid + '^^' + cart.amount + '^^' + cart.sku_key_name);
                        }
                    });
                });
                if(supplier>0)
                {
                    alert('此订单含有厂家直发产品，如需申请售后，请按商品单独申请售后服务')
                }
              window.location.href = '<?php echo Url::to(['/h5/order/confirm']);?>?type=cart&sid=' + sid + '&cart=' + encodeURIComponent(order_cart.join('$$$'));
            }
        },
        mounted: function () {
            this.loadData();
        },
        filters: {
            moneyFormat: function (money) {
                return money.toFixed(2);
            }
        }
    });
</script>
