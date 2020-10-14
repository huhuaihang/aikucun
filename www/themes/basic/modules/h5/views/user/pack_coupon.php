<?php
/**
 * @var $this \yii\web\View
 */
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
$this->title = '我的礼包卡券';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">我的卡券</div>
        <div class="mall-header-right" style="z-index:9999;position: relative">
            <a href="/h5/goods/pack-coupon-view"> <span style="font-size: .24rem; margin-right: 10px; color: #333;"class="tj_tan">使用规则</span></a>
        </div>
    </header>
    <div class="coupon_s">
        <div class="coupon_r">
            <ul>
                <li @click="select(1)">
                    <p :class="{'coupon_p1':SearchForm.status==1,'coupon_p2':SearchForm.status!=1}">未使用</p>
                </li>
                <li @click="select(2)">
                    <p :class="{'coupon_p1':SearchForm.status==2,'coupon_p2':SearchForm.status!=2}">已使用</p>
                </li>
                <li @click="select(0)">
                    <p :class="{'coupon_p1':SearchForm.status==0,'coupon_p2':SearchForm.status!=0}">已过期</p>
                </li>
            </ul>
        </div>
        <div class="coupon_y">
            <ul>
                <li v-for="coupon in pack_coupon_list">
                    <div class="coupon_ty">
                        <h2>{{coupon.name}}</h2>
                        <p>{{coupon.desc}}</p>
                        <p>有效时间：{{coupon.over_time }}</p>
                    </div>
                    <div class="coupon_pl" v-if="coupon.status==1">
                        <p><a :href="'/h5/user/sale-pack?coupon_id='+coupon.id">立即换购</a></p>
                    </div>
                    <div class="coupon_pl"  v-if="coupon.status==2">
                        <p>已使用</p>
                    </div>
                    <div class="coupon_pl"  v-if="coupon.status==0">
                        <p>已过期</p>
                    </div>
                </li>

            </ul>
        </div>
    </div>

</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            pack_coupon_list: [], // 卡券列表
            SearchForm: {
                status:1,
                page: 1,
            },
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {
            select:function(status){
              this.SearchForm.status=status;
                this.loadUser();
            },
            loadUser: function () {

                apiGet('/api/user/package-coupon', this.SearchForm, function (json) {
                    if (callback(json)) {
                        app.pack_coupon_list = json['list'];

                    }
                    console.log(app.pack_coupon_list)
                });

            },

        },
        filters: {
            numberToInt: function (value) {
                return parseInt(value);
            },
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
        mounted: function () {
            this.loadUser();

        },
        updated:function () {


        }
    });

</script>