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
        <div class="mall-header-title">我要卖</div>
    </header>
    <div style="height: 1.3rem;"></div>
    <div class="sj">
        <input type="text" value=""  v-model="mobile" placeholder="输入手机号" >
    </div>
  <p class="sj1" style="display: none">{{message}}</p>
    <a class="sj2"   v-on:click="getinfo"  style="display: none"  >激活</a>
    <div class="sj-t">
<!--        核对弹窗-->
        <div class="sale_rt" style="display: none">
            <div class="sale_sd">
                <img src="/images/sale_g.png" alt="" class="tgb">
            </div>
            <div class="sale_po">
                请核对对方信息
            </div>
            <div class="sale_qw">
                <div class="sale_qw_e">
                    <img src="/images/vip_t.png" alt="">
                </div>
                <div class="sale_qw_r">
                    <p>真实姓名：{{real_name}}</p>
                    <p>昵称：{{nickname}}</p>
                    <p>手机号：{{mobile}}</p>
                </div>
            </div>
            <div class="sale_lk">
                <ul>
                    <li>
                        <a v-if="hand_count==0" :href="'./sale-pack?mobile='+mobile">为TA下单</a>
                        <a v-if="hand_count>0" @click="layer_confirm">为TA下单</a>
                    </li>
                    <li class="tgb">
                        <a href="#">否</a>
                    </li>
                </ul>
            </div>
        </div>
<!--        提示弹窗-->
        <div class="sale_ti" style="display: none">
            <div class="sale_ti_t">
                <h2>提示</h2>
            </div>
            <div class="sale_ti_b">
                <div class="sale_ti_z">
                    <p>经平台检测，您手中尚有{{hand_count}}个会员大礼包（手中真实囤货），激活该粉丝后您的“手中囤货”数量-1。激活完毕后请及时将大礼包发到该粉丝手中！</p>
                </div>
                <div class="sale_ti_x">
                    <ul>
                        <li @click="active_user">
                            <a href="#">激活</a>
                        </li>
                        <li class="sale_ti_g">
                            <a href="#">否</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
<!--        激活成功提示弹窗-->
        <div class="sale_pl" style="display: none">
            <div class="sale_pl_w">
                <img src="/images/sale_bei4.png" alt="" class="sale_pl_img">
                <p>恭喜您成功激活新粉丝</p>
            </div>
            <div class="sale_pl_e">
                <p>姓名：{{real_name}}</p>
                <p>昵称：{{nickname}}</p>
                <p>手机号：{{mobile}}</p>
            </div>
            <div class="sale_pl_q">
                <a href="/h5/user/shop">查看店铺</a>
            </div>
        </div>
<!--        <div class="ts2">-->
<!--            <img class="tgb2" src="/images/popover1.png" alt="">-->
<!--            <p>友情提示</p>-->
<!--            <p  style="text-align: center; font-size: 14px;">激活成功！</p>-->
<!--            <a class="txd">确定</a>-->
<!--        </div>-->
    </div>
</div><!--box-->
<style>
    .layui-layer-dialog{
       display: none;
    }
</style>
<script>
    var mobile ='';
    var nick_name ='';
    var real_name ='';
    if(Util.request.get('mobile'))
    {
        mobile=Util.request.get('mobile');
        nick_name=Util.request.get('nick_name');
        real_name=Util.request.get('real_name');

    }
    var app = new Vue({
        el: '#app',
        data: {
            mobile:mobile,
            real_name:real_name,
            nickname:nick_name,
            message:'',
            hand_count:'',

        },
        methods: {
            getinfo: function () {
                // 获取用户信息
                apiPost('<?php echo Url::to(['/api/user/check-child-mobile']);?>', {mobile:this.mobile}, function (json) {
                    if (callback(json)) {

                        app.mobile = json['mobile'];
                        app.real_name = json['real_name'];
                        app.nickname = json['nickname'];
                        app.hand_count = json['hand_count'];

                        app.$nextTick(function () {
                            $(".sj-t").show();
                            $(".sale_rt").show();

                        });

                    }
                });

            },

            active_user:function () {

                // 激活会员用户
                apiPost('<?php echo Url::to(['/api/user/active-user-new']);?>', {mobile:this.mobile}, function (json) {

                    if (callback(json)) {
                        $(".sale_pl").show();

                        $(".sale_rt").hide();
                        $(".sale_ti").hide();
                        $(".sj2").hide();
                        console.log(json)
                    }
                });
            },
           layer_confirm:function () {
               $(".sale_rt").hide();
               $(".sale_ti").show();
           },


        },
        mounted:function(){
            if(Util.request.get('mobile'))
            {
                $(".sj-t").show();
                $(".sale_pl").show();
            }

        },
        watch: {

            mobile (value) {
                var reg=11 && /^((13|14|15|17|18)[0-9]{1}\d{8})$/;
                if (value.length >= 11) {
                    if(!reg.test(value)) {
                        app.message='手机号格式不正确';
                        $(".sj1").show();
                        return false;
                    }
                    else
                    {
                        // 获取用户信息
                        apiPost('<?php echo Url::to(['/api/user/check-child-mobile']);?>', {mobile:value}, function (json) {

                            if (callback(json)) {
                                app.message=json['real_name'];
                                $(".sj1").show();
                                $(".sj2").show();
                            }
                            else
                            {
                                app.message=json['message'];
                                $(".sj1").show();
                            }
                        });

                    }

                    } else {
                    $(".sj2").hide();
                    $(".sj1").hide();
                         }

                 }
        },



    });
    $(document).ready(function(){
        $(".sj-t").height($(window).height());
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
        $(".tsqx,.sale_ti_g").click(function(){
            $(".sj-t").hide();
            $(".sale_ti").hide();
            $(".sale_rt").hide();
            $(".sale_pl").hide();
        });
        $(".sale_pl_img").click(function(){
           window.location.href='/h5/user/sale';
        });

        $(".txd").click(function(){
            window.history.back(-1);
        });
        $(".tgb2").click(function(){
            window.history.back(-1);
        });
        $(".tgb").click(function(){
            $(".sj-t").hide();
            $(".sale_ti").hide();
            $(".sale_rt").hide();
            $(".sale_pl").hide();
        });
    });
</script>
