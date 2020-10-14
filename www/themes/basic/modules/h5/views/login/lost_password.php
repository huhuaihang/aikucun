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

$this->title = '找回密码';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">找回密码</div>
    </header>
    <div class="container">
        <div class="z_retrieve_password">
            <form @submit.prevent="submit">
                <div class="div1">
                    <label>+86</label>
                    <input type="number" placeholder="请输入手机号" v-model="UserPasswordForm.mobile" ref="mobile">
                </div>
                <div class="div1 yanzhengma">
                    <label>验证码</label>
                    <input type="number" placeholder="输入验证码" v-model="UserPasswordForm.code" ref="code">
                    <span id="btn_send_code" @click="sendSmsCode">{{btn_txt}}</span>
                </div>
                <div class="div1">
                    <label>新密码</label>
                    <input type="password" placeholder="输入新密码" v-model="UserPasswordForm.password" ref="password">
                </div>
                <div class="div1">
                    <label>确认密码</label>
                    <input type="password" placeholder="再次输入密码" v-model="UserPasswordForm.re_password" ref="re_password">
                </div>
                <button>确定</button>
            </form>
        </div><!--z_retrieve_password-->
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            UserPasswordForm: {
                mobile: '',
                code: '',
                password: '',
                re_password: '',
                client_type: 'h5',
            },
            left_sec: 0,
            btn_txt: '获取验证码'
        },
        methods: {
            sendSmsCode: function () {
                if (this.left_sec > 0) {
                    return;
                }
                if (!/^\d{11}$/.test(this.UserPasswordForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                apiGet('<?php echo Url::to(['/api/user/send-forget-sms-code']);?>', {mobile: this.UserPasswordForm.mobile}, function (json) {
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
                if (!/^\d{11}$/.test(this.UserPasswordForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                if (!/^\d{4}$/.test(this.UserPasswordForm.code)) {
                    layer.msg('手机验证码格式错误，只能填写4位数字。', function () {});
                    app.$refs.code.focus();
                    return false;
                }
                if (!/^.+$/.test(this.UserPasswordForm.password)) {
                    layer.msg('密码不能为空。', function () {});
                    app.$refs.password.focus();
                    return false;
                }
                if (!/^.+$/.test(this.UserPasswordForm.re_password)) {
                    layer.msg('确认密码不能为空。', function () {});
                    app.$refs.re_password.focus();
                    return false;
                }
                if (this.UserPasswordForm.password !== this.UserPasswordForm.re_password) {
                    layer.msg('两次输入的密码不一致！', function () {});
                    app.$refs.re_password.focus();
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/set-password']);?>', this.UserPasswordForm, function (json) {
                    if (callback(json)) {
                        window.location = '<?php echo Url::to(['/h5/login']);?>';
                    }
                });
            }
        }
    });
</script>
