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

$this->title = '绑定';
?>
<style>
    body {background:#fff;}
</style>
<div class="box" id="app">
    <div class="new_login">
    <div class="login_head"><img src="/images/new_login_banner.png"></div>
        <div class="login_tab">
            <a href="<?php echo Url::to(['/h5/login']);?>">登录</a>
            <a href="<?php echo Url::to(['/h5/register']);?>" class="a1">注册</a>
        </div><!--tab-->
        <form @submit.prevent="submit"  class="login_form registered_form">
            <div>
                <label></label>
                <input type="number" placeholder="手机号" v-model="UserRegisterForm.mobile" ref="mobile">
            </div>
            <div class="yanzhengma">
                <label></label>
                <input type="number" placeholder="获取验证码" v-model="UserRegisterForm.code" ref="code">
                <span id="btn_send_code" @click="sendSmsCode">{{ btn_txt }}</span>
            </div>
            <div>
                <label></label>
                <input type="password" placeholder="设置新密码" v-model="UserRegisterForm.password" ref="password">
            </div>
            <div>
                <label></label>
                <input type="text" placeholder="邀请码" v-model="UserRegisterForm.invite_code" ref="nickname">
            </div>
            <button>注册</button>
        </form>
    </div>
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            UserRegisterForm: {
                password: '',
                mobile: '',
                code: '',
                invite_code: "<?php echo Yii::$app->request->get('invite_code')?>",
                save_session: 1,
                client_type: 'h5',
            },
            left_sec: 0,
            btn_txt: '获取'
        },
        methods: {
            sendSmsCode: function () {
                if (this.left_sec > 0) {
                    return;
                }
                if (!/^\d{11}$/.test(this.UserRegisterForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/send-register-sms-code']);?>', {'mobile':this.UserRegisterForm.mobile}, function (json) {
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
                if (!/^.+$/.test(this.UserRegisterForm.password)) {
                    layer.msg('密码不能为空。', function () {});
                    app.$refs.password.focus();
                    return false;
                }
                if (!/^\d{11}$/.test(this.UserRegisterForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                if (!/^\d{4}$/.test(this.UserRegisterForm.code)) {
                    layer.msg('手机验证码格式错误，只能填写4位数字。', function () {});
                    app.$refs.code.focus();
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/register']);?>', this.UserRegisterForm, function (json) {
                    if (callback(json)) {
                        localStorage.setItem('token', json['token']);
                        window.location = '<?php echo Url::to(['/h5/user']);?>';
                    }
                });
            }
        }
    });
</script>
