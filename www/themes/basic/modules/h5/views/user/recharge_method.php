<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\FinanceLog;
use app\models\System;
use app\models\WeixinMpApi;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this \yii\web\View
 * @var $money
 */
ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '充值方式';
?>
<div class="box">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user/recharge-values']);?>'" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">选择支付方式</a>
    </div><!--new_header-->
    <ul class="b_zhifulist b_magt1">
        <?php if (System::getConfig('allinpay_ali_open') == 1) {?>
        <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>">
            <div class="b_zfselect">
                <img src="/images/progress_icon_03.png"/>
            </div>
            <p class="b_zficon">
                <img src="/images/zhifubao_03.png"/>
            </p>
            <p>支付宝</p>
        </li>
        <?php }?>
        <?php if (System::getConfig('weixin_mp_pay_open') == 1
        && preg_match('/micromessenger/i', Yii::$app->request->getUserAgent())) {
        $api = new WeixinMpApi();
        $code = Yii::$app->request->get('code');
        if (empty($code)) {
            $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true)) . '";', View::POS_HEAD);
            return;
        } else {
            // 根据微信code获取openid
            try {
                $openid = $api->code2Openid($code);
                $this->registerJs("window.localStorage.setItem('weixin_openid', '{$openid}');", View::POS_HEAD);
            } catch (Exception $e) {
                throw new Exception('无法获取用户OpenId。');
            }
        }
        $config = $api->jsWxConfig(Url::current([], true));
        $config['jsApiList'] = [
            'chooseWXPay',
        ];
        $config = json_encode($config);
        $this->registerJsFile('https://res.wx.qq.com/open/js/jweixin-1.2.0.js', ['position' => View::POS_HEAD]);
        $this->registerJs(<<<JJSS
wx.config({$config});
wx.ready(function () {
    console.log('微信准备完成');
});
wx.error(function (res) {
    console.error(res);
});
JJSS
        );?>
            <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_MP;?>">
                <div class="b_zfselect">
                    <img src="/images/progress_icon_03.png"/>
                </div>
                <p class="b_zficon">
                    <img src="/images/weixin_03.png"/>
                </p>
                <p>微信</p>
            </li>
        <?php }?>
        <?php if (System::getConfig('weixin_h5_pay_open') == 1) {?>
            <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_H5;?>">
                <div class="b_zfselect">
                    <img src="/images/progress_icon_03.png"/>
                </div>
                <p class="b_zficon">
                    <img src="/images/weixin_03.png"/>
                </p>
                <p>微信</p>
            </li>
        <?php }?>
    </ul>
    <a class="b_confrim" href="javascript:void(0)">确定</a>
</div>
<script>
    function page_init() {
        window.pay_method = 0; // 支付方式
        $(".b_zhifulist li").click(function(){
            $(this).find('.b_zfselect img').attr('src','/images/progress1_icon_03.png');
            $(this).siblings('li').find('.b_zfselect img').attr('src','/images/progress_icon_03.png');
            window.pay_method = $(this).data('pay_method');
        });
        var ua = navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i)=="micromessenger") {
            //微信浏览器 去掉支付宝支付
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_H5;?>"]').hide();
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_ZFB;?>"]').hide();
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>"]').hide();
        }
        $(".b_confrim").click(function() {
            if (window.pay_method === 0) {
                layer.msg('请先选择支付方式。', function () {});
                return false;
            }
            apiGet('/api/user/recharge', {'money': <?php echo $money;?>, 'pay_method': window.pay_method, 'openid':window.localStorage.getItem('weixin_openid')}, function(json) {
                if (callback(json)) {
                    switch (window.pay_method) {
                        case <?php echo FinanceLog::PAY_METHOD_WX_MP;?>: // 微信公众号支付
                            try {
                                var config = json['weixin'];
                                wx.chooseWXPay({
                                    'timestamp': config['timeStamp'],
                                    'nonceStr': config['nonceStr'],
                                    'package': config['package'],
                                    'signType': config['signType'],
                                    'paySign': config['paySign'],
                                    'success': function (res) {
                                        window.location.href = "<?php echo Url::to(['/h5/user/recharge-values']);?>";
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
                            } catch (e) {
                            }
                            break;
                        case <?php echo FinanceLog::PAY_METHOD_WX_H5;?>: // 微信H5支付
                            window.location = json['redirect_url'];
                            break;
                        case <?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>: // 通联支付宝
                            window.location.href = json['payinfo'];
                            break;
                    }
                }
            });
        });
    }
</script>
