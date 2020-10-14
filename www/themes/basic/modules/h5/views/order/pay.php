<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\FinanceLog;
use app\models\PinganApi;
use app\models\System;
use app\models\WeixinMpApi;
use yii\base\Exception;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this \yii\web\View
 * @var $order \app\models\Order
 */

ApiAsset::register($this);
LayerAsset::register($this);

$this->title = '选择付款方式';
?>
<style rel="stylesheet">
    .new_back{
        width:100%;
        height:100%;
        background-color:rgba(0,0,0,.8);
        position:fixed;
        left:0;
        top:0;
        right:0;
        bottom:0;
        z-index:10;
        color:#f00;
    }
    .determine_box{
        width:100%;
        position: fixed;
        top:30%;
        margin:0 auto;
    }
    .determine{
        width:6.4rem;
        height: 4rem;
        margin:0 auto;
        background: #fff;
        border-radius: 6px;
        padding-top:0.3rem;
    }
    .determine p{
        height:1.2rem;
        line-height: 1.2rem;
        font-size:0.35rem;
        text-align: center;
    }
    .determine p a{
        color: #666;
    }
    .determine .p2{
        border-top:1px solid #999;
        border-bottom:1px solid #999;
    }
    .determine .p2 a{color: #FD5439;}
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
<!--            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>-->
            <a href="/h5/order" ><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">
            选择付款方式
        </div>
    </header>
    <div class="container">
        <ul class="b_zhifulist b_magt1">
            <li>
                <p class="p1">订单信息</p>
                <p class="p2"><?php echo array_reduce($order->itemList, function ($carry, $item) {
                    /** @var \app\models\OrderItem $item */
                    if (empty($carry)) {
                        $carry = $item->title;
                    } else {
                        $carry .= '、' . $item->title;
                    }
                    return $carry;
                }, '');?></p>
            </li>
            <li>
                <p class="p1">收款方</p>
                <p class="p2"><?php echo Html::encode(Yii::$app->params['companyName']);?></p>
            </li>
            <li>
                <p class="p1">付款金额</p>
                <p class="p2 b_color_red">￥<?php echo $order->amount_money;?></p>
            </li>
        </ul>
        <!--选择付款方式-->
        <ul class="b_zhifulist b_magt1">
<!--            <li data-pay_method="--><?php //echo FinanceLog::PAY_METHOD_YE;?><!--">-->
<!--                <div class="b_zfselect">-->
<!--                    <img src="/images/address_noselect_03.png"/>-->
<!--                </div>-->
<!--                <p class="b_zficon">-->
<!--                    <img src="/images/me_15.png"/>-->
<!--                </p>-->
<!--                <p>佣金账户</p>-->
<!--            </li>-->
            <?php if (System::getConfig('alipay_open') == 1) {?>
                <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_ZFB;?>">
                    <div class="b_zfselect">
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/zhifubao_03.png"/>
                    </p>
                    <p>支付宝</p>
                </li>
            <?php }?>
<!--            <li data-pay_method="--><?php //echo FinanceLog::PAY_METHOD_YE;?><!--">-->
<!--                <div class="b_zfselect">-->
<!--                    <img src="/images/address_noselect_03.png"/>-->
<!--                </div>-->
<!--                <p class="b_zficon">-->
<!--                    <img src="/images/weixin-a.png"/>-->
<!--                </p>-->
<!--                <p>微信支付</p>-->
<!--            </li>-->
            <?php if (System::getConfig('weixin_scan_pay_open') == 1) {?>
                <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_SCAN;?>">
                    <div class="b_zfselect">
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/weixin_03.png"/>
                    </p>
                    <p>微信扫码</p>
                </li>
            <?php }?>
            <?php if (System::getConfig('weixin_mp_pay_open') == 1
                && preg_match('/micromessenger/i', Yii::$app->request->getUserAgent())) {
                $api = new WeixinMpApi();
//                $code = Yii::$app->request->get('code');
//                if (empty($code)) {
//                    $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true)) . '";', View::POS_HEAD);
//                    return;
//                } else {
//                    // 根据微信code获取openid
//                    try {
//                        $openid = $api->code2Openid($code);
//                        $this->registerJs("window.localStorage.setItem('weixin_openid', '{$openid}');", View::POS_HEAD);
//                    } catch (Exception $e) {
//                        throw new Exception('无法获取用户OpenId。');
//                    }
//                }
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
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/weixin_03.png"/>
                    </p>
                    <p>微信支付</p>
                </li>
            <?php }?>
            <?php if (System::getConfig('weixin_h5_pay_open') == 1) {?>
                <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_H5;?>">
                    <div class="b_zfselect">
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/weixin_03.png"/>
                    </p>
                    <p>微信支付</p>
                </li>
            <?php }?>
            <?php if (System::getConfig('allinpay_h5_open') == 1) {?>
                <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_ALLINPAY_H5;?>">
                    <div class="b_zfselect">
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/allinpay_logo.png"/>
                    </p>
                    <p>通联支付</p>
                </li>
            <?php }?>
            <?php if (System::getConfig('allinpay_ali_open') == 1) {?>
                <li data-pay_method="<?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>">
                    <div class="b_zfselect">
                        <img src="/images/address_noselect_03.png"/>
                    </div>
                    <p class="b_zficon">
                        <img src="/images/zhifubao_03.png"/>
                    </p>
                    <p><!-- 通联支付 -->支付宝</p>
                </li>
            <?php }?>
            <?php if (System::getConfig('pingan_open') == 1) {?>
                <?php $pingan_api = new PinganApi();
                foreach ($pingan_api->UnionAPI_Opened(Yii::$app->id . '_' . Yii::$app->user->id) as $card) {?>
                    <li class="b_zfselect_card1" data-pay_method="<?php echo FinanceLog::PAY_METHOD_YHK;?>" data-card_openid="<?php echo $card['OpenId'];?>">
                        <div class="b_zfselect b_zfselect1">
                            <img src="/images/address_noselect_03.png"/>
                        </div>
                        <p class="b_zficon b_zficon1">
                            <img src="<?php echo '/images/bank/' . preg_replace('/\d/', '', $card['plantBankId']) . '.png';?>"/>
                        </p>
                        <p style="float:none;"><?php echo Html::encode($card['plantBankName']);?></p>
                        <p><?php echo Html::encode($card['accNo']);?></p>
                    </li>
                <?php }?>
                <li class="b_zfselect_card" onclick="document.getElementById('P_FORM').submit();">
                    <p>银行卡绑定</p>
                    <div style="display:none;"><?php echo $pingan_api->UnionAPI_Open(Yii::$app->id . '_' . Yii::$app->user->id, Url::current());?></div>
                </li>
            <?php }?>
        </ul>
        <a class="b_confrim" href="javascript:void(0)">确定</a>
        <div class="z_rgba_mima">
            <div class="pay_tanchuang">
                <h4>输入支付密码</h4>
                <p class="b_money">￥<?php echo $order->amount_money;?></p>
                <div class="alieditContainer" id="payPassword_container">
                    <input class="ui-input i-text" id="payPassword_rsainput" name="payPassword_rsainput" minlength="6" maxlength="6" tabindex="1" type="tel" value="" oncontextmenu="return false" onpaste="return false" oncopy="return false" oncut="return false" autocomplete="off" />
                    <!-- 文本框不支持右键 oncontextmenu  不支持粘贴onpaste  不支持复制oncopy  不支持剪切oncut  -->
                    <div class="sixDigitPassword" tabindex="0">
                        <i class="active"><b></b></i>
                        <i><b></b></i>
                        <i><b></b></i>
                        <i><b></b></i>
                        <i><b></b></i>
                        <i><b></b></i>
                        <span class="guangbiao" style="left:0;"></span>
                    </div>
                </div>
                <div class="b_close_btn">
                    <img src="/images/colse_btn_03.png"/>
                </div>
            </div><!--pay_tanchuang-->
        </div><!--rgba-->
    </div>
    <div class="new_back" id="checkPayBox" style="display:none;">
        <div class="determine_box">
            <div class="determine">
                <p class="p1"><a href="javascript:void(0)">请确认支付是否已完成</a></p>
                <p class="p2"><a href="<?php echo Url::to(['/h5/order/view', 'order_no' => $order->no, 'refresh' => 1]);?>">已完成支付</a></p>
                <p class="p3"><a href="javascript:void(0)" onclick="localStorage.setItem('check_pay_result', '0');$('#checkPayBox').hide();"> 支付遇到问题，重新支付</a></p>
            </div>
        </div>
    </div>
</div><!--box-->
<script>
    window.pay_method = 0; // 当前选择支付方式
    window.card_openid = ''; // 绑定银行卡OpenId
    function page_init() {
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
                    //throw new Exception('无法获取用户OpenId。');
                    $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true)) . '";', View::POS_HEAD);
                }
            }
            ?>
        }
        if (localStorage.getItem('check_pay_result') == '1') {
            checkPayResult('<?php echo $order->no;?>');
        }
