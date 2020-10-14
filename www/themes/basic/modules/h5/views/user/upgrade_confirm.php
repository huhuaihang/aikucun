<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\web\View;
use yii\helpers\Url;
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
$this->title = '升级卡确认页面';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">确认信息</div>
    </header>
    <div style="height: 1.3rem;"></div>
    <div class="confirm">
        <div class="confirm_sr">
            <img v-if="upgrade_info.is_active == 1" src="/images/confirm_w.png" alt="">
        </div>
        <div class="confirm_qw">
            <p>你还需{{upgrade_info.count}}个会员礼包成长为{{upgrade_info.level_name}}</p>
            <p>¥{{upgrade_info.upgrade_price}}<span v-if="upgrade_info.is_active == 1">原价：¥{{upgrade_info.price}}</span></p>
            <p>({{upgrade_info.count}}个*399元)</p>
            <p v-if="upgrade_info.is_active == 1">注：{{upgrade_info.remark}}</p>
        </div>
    </div>
    <div class="package_s">
        <p>选择支付方式</p>
    </div>
    <div class="package_mv">
        <ul>
            <li>
                <img src="/images/package_w.png" alt="">
                <p>微信支付</p>
                <img src="/images/package5.png" alt="">
            </li>
<!--            <li>-->
<!--                <img src="/images/package_z.png" alt="">-->
<!--                <p>微信支付</p>-->
<!--                <img src="/images/package4.png" alt="">-->
<!--            </li>-->
        </ul>
    </div>
    <div class="pack_rt_gh" @click="pay">
        <p>确定</p>
    </div>
</div><!--box-->

<script>
    wx.config({jsApiList:'chooseWXPay',appId:'<?php  echo $config['appId'];?>'});
    wx.ready(function () {
        console.log('微信准备完成');
    });
    var open_id = localStorage.getItem('open_id');

     if (!open_id || typeof(open_id) =="undefined" || open_id ==0) {
         <?php
        // $api = new WeixinMpApi();
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
                // throw new Exception('无法获取用户OpenId。');
                $this->registerJs('window.location.href="/h5/user/upgrade-confirm"', View::POS_HEAD);
             }
         }
         ?>
    }


    var app = new Vue({
        el: '#app',
        data: {
            mobile:'',
            upgrade_info:[],//升级卡信息
            count:'',//礼包数量
            level_name:'',//下一等级

        },
        methods: {
            getUpgrade: function () {

                // 获取升级卡信息
                apiGet('<?php echo Url::to(['/api/user/upgrade']);?>', {}, function (json) {

                    if (callback(json)) {
                        app.upgrade_info=json['info'];
                        app.count=json['info']['count'];
                        app.level_name=json['info']['level_name'];

                    }else{

                        setTimeout(function () {
                            window.location.href ='/h5/user/shop';
                        }, 1000);
                    }
                    console.log(json)
                });
            },
            pay:function () {

                // 支付
                apiGet('<?php echo Url::to(['/api/user/pay-upgrade']);?>', {pay_method:<?php echo FinanceLog::PAY_METHOD_WX_MP;?>,openid:window.localStorage.getItem('open_id')}, function (json) {

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
                                window.location.href = '/h5/user/upgrade-callback?count='+ app.count + '&level_name=' + app.level_name;
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



        },
        watch: {

        },
        mounted:function () {
            this.getUpgrade();
        },


    });

</script>
