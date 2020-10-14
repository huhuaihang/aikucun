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

$this->title = '更换登录手机号码';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">更换登录手机号码</div>
    </header>
    <div class="container">
        <!--绑定新手机-->
        <form @submit.prevent="submit">
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="password" placeholder="请输入当前登录密码" v-model="UserBindMobileForm.password"/>
            </div>
            <div class="b_magt ubb b_bindphone">
                <input class="b_rewrite1 " type="number" id="old_mobile" placeholder="请输入老的手机号" v-model="UserBindMobileForm.old_mobile"  ref="old_mobile"/>
                <span class="b_getcode" @click="sendSmsCode('old')">{{ btn_old_txt }}</span>
            </div>
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="text" placeholder="请输入验证码" v-model="UserBindMobileForm.old_code" ref="old_code"/>
            </div>
            <div class="b_magt ubb b_bindphone">
                <input class="b_rewrite1 " type="number" id="new_mobile" placeholder="请输入新的手机号" v-model="UserBindMobileForm.mobile" ref="mobile"/>
                <span class="b_getcode" @click="sendSmsCode('new')">{{ btn_txt }}</span>
            </div>
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="text" placeholder="请输入验证码" v-model="UserBindMobileForm.code" ref="code"/>
            </div>
            <button class="b_confrim">确定</button>
        </form>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            UserBindMobileForm: {
                password: '',
                old_mobile: '',
                mobile: '',
                old_code: '',
                code: '',
            },
            left_old_sec: 0,
            left_sec: 0,
            is_old: 0,
            btn_txt: '获取验证码',
            btn_old_txt: '获取验证码'
        },
        methods: {
            sendSmsCode: function (e) {
                if (this.left_sec > 0) {
                    return;
                }
                var send_mobile = '';
                var is_old = 0;
                if (e == 'old') {
                    send_mobile = this.UserBindMobileForm.old_mobile;
                    this.is_old = is_old = 1;
                } else {
                    send_mobile = this.UserBindMobileForm.mobile;
                    this.is_old = is_old = 0;
                }
                if (send_mobile.length <= 0) {
                    layer.msg('手机号不能为空。', function () {});
                    return;
                }
                if (!/^\d{11}$/.test(send_mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                apiGet('<?php echo Url::to(['/api/user/send-sms-code']);?>', {mobile:send_mobile, is_old:is_old}, function (json) {
                    if (callback(json)) {
                        layer.msg('短信验证码已发送。', function () {});
                        if (is_old == 1) {
                            app.left_old_sec = 60;
                        } else {
                            app.left_sec = 0;
                        }

                        app.update_time();
                    }
                });
            },
            update_time: function () {
                if (this.is_old == 1) {
                    if (this.left_old_sec > 0) {
                        this.left_old_sec--;
                        this.btn_old_txt = '(' + this.left_old_sec + ')S';
                        window.setTimeout(function () {app.update_time();}, 1000);
                    } else {
                        this.btn_old_txt = '重新发送';
                    }
                } else {
                    if (this.left_sec > 0) {
                        this.left_sec--;
                        this.btn_txt = '(' + this.left_sec + ')S';
                        window.setTimeout(function () {app.update_time();}, 1000);
                    } else {
                        this.btn_txt = '重新发送';
                    }
                }

            },
            submit: function () {
                apiPost('<?php echo Url::to(['/api/user/bind-mobile']);?>', this.UserBindMobileForm, function (json) {
                    if (callback(json)) {
                        layer.msg('已绑定新登录手机号码。', function () {
                            window.location = '<?php echo Url::to(['/h5/user/profile']);?>';
                        });
                    }
                });
            }
        }
    });
</script>
