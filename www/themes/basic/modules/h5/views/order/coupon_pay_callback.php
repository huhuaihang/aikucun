<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '优惠券活动支付成功页面';

?>
<div class="box" id="app">
<!--    <div class="new_header">-->
<!--        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>-->
<!--        <a href="#" class="a2">优惠券活动支付成功页面</a>-->
<!--    </div>-->
    <div class="callback_s">
        <img src="/images/callback2.png" alt="">
        <p>支付成功</p>
    </div>
    <div class="callback_w">
        <p>支付金额<span class="callback_w_p1">¥{{pay_money}}</span></p>
        <p>订单编号<span class="callback_w_p2">{{order_no}}</span></p>
    </div>
    <div class="callback_r">
        <div class="callback_t">
            <p>购买商品<a :href="'/h5/order/view?order_no='+order_no">查看订单详情</a></p>
        </div>
        <div class="callback_b"  v-if="Object.keys(goods).length>0" >
            <div class="callback_b_z">
                <img :src="goods.main_pic" alt="">
            </div>
            <div class="callback_b_y">
                <p>{{goods.title}}</p>
                <p style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;">{{goods.desc}}</p>
                <p>¥{{goods.price}}</p>
            </div>
        </div>
        <div class="callback_m"  v-if="Object.keys(gift).length>0">
            <img src="/images/callback3.png" alt="">
            <p>{{gift.title}}</p>
        </div>
        <div class="callback_tr" v-if="Object.keys(gift).length>0">
            <div class="callback_tr_z">
                <img :src="gift.main_pic" alt="">
            </div>
            <div class="callback_tr_y">
                <p>{{gift.name}}</p>
                <p></p>
                <p>¥{{gift.price}}</p>
            </div>
        </div>
    </div>
    <div class="callback_r" v-if="Object.keys(coupon).length>0">
        <div class="callback_t">
            <p>优惠券<a href="/h5/user/groud-push">点击查看优惠券</a></p>
        </div>
        <div class="callback_pr">
            <div class="callback_pr_z">
                <p>¥<span>{{coupon.price}}</span></p>
                <p>活动专用券</p>
            </div>
            <div class="callback_pr_h">
                <p>{{coupon.name}}</p>
                <p>获得时间：{{coupon.time | timeFormat }}</p>
            </div>
            <div class="callback_pr_y">
                <p>X{{coupon.num}}</p>
            </div>
        </div>
    </div>
    <div style="height: 1rem;"></div>
    <div class="callback_ykl" v-if="type == 1">
        <a href="<?php echo Url::to(['/h5/user/pack-coupon']);?>">查看我的卡券</a>
    </div>
    <div class="sj_yt"  v-if="type == 1" style="z-index: 999;" >
        <div class="callback_tyn">
            <div class="callback_quy">确定</div>
        </div>
        <div class="callback_qlm">
            <img src="/images/callback6.png" alt="">
        </div>
    </div>
</div>
<script>
    var order_no = Util.request.get('order_no');
    var app = new Vue({
        el: '#app',
        data: {
            pay_money: 0,
            goods:{},//商品信息
            gift:{},//赠品信息
            order_no:order_no,
            coupon:{},//优惠券信息
            type:0,//判断是否是礼包卡券商品 0否 1是
        },
        methods: {
          getFinance:function () {
              apiGet('<?php echo Url::to(['/api/order/finance']);?>', {'order_no':order_no}, function (json) {

                  if (callback(json)) {
                      app.goods=json['list']['goods'];
                      console.log(json)
                      if(json['list']['gift']!== undefined)
                      {
                          app.gift=json['list']['gift'];
                      }
                      if(json['list']['coupon']!== undefined)
                      {
                      app.coupon=json['list']['coupon'];
                      }
                      app.type=json['type'];
                      app.pay_money=json['money'];
                      app.$nextTick(function () {
                          $(".sj_yt").height($(window).height());

                          $(".callback_quy,.callback_qlm").click(function(){
                              $(".sj_yt").hide();
                          });
                      });
                  }
              });
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
                return y + '-' + M + '-' + d ;
            }
        },
        mounted:function () {

            this.getFinance();

        },
    });

</script>
