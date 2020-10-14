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
            open_id: '',
            invite_code: "<?php echo Yii::$app->request->get('invite_code')?>",
        },
        methods: {
            submit: function () {
                if (!/^\d{11}$/.test(this.UserLoginForm.mobile)) {
                    layer.msg('手机号码格式错误。', function () {
                    });
                    app.$refs.mobile.focus();
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/login']);?>', this.UserLoginForm, function (json) {
                    if (callback(json)) {
                        localStorage.setItem('token', json['token']);
                        window.location = '<?php echo Url::to(['/h5']);?>';
                    }
                });
            }
        }
    });
</script>
