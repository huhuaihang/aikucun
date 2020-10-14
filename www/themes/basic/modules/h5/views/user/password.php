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

$this->title = '设置新密码';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">设置新密码</div>
    </header>
    <div class="container">
        <!--设置密码-->
        <form @submit.prevent="submit">
            <ul class="b_reset_code b_magt">
                <?php if (!empty(Yii::$app->user->identity['password'])) {?>
                <li>
                    <input class="b_first b_jisuan" type="password" placeholder="请输入原密码" v-model="UserPasswordForm.old_password">
                </li>
                <?php }?>
                <li class="ubb b_magt">
                    <input class="b_first b_jisuan" type="password" placeholder="请输入新密码" v-model="UserPasswordForm.password">
                </li>
                <li>
                    <input class="b_first b_jisuan" type="password" placeholder="重新确认新密码" v-model="UserPasswordForm.re_password">
                </li>
            </ul>
            <button class="b_confrim">确定</button>
        </form>
    </div>
</div>
<script>
    new Vue({
        el: '#app',
        data: {
            UserPasswordForm: {
                old_password: '',
                password: '',
                re_password: ''
            }
        },
        methods: {
            submit: function () {
                apiPost('<?php echo Url::to(['/api/user/save-password']);?>', this.UserPasswordForm, function (json) {
                    if (callback(json)) {
                        window.location = '<?php echo Url::to(['/h5/user/profile']);?>';
                    }
                });
            }
        }
    });
</script>