//        apiGet('<?php //echo Url::to(['/api/user/detail']);?>//', {}, function (json) {
//            if (callback(json)) {
//                if (json['user']['have_payment_password'] != 1) {
//                    layer.msg('您还没有设置支付密码，请先设置支付密码。', function () {
//                        window.location.href = '<?php //echo Url::to(['/h5/user/payment-password', 'returnUrl' => Url::to(['/h5/order/pay', 'order_no' => $order->no])]);?>//';
//                    });
//                }
//            }
//        });
        var ua = navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i)=="micromessenger") {
            //微信浏览器 去掉支付宝支付
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_WX_H5;?>"]').hide();
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_ZFB;?>"]').hide();
            $('li[data-pay_method="<?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>"]').hide();
        }
        /*默认选择*/
        $('.b_zhifulist li').click(function(){
            $(this).find('.b_zfselect img').attr('src','/images/address_selected_03.png');
            $(this).siblings('li').find('.b_zfselect img').attr('src','/images/address_noselect_03.png');
            window.pay_method = $(this).data('pay_method');
            window.card_openid = $(this).data('card_openid');
        });
        $(".b_confrim").click(function() {
            if (window.pay_method === 0) {
                layer.msg('请先选择支付方式。', function () {});
                return false;
            }
            $(".i-text").val('');
            var inp_l = $('.i-text').val().length;
            $(".sixDigitPassword").find("i").eq( inp_l ).addClass("active").siblings("i").removeClass("active");
            $(".sixDigitPassword").find("i").eq( inp_l ).prevAll("i").find("b").css({"display":"block"});
            $(".sixDigitPassword").find("i").eq( inp_l - 1 ).nextAll("i").find("b").css({"display":"none"});
            var offset = inp_l * 0.163;
            var length = (offset * 100) + '%';
            $(".guangbiao").css({"left":length});
            if( inp_l == 0) {
                $(".sixDigitPassword").find("i").eq( 0 ).addClass("active").siblings("i").removeClass("active");
                $(".sixDigitPassword").find("b").css({"display":"none"});
                $(".guangbiao").css({"left":0});
            }
            //$(".z_rgba_mima").show();
            payOrder('<?php echo $order->no;?>', window.pay_method);
        });
        $(".b_close_btn img").click(function() {
            $(".sixDigitPassword i").find("b").css('display','none');
            $(".sixDigitPassword i").eq(0).addClass("active").siblings("i").removeClass("active");
            $(".z_rgba_mima").hide();
            $(".i-text").val('');
        });
        $('.ui-input').focus();
        $('.b_close_btn').on('click', function(){
            $('.ui-input').val('');
        });
        $('.sixDigitPassword').on('click',function() {
            $('.ui-input').focus();
        });
        $(".i-text").keyup(function() {
            var inp_l = $(this).val().length;
            $(".sixDigitPassword").find("i").eq( inp_l ).addClass("active").siblings("i").removeClass("active");
            $(".sixDigitPassword").find("i").eq( inp_l ).prevAll("i").find("b").css({"display":"block"});
            $(".sixDigitPassword").find("i").eq( inp_l - 1 ).nextAll("i").find("b").css({"display":"none"});
            var offset = inp_l * 0.163;
            var length = (offset * 100) + '%';
            $(".guangbiao").css({"left":length});
            if( inp_l == 0) {
                $(".sixDigitPassword").find("i").eq( 0 ).addClass("active").siblings("i").removeClass("active");
                $(".sixDigitPassword").find("b").css({"display":"none"});
                $(".guangbiao").css({"left":0});
            } else if( inp_l == 6) {
                $(".sixDigitPassword").find("b").css({"display":"block"});
                $(".sixDigitPassword").find("i").eq(5).addClass("active").siblings("i").removeClass("active");
                var offset = 5 * 0.166;
                length = (offset * 100) + '%';
                $(".guangbiao").css({"left":length});
                payOrder('<?php echo $order->no;?>', window.pay_method);
            }
        });
    }
    function payOrder(order_no, method) {
        var payment_password = $('#payPassword_rsainput').val();
        $('.ui-input').val('');
        $(".z_rgba_mima").hide();
        $(".i-text").val('');
        //apiGet('<?php echo Url::to(['/api/order/prepare-pay']);?>', {'order_no':order_no, 'pay_method':method, 'payment_password':payment_password, 'openid':window.localStorage.getItem('weixin_openid')}, function (json) {
        apiGet('<?php echo Url::to(['/api/order/prepare-pay']);?>', {'order_no':order_no, 'pay_method':method, 'openid':window.localStorage.getItem('open_id')}, function (json) {
            if (callback(json)) {
                var order_no = json['order_no'];
                switch (method) {
                    case <?php echo FinanceLog::PAY_METHOD_YHK;?>: // 银行卡
                        $.getJSON('<?php echo Url::to(['/api/pingan/send-sms']);?>', {
                            'order_id':json['trade_no'],
                            'money':json['money'],
                            'customer_id':'<?php echo Yii::$app->id . '_', Yii::$app->user->id;?>',
                            'open_id':window.card_openid
                        }, function(json) {
                            if (callback(json)) {
                                var orig = json['orig'];
                                if (orig['status'] !== '01') {
                                    layer.msg(orig['errorMsg'], function () {});
                                } else {
                                    layer.prompt({
                                        formType: 0,
                                        value: '',
                                        title: '请输入您收到的短信验证码：'
                                    }, function(verifyCode, index){
                                        layer.close(index);
                                        $.getJSON('<?php echo Url::to(['/api/pingan/pay']);?>', {
                                            'order_id':orig['orderId'],
                                            'money':orig['amount'],
                                            'name':'<?php echo System::getConfig('site_name');?>订单',
                                            'pay_date':orig['paydate'],
                                            'customer_id':orig['customerId'],
                                            'open_id':window.card_openid,
                                            'verify_code':verifyCode,
                                            'remark':'<?php echo System::getConfig('site_name');?>订单'
                                        }, function(json) {
                                            if (callback(json)) {
                                                localStorage.setItem('check_pay_result', '1');
                                                checkPayResult(order_no);
                                            }
                                        });
                                    });
                                }
                            }
                        });
                        break; // 银行卡 结束
                    case <?php echo FinanceLog::PAY_METHOD_WX_SCAN;?>: // 微信扫码
                        layer.open({
                            type: 1,
                            title: false,
                            closeBtn: 0,
                            shadeClose: true,
                            area: ['320px', '320px'],
                            content: '<img src="<?php echo Url::to(['/site/qr']);?>?content=' + json['weixin']['code_url'] + '" />',
                            end: function () {
                                localStorage.setItem('check_pay_result', '0');
                            }
                        });
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        break; // 微信扫码 结束
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
                                    // pay_callback({'result':'success', 'pay_result':'success', 'pay_money':0});
                                    checkPayResult(order_no);

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
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        window.location = json['redirect_url'];
                        break;
                    case <?php echo FinanceLog::PAY_METHOD_ZFB;?>: // 支付宝
                        var form = json['form'];
                        $('body').append(form);
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        break; // 支付宝 结束
                    case <?php echo FinanceLog::PAY_METHOD_ALLINPAY;?>: // 通联支付
                        $('body').append(json['form']);
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        break;
                    case <?php echo FinanceLog::PAY_METHOD_ALLINPAY_H5;?>: // 通联H5支付
                        $('body').append(json['form']);
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        break;
                    case <?php echo FinanceLog::PAY_METHOD_ALLINPAY_ALI;?>: // 通联支付宝
                        window.location.href = json['payinfo'];
                        localStorage.setItem('check_pay_result', '1');
                        checkPayResult(order_no);
                        break;
                    case <?php echo FinanceLog::PAY_METHOD_YE;?>: // 佣金
                        if (json['pay_success']) {
                            pay_callback({'result':'success', 'pay_result':'success', 'pay_money':json['pay_money']});
                        } else {
                            layer.msg('支付失败。', function () {});
                        }
                        break;
                }
            }
        });
    }
    function checkPayResult(order_no) {
        // if (localStorage.getItem('check_pay_result') != '1') {
        //     $('#checkPayBox').hide();
        //     return false;
        // }
        $('#checkPayBox').show();
        apiGet('<?php echo Url::to(['/api/order/finance']);?>', {'order_no':order_no}, function (json) {
            if (json['error_code'] == 0) {
                if (json['status'] == <?php echo FinanceLog::STATUS_SUCCESS;?>) {
                    localStorage.setItem('check_pay_result', '0');
                    pay_callback({'result':'success', 'pay_result':'success', 'pay_money':json['money'],'list':json['list'],'order_no':order_no});
                } else {
                    window.setTimeout(function () {
                        checkPayResult(order_no);
                    }, 1000);
                }
            } else {
                $('#checkPayBox').hide();
            }
        });
    }
    /**
     * 支付回调
     * @param json
     * {
     *     "result":"success",
     *     "pay_id":"order_no",
     *     "pay_result":"success",
     *     "pay_money":"99.99",
     *     "pay_remark":"..."
     * }
     */
    function pay_callback(json) {
        $('#checkPayBox').hide();
        if (callback(json)) {
            if (json['pay_result'] === 'success') {
                var message = '支付成功。';
                if (json['pay_money'] > 0) {
                    message += '<br />支付金额：' + json['pay_money'];
                }

                if(JSON.stringify(json['list']) != '{}')
                {
                    window.location.href = '<?php echo Url::to(['/h5/order/coupon-pay-callback?order_no=']);?>'+json['order_no'];
                }else
                {
                layer.msg(message, function () {
                    window.location.href = '<?php echo Url::to(['/h5/order']);?>';
                });
                }
            }
        }
    }
</script>
