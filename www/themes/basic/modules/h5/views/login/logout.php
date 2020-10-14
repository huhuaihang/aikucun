<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\WeixinMpApi;
use yii\helpers\Url;
use yii\web\View;

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
            code: "<?php echo Yii::$app->request->get('code')?>",
            open_id: '',
            invite_code: "<?php echo Yii::$app->request->get('invite_code')?>",
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
                    //alert(json['type']);
                    if (callback(json)) {
                        if (json['type'] == 1) {
                            localStorage.setItem('token', json['token']);
                            window.location.href = '<?php echo Url::to(['/h5/user']);?>';
                        } else if (json['type'] == 2) {
                            window.location = '<?php echo Url::to(['/h5/register/activate']);?>';
                        } else if (json['type'] == 3) {
                            window.location = '<?php echo Url::to(['/h5/register/index']);?>';
                        }
                    }
                });
            },
            getCode: function () {
//                apiGet('<?php //echo Url::to(['/h5/user/get-open'])?>//', '', function(json){
//                    window.location = json['url'];
//                });
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
                        throw new Exception('无法获取用户OpenId。');
                    }
                }
                ?>
            },
            getOpenid: function (code) {
                apiGet('<?php echo Url::to(['/api/user/get-open'])?>', {code : code}, function(json){
                    this.open_id = json['open_id'];
                    localStorage.setItem('open_id', json['open_id']);
                });
            },
        },
        mounted: function () {
            // 自动登录  || 有账号 激活 || 没有账号注册成功并 登录
            //自动登录  1先获取 openid 2判断是否后台有 3如果有 直接登录  4如果没有 选择（绑定填写邀请码注册登录|激活登录）
            var ua = window.navigator.userAgent.toLowerCase();
            var open_id = localStorage.getItem('open_id');
            var token = localStorage.getItem('token');
            //alert(open_id);
            //alert(token);
            localStorage.setItem('open_id', '');
            localStorage.setItem('token', '');

            }
        }
    });
</script>
