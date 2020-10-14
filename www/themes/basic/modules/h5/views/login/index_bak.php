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

$this->title = '用户登录';
?>
<style>
    body {background:#fff;}
</style>
<div class="box" id="app">
    <div class="new_login">
        <div class="login_head"><img src="/images/banner1.png"></div>
        <div class="login_tab">
            <a href="<?php echo Url::to(['/h5/login']);?>" class="a1">登录</a>
            <a href="<?php echo Url::to(['/h5/register']);?>">注册</a>
        </div><!--tab-->
        <form @submit.prevent="submit" class="login_form">
            <div class="div1">
                <label></label>
                <input type="number" placeholder="请输入手机号" v-model="UserLoginForm.mobile" ref="mobile" />
            </div>
            <div class="div2">
                <label></label>
                <input type="password" placeholder="请输入密码" v-model="UserLoginForm.password" />
            </div>
            <button>登录</button>
            <a href="<?php echo Url::to(['/h5/login/lost-password']);?>"  class="forgot_password">忘记密码？</a>
        </form>
    </div><!--new_login-->
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            UserLoginForm: {
                mobile: '',
                password: '',
                save_session: 1 // 同时在服务端记录session
            },
            code: {
                code: "<?php echo Yii::$app->request->get('code')?>",
            },
            open_id: '',
        },
        methods: {
            submit: function () {
                if (!/^\d{11}$/.test(this.UserLoginForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {});
                    app.$refs.mobile.focus();
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/login']);?>', this.UserLoginForm, function (json) {
                    if (callback(json)) {
                        localStorage.setItem('token', json['token']);
                        var fromurl = document.referrer;
                        if (fromurl && (fromurl.indexOf('login') < 0)) {
                            window.location = fromurl;
                        } else {
                            window.location = '<?php echo Url::to(['/h5/user']);?>';
                        }
                    }
                });
            },
            checkUser: function (open_id) {
                apiPost('<?php echo Url::to(['/api/user/check-user']);?>', {open_id:open_id}, function (json) {
                    if (callback(json)) {
                        if (json['type'] == 1) {
                            localStorage.setItem('token', json['token']);
                            window.location = '<?php echo Url::to(['/h5/user']);?>';
                        } else if (json['type'] == 2) {
                            window.location = '<?php echo Url::to(['/h5/register/activate']);?>';
                        } else if (json['type'] == 3) {
                            window.location = '<?php echo Url::to(['/h5/register/index']);?>';
                        }
                    }
                });
            },
            getCode: function () {
                apiGet('<?php echo Url::to(['/h5/user/get-open'])?>', '', function(json){
                    if (callback(json)) {
                        this.code.code = json;
                    }
                });
            },
            getOpenid: function (code) {
                apiGet('<?php echo Url::to(['/api/user/get-open'])?>', {code : code}, function(json){
                    if (callback(json)) {
                        this.open_id = json;
                        this.checkUser(json);
                    }
                });
            },
        },
        mounted: function () {
            // 自动登录  || 有账号 激活 || 没有账号注册成功并 登录
            //自动登录  1先获取 openid 2判断是否后台有 3如果有 直接登录  4如果没有 选择（绑定填写邀请码注册登录|激活登录）
            var ua = window.navigator.userAgent.toLowerCase();
            //通过正则表达式匹配ua中是否含有MicroMessenger字符串
            if(ua.match(/MicroMessenger/i) == 'micromessenger') {
                if (this.code.code) {
                    this.getOpenid(this.code.code);
                } else {
                    var code = this.getCode();
                    this.code.code = code;
                    if (code) {
                        this.getOpenid(this.code.code);
                    }
                }
            }
        }
    });
</script>
