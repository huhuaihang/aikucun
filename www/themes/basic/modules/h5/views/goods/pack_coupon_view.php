<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\widgets\AdWidget;
use app\assets\VueAsset;
use yii\web\View;
use app\models\WeixinMpApi;
use app\models\FinanceLog;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

if(empty(Yii::$app->request->get('app')))
{
$api = new WeixinMpApi();
$this->registerJsFile('https://res.wx.qq.com/open/js/jweixin-1.2.0.js', ['position' => View::POS_HEAD]);
$config = $api->jsWxConfig(Url::current([], true));
}
$this->title = '礼包卡券详情';
?>
<div class="box1" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo  Url::to(['/h5']) ; ?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">礼包卡券详情</div>
    </header>
    <div class="exchange_sy">
        <ul>
            <li>
              <div v-html="goods.content" ></div>
            </li>

        </ul>
    </div>
    <div class="exchange_st">
        <a  @click="pay">立即购买</a>
    </div>
</div>
<script>

    <?php if(empty(Yii::$app->request->get('app'))){ ?>

        wx.config({jsApiList: ['chooseWXPay'], appId: '<?php  echo $config['appId'];?>'});
        wx.ready(function () {
            console.log('微信准备完成');
        });
        var open_id = localStorage.getItem('open_id');
        if (!open_id || typeof(open_id) == "undefined" || open_id == 0) {
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
                    $this->registerJs('window.location.href="/h5/goods/pack-coupon-view"', View::POS_HEAD);
                }
            }
            ?>
        }
    <?php }?>

    var app = new Vue({
        el: '#app',
        data: {
            title: '商品列表',
            goods: [], // 商品详情
            scroll: false // 滚动监听器
        },
        methods: {
            /**
             * 加载礼包卡券商品详情
             */
            loadGoodsDetail: function () {
                apiGet('/api/goods/pack-redeem-detail', this.SearchForm, function (json) {
                    if (callback(json)) {
                        if(json['detail'] === undefined){
                           alert('活动暂未开启');
                            window.location.href = '/h5';
                            return false;
                        }
                        app.goods=json['detail'];
                        console.log(json);
                    }
                });
            },
            pay:function () {

                // 支付
                apiGet('<?php echo Url::to(['/api/user/pay-package-coupon']);?>', {id:this.goods.id,pay_method:<?php echo FinanceLog::PAY_METHOD_WX_MP;?>,openid:window.localStorage.getItem('open_id')}, function (json) {

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
                                window.location.href = '/h5/order/coupon-pay-callback?order_no='+json['no'];
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

        mounted: function () {
            // 云约中打开时去掉双标题
            if (Util.request.get('app') !== undefined) {
                $('.mall-header').hide();
                $('.exchange_st').hide();
                $('.exchange_sy').css('margin-top','0');
            }

            this.loadGoodsDetail();
        }
    });
</script>