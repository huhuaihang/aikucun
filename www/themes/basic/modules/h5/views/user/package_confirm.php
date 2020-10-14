<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;
use yii\web\View;
use app\models\FinanceLog;
use app\models\WeixinMpApi;
use yii\base\Exception;
/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);
$api = new WeixinMpApi();
$this->registerJsFile('https://res.wx.qq.com/open/js/jweixin-1.2.0.js', ['position' => View::POS_HEAD]);
$config = $api->jsWxConfig(Url::current([], true));
$this->title = '套餐卡确认页面';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">确认信息</div>
    </header>
    <div style="height: 1.3rem;"></div>
    <div class="package_s">
        <p>选择套餐</p>
    </div>
    <div class="package_kj">
        <ul>
            <li v-for="(package,index) in package_list" :class="'package_li'+(index+1)"  @click="check_package_id(package.id,package.package_price,package.name)">
                <div class="package_ty">
                    <p>{{package.name}}</p>
                    <p>¥{{package.package_price}}<span>原价：¥{{package.price}}</span></p>
                </div>
                <div :class="'package_yp'+(index+1)">
                    <img :src="'/images/package'+(index+1)+'.png'" alt="">
                </div>
            </li>
<!--            <li class="package_li2">-->
<!--                <div class="package_ty">-->
<!--                    <p>套餐名称</p>-->
<!--                    <p>¥3900<span>原价：¥3999</span></p>-->
<!--                </div>-->
<!--                <div class="package_yp2">-->
<!--                    <img src="/images/package2.png" alt="">-->
<!--                </div>-->
<!--            </li>-->
<!--            <li class="package_li3">-->
<!--                <div class="package_ty">-->
<!--                    <p>套餐名称</p>-->
<!--                    <p>¥3900<span>原价：¥3999</span></p>-->
<!--                </div>-->
<!--                <div class="package_yp3">-->
<!--                    <img src="/images/package3.png" alt="">-->
<!--                </div>-->
<!--            </li>-->
        </ul>
    </div>
    <div class="package_s">
        <p>选择支付方式</p>
    </div>
    <div class="package_mv">
        <ul>
<!--            <li>-->
<!--                <img src="/images/package_z.png" alt="">-->
<!--                <p>微信支付</p>-->
<!--                <img src="/images/package4.png" alt="">-->
<!--            </li>-->
            <li>
                <img src="/images/package_w.png" alt="">
                <p>微信支付</p>
                <img src="/images/package5.png" alt="">
            </li>
        </ul>
    </div>
    <div class="pack_rt_gh" @click="pay">
        <p>确定</p>
    </div>
</div><!--box-->
<style>
    /*.layui-layer-dialog{*/
       /*display: none;*/
    /*}*/
</style>
<script>

    wx.config({jsApiList:'chooseWXPay',appId:'<?php  echo $config['appId'];?>'});
    wx.ready(function () {
        console.log('微信准备完成');
    });
    var open_id = localStorage.getItem('open_id');
    if (!open_id || typeof(open_id) =="undefined" || open_id ==0) {
        <?php
        $api = new WeixinMpApi();
        $code = Yii::$app->request->get('code');
        if (empty($code)) {
            $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true)) . '";', View::POS_HEAD);
            return;
        } else {
            // 根据微信code获取openid
            try {
                $openid = $api->code2Openid($code);
                $this->registerJs("window.localStorage.setItem('open_id', '{$openid}');", View::POS_HEAD);
            } catch (Exception $e) {
                throw new Exception('无法获取用户OpenId。');
            }
        }
        ?>
    }

 //console.log(open_id)
    var app = new Vue({
        el: '#app',
        data: {
            mobile:'',
            package_list:[],//套餐卡列表
            package_id:'',//套餐id
            title:'',
            package_money:0,

        },
        methods: {
            getPackage: function () {

                // 获取套餐卡列表
                apiPost('/api/goods/package-list', {mobile:this.mobile}, function (json) {

                    if (callback(json)) {
                        app.package_list = json['list'];
                        app.package_id=json['list'][0]['id'];
                        app.title=json['list'][0]['name'];
                        app.package_money=json['list'][0]['package_price'];
                        console.log(app.package_id)

                    }else{

                    setTimeout(function () {
                        window.location.href ='/h5/user/shop';
                    }, 1000);
                     }
                    app.$nextTick(function () {
                        $(".package_yp1").show();
                        $(".package_li1").click(function(){

                            $(".package_yp1").show();
                            $(".package_yp2,.package_yp3").hide();
                        });
                        $(".package_li2").click(function(){
                            $(".package_yp2").show();
                            $(".package_yp1,.package_yp3").hide();
                        });
                        $(".package_li3").click(function(){
                            $(".package_yp3").show();
                            $(".package_yp1,.package_yp2").hide();
                        });

                    });
                    console.log(app.package_list)
                });
            },

            pay:function () {

                // 支付
                apiGet('<?php echo Url::to(['/api/user/pay-package']);?>', {id:this.package_id,pay_method:<?php echo FinanceLog::PAY_METHOD_WX_MP;?>,openid:window.localStorage.getItem('open_id')}, function (json) {

                    if (callback(json)) {

                        wx.error(function (res) {
                            console.error(res);
                        });
                        var config = json['weixin'];
                        wx.chooseWXPay({
                            'timestamp': config['timeStamp'],
                            'nonceStr': config['nonceStr'],
                            'package': config['package'],
                            'signType': config['signType'],
                            'paySign': config['paySign'],
                            'success': function (res) {

                              //  console.log(res)
                              window.location.href = '/h5/user/package-callback?money='+ app.package_money +'&title='+app.title;
                                //pay_callback({'result':'success', 'pay_result':'success', 'pay_money':0});
                            },
                            'fail': function (res) {
                            },
                            'complete': function (res) {
                            },
                            'cancel': function (res) {
                            },
                            'trigger': function (res) {
                            }
                        });

                    }
                });
            },

            check_package_id:function (id,money,title) {
                  app.package_id=id;
                  app.package_money=money;
                  app.title=title;
                  console.log(app.package_id)
                console.log(app.package_money)
                console.log(app.title)
            },

        },
        mounted:function(){
            this.getPackage();
        },
        watch: {

        },



    });

</script>
