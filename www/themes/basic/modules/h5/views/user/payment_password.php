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

$this->title = '设置支付密码';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">设置支付密码</div>
    </header>
    <div class="container">
        <!--设置支付密码-->
        <form @submit.prevent="submit">
            <div class="b_magt ubb b_bindphone">
                <label class="b_rewrite1">{{mobile}}</label>
                <span class="b_getcode" @click="sendSmsCode">{{ btn_txt }}</span>
            </div>
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="number" placeholder="请输入验证码" v-model="UserPaymentPasswordForm.code"/>
            </div>
            <ul class="b_reset_code b_magt">
                <li class="ubb b_magt"><input  class="b_second b_jisuan" type="password"  onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="6" placeholder="请输入6位数字支付密码" v-model="UserPaymentPasswordForm.password"/></li>
                <li><input  class="b_third b_jisuan" type="password" onkeyup="value=value.replace(/[^\d]/g,'')" maxlength="6" placeholder="重新确认新支付密码" v-model="UserPaymentPasswordForm.re_password"/></li>
            </ul>
            <button class="b_confrim">确定</button>
        </form>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            mobile: '',
            UserPaymentPasswordForm: {
                code: '',
                password: '',
                re_password: '',
                client_type: 'h5',
            },
            left_sec: 0,
            btn_txt: '获取验证码'
        },
        methods: {
            loadUser: function () {
                apiGet('/api/user/detail', {}, function (json) {
                    app.mobile = json['user']['mobile'].replace(/(\d{3})(\d{4})(\d{4})/, '$1****$3');
                });
            },
            sendSmsCode: function () {
                if (this.left_sec > 0) {
                    return;
                }
                apiGet('<?php echo Url::to(['/api/user/send-payment-sms-code']);?>', {}, function (json) {
                    if (callback(json)) {
                        layer.msg('短信验证码已发送。', function () {});
                        app.left_sec = 60;
                        app.update_time();
                    }
                });
            },
            update_time: function () {
                if (this.left_sec > 0) {
                    this.left_sec--;
                    this.btn_txt = '(' + this.left_sec + ')S';
                    window.setTimeout(function () {app.update_time();}, 1000);
                } else {
                    this.btn_txt = '重新发送';
                }
            },
            submit: function () {
                apiPost('<?php echo Url::to(['/api/user/set-payment-password']);?>', this.UserPaymentPasswordForm, function (json) {
                    if (callback(json)) {
                        window.location = '<?php echo Yii::$app->request->get('returnUrl', Url::to(['/h5/user/profile']));?>';
                    }
                });
            }
        },
        mounted: function () {
            this.loadUser();
        }
    });
</script>
