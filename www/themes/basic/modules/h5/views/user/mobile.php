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
        <div class="mall-header-title">绑定新手机</div>
    </header>
    <div class="container">
        <!--绑定新手机-->
        <form @submit.prevent="submit">
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="password" placeholder="请输入当前登录密码" v-model="UserBindMobileForm.password"/>
            </div>
            <div class="b_magt ubb b_bindphone">
                <input class="b_rewrite1 " type="number" id="new_mobile" placeholder="请输入新的手机号" v-model="UserBindMobileForm.mobile"/>
                <span class="b_getcode" @click="sendSmsCode">{{ btn_txt }}</span>
            </div>
            <div class="ubb b_bindphone">
                <input class="b_rewrite1 " type="text" placeholder="请输入验证码" v-model="UserBindMobileForm.code"/>
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
                mobile: '',
                code: ''
            },
            left_sec: 0,
            btn_txt: '获取验证码'
        },
        methods: {
            sendSmsCode: function () {
                if (this.left_sec > 0) {
                    return;
                }
                apiGet('<?php echo Url::to(['/api/user/send-sms-code']);?>', {mobile:this.UserBindMobileForm.mobile}, function (json) {
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
