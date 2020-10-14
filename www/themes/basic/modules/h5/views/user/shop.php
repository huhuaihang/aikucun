<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->title = '用户中心';
?>
<div class="box" id="app">
<header class="mall-header">
    <div class="mall-header-left">
        <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
    </div>
    <div class="mall-header-title">我的店铺</div>
    <div class="mall-header-right">
        <span style="font-size: .24rem; margin-right: 10px; color: #333;"class="tj_tan">说明</span>
    </div>
</header>
    <div style="height: 1.3rem;"></div>
<!--    <div class="tj">-->
<!--        <p>-->
<!--            <span>名额统计</span>-->
<!--        </p>-->
<!--        <div class="tj1">-->
<!--            <div>-->
<!--                已出售名额<br/>-->
<!--                <span>{{user.sale_count}}</span>-->
<!--            </div>-->
<!--            <div>-->
<!--                剩余名额<br/>-->
<!--                <span>{{user.prepare_count}}</span>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="tj_lk">-->
<!--            <a href="--><?php //echo Url::to(['/h5/user/sale']);?><!--">我要卖</a>-->
<!--        </div>-->
<!--    </div>-->
    <div class="shop_tj">
        <div class="shop_ty">
            <ul>
                <li>
                    <p>已出售礼包</p>
                    <p>{{user.sale_count}}个</p>
                </li>
                <li>
                    <p>剩余礼包</p>
                    <p>{{user.prepare_count}}个</p>
                </li>
                <li>
                    <p>手中囤货</p>
                    <p>{{user.hand_count}}个</p>
                </li>
            </ul>
        </div>
        <div class="shop_tr">
            <a href="<?php echo Url::to(['/h5/user/sale']);?>">
                <img src="/images/shop_d.png" alt="">
            </a>
        </div>
    </div>
    <div class="vip_pic_s" v-for="ad in ad_list">
        <a :href="ad.url"> <img :src="ad.img" alt=""></a>
    </div>
    <div style="height: .3rem;"></div>
    <div class="tj3">
<!--        <p><span>已售礼包记录</span></p>-->
        <div class="shop_op">
            <ul>
                <li :class="{'shop_ys':active==1}" @click="change(1)">已售的礼包</li>
                <li :class="{'shop_ys':active==2}" @click="change(2)">已购的礼包</li>
            </ul>
        </div>
        <div class="tj2" v-if="active==1">
            <ul>
                <li v-for="list in list">
                    <img :src="list['avatar']" alt="">
                    <p>{{list['real_name']}}<br/><span>{{list['create_time'] | datetimeFormat}}</span></p>
                    <p style="width: 60%;">{{list['mobile']}}<br/><span class="more_btn_sy">{{list['goods_title']}}</span> </p>
                </li>
            </ul>
            <div class="more_btn"   @click="tabClick"  v-if="more_btn">
                <a>
                    <div class="classify-s"><h2>点击加载更多</h2></div>
                </a>
            </div>
        </div>
        <div class="shop_yk"  v-if="active==2">
            <ul>
                <li v-for="log in buy_list">
                    <div class="shop_yk_z">
                        <p>{{log.title}}</p>
                        <p>订单号：{{log.no}}</p>
                        <p>数量：<span>{{log.amount}}</span></p>
                        <p>时间：{{log.create_time | datetimeFormat }}</p>
                    </div>
                    <div class="shop_yk_y">
                        <p>¥{{log.money}}</p>
                    </div>
                </li>

            </ul>
            <div class="more_btn"   @click="tabClick2"  v-if="more_btn2">
                <a>
                    <div class="classify-s"><h2>点击加载更多</h2></div>
                </a>
            </div>
        </div>
    </div>
    <div class="tj_ch">
        <div class="tj_kl">
            <h2>提示</h2>
            <p style="text-align: left" v-html="des"></p>
            <span class="tj_op">确定</span>
        </div>
    </div>
    </div><!--box-->

<script>
    var app = new Vue({
        el: '#app',
        data: {
            more_btn:false,
            more_btn2:false,
            active:1,
            user: {}, // 用户信息
            ad_list:[], //广告图
            list: [], // 已售记录列表
            buy_list: [], // 已购记录列表
            des:{},
            SearchForm: {
                page: 1
            }, // 搜索表单
            SearchForm2: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            page2: {}, // 已购分页
        },
        methods: {

            change: function (num) {
                this.active = num;
            },
            desc: function () {
                apiGet('/api/default/description', {name: 'shop'}, function (json) {
                    if (callback(json)) {
                        console.log(json)
                        app.des = json.des;
                    }
                });
            },
            getShopInfo: function () {
                apiGet('/api/user/my-shop', {}, function (json) {
                    if (callback(json)) {
                        console.log(json)
                        app.user = json['user'];
                        app.ad_list = json['ad_list'];
                    }
                });
            },

            getSaleLog:function(){
                // 获取已售礼包信息
                apiGet('<?php echo Url::to(['/api/user/sale-log']);?>', this.SearchForm, function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (log) {
                            app.list.push(log);
                        });
                        //分页
                        app.page = json['page']['pageCount'];
                        if (json['page']['pageCount'] > 1) {

                            app.more_btn=true;
                        }
                        else {
                            app.more_btn=false;
                        }
                        console.log(app.list)
                    }
                });
            },
            getBuyPackLog:function(){
                // 获取已购礼包信息
                apiGet('<?php echo Url::to(['/api/user/buy-pack-list']);?>', this.SearchForm2, function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (log) {
                            app.buy_list.push(log);
                        });
                        //分页
                        app.page2 = json['page']['pageCount'];
                        if (json['page']['pageCount'] > 1) {

                            app.more_btn2=true;
                        }
                        else {
                            app.more_btn2=false;
                        }
                        console.log(app.buy_list)
                    }
                });
            },

            tabClick: function () {
                if (app.SearchForm.page < app.page) {
                    app.SearchForm.page++;
                    app.getSaleLog();
                } else {
                    app.more_btn = false;
                    layer.msg('没有更多数据了。');
                }
            },
            //更多优品分页加载
            tabClick2: function () {
                if (app.SearchForm2.page < app.page2) {
                    app.SearchForm2.page++;
                    app.getBuyPackLog();
                } else {
                    app.more_btn2 = false;
                    layer.msg('没有更多数据了。');
                }
            },

        },

        filters: {

            datetimeFormat: function (timestamp) {
                var date = new Date(timestamp * 1000);
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
                return y + '-' + M + '-' + d ;
            }
        },
        mounted:function()
        {
            this.getShopInfo();
            this.getBuyPackLog();
            this.getSaleLog();
            this.desc();

        },
        created: function () {

        }
    });
    $(document).ready(function(){
        $(".tj_ch").height($(window).height());
        // $(".sj2").click(function(){
        //
        //
        //     $(".sj-t").show();
        //     $(".ts1").show();
        //     $(".ts2").hide();
        // });
        // $(".tsqd").click(function(){
        //     $(".ts2").show();
        //     $(".ts1").hide();
        // });
        $(".tj_tan").click(function(){
            $(".tj_ch").show();
        });
        $(".tj_op,.tj_ch").click(function(){
            $(".tj_ch").hide();
        });
    });
</script>
