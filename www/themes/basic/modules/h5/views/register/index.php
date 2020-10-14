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

$this->title = '注册';
?>
<style>
    body {background:#fff;}
</style>
<div class="box" id="app">
    <div class="new_login">
    <div class="login_head"><img src="/images/banner1.png"></div>
<!--        <div class="login_tab">-->
<!--            <a href="--><?php //echo Url::to(['/h5/login']);?><!--">登录</a>-->
<!--            <a href="--><?php //echo Url::to(['/h5/register']);?><!--" class="a1">注册</a>-->
<!--        </div>-->
        <!--tab-->
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
                <input type="text" placeholder="姓名" v-model="UserRegisterForm.real_name" ref="nickname">
            </div>
            <div>
                <label></label>
                <input type="password" placeholder="设置登录密码" v-model="UserRegisterForm.password" ref="password">
            </div>
            <div>
                <label></label>
                <input type="text" placeholder="邀请码" v-model="UserRegisterForm.invite_code" ref="invite_code">
                <img src="/images/tishi.png" alt="" style="width: 8%;" class="b_gzhao">
            </div>
<!--            <div>-->
<!--                <label></label>-->
<!--                <input type="text" placeholder="团队节点邀请码" v-model="UserRegisterForm.team_invite_code" ref="team_invite_code">-->
<!--                <img src="/images/tishi.png" alt="" style="width: 8%;" class="b_gzhaos">-->
<!--            </div>-->
            <button>注册</button>
            <div class="activation">
                <ul>
                    <li>
                        <a href="<?php echo Url::to(['/h5/register/activate']);?>">已有账号去绑定微信号</a>
                    </li>
<!--                    <li>-->
<!--                        <a href="--><?php //echo Url::to(['/h5/register/bind']);?><!--">已有账号去激活绑定</a>-->
<!--                    </li>-->
                </ul>
            </div>
        </form>
    </div>
    <div class="b_maskss">
        <div class="state">
            <div class="state_s">
                <img src="/images/colse_btn_03.png" alt="" class="b_closebtn">
            </div>
            <div class="state_b">
                <h2>邀请码说明</h2>
                <p>1. 云淘帮是会员制社交电商平台，每个注册的会员需填写邀请码；</p>
                <p>2. 获取邀请码后，请核对准确，以确保成功注册；</p>
                <p>3. 注册成功后，邀请人关系即绑定，不可更改；</p>
            </div>
            <div class="state_g">确定</div>
        </div>
    </div>
    <div class="b_maskss_s">
        <div class="state">
            <div class="state_s">
                <img src="/images/colse_btn_03.png" alt="" class="b_closebtn">
            </div>
            <div class="state_b">
                <h2>团队节点邀请码</h2>
                <p>1. 若您有指定的团队，除了填写邀请码外，还需填写团队指定人的邀请码；</p>
                <p>2. 若您无指定团队，此项无需填写，请直接忽略；</p>
            </div>
            <div class="state_g">确定</div>
        </div>
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
                real_name: '',
                nickname: localStorage.getItem('nickname'),
                avatar: localStorage.getItem('head_img'),
                union_id: localStorage.getItem('union_id'),
                invite_code: "<?php $code = Yii::$app->request->get('invite_code'); $in_code = Yii::$app->request->cookies->get('invite_code'); echo empty($code) ? $in_code : $code;?>",
                team_invite_code: "",
                save_session: 1,
                client_type: 'h5',
                open_id: localStorage.getItem('open_id'),
            },
            left_sec: 0,
            btn_txt: '获取',
            code: "<?php echo Yii::$app->request->get('code')?>",
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
                this.UserRegisterForm.nickname = localStorage.getItem('nickname');
                this.UserRegisterForm.avatar = localStorage.getItem('head_img');
                this.UserRegisterForm.union_id = localStorage.getItem('union_id');
                apiPost('<?php echo Url::to(['/api/user/wx-register']);?>', this.UserRegisterForm, function (json) {
                    if (callback(json)) {
                        localStorage.setItem('token', json['token']);
                        window.location = '<?php echo Url::to(['/h5/user']);?>';
                    }
                });
            },
            checkUser: function (open_id) {
                apiPost('<?php echo Url::to(['/api/user/check-user']);?>', {open_id:open_id}, function (json) {
                    //alert(json['type']);
                    if (callback(json)) {
                        if (json['type'] == 1 || json['type'] == 2) {
                            localStorage.setItem('token', json['token']);
                            window.location = '<?php echo Url::to(['/h5/user']);?>';
                        }
//                        else if (json['type'] == 2) {
//                            window.location = '<?php //echo Url::to(['/h5/register/activate']);?>//';
//                        }
                        else if (json['type'] == 3) {
                            window.location = '<?php //echo Url::to(['/h5/register/index']);?>//';
                        }
                    }
                });
            },
            checkUserNew: function (open_id, union_id) {
                apiPost('<?php echo Url::to(['/api/user/check-user-new']);?>', {open_id:open_id, union_id:union_id}, function (json) {
                    //alert(json['type']);
                    if (callback(json)) {
                        if (json['type'] == 1 || json['type'] == 2) {
                            localStorage.setItem('token', json['token']);
                            window.location = '<?php echo Url::to(['/h5/user']);?>';
                        }
                        else if (json['type'] == 3) {
                            window.location = '<?php //echo Url::to(['/h5/register/index']);?>//';
                        }
                    }
                });
            },
            getInfo: function () {
                <?php
                $api = new WeixinMpApi();
                $code = Yii::$app->request->get('code');
                if (empty($code)) {
                    $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true), 'snsapi_userinfo') . '";', View::POS_HEAD);
                    return;
                } else {
                    // 根据微信code获取openid
                    try {
                        $info = $api->getInfoAccessToken($code,'all');
                        Yii::warning($info);
                        //$openid = $api->code2Openid($code);
                        $this->registerJs("window.localStorage.setItem('open_id', '{$info['openid']}');", View::POS_HEAD);
                        //$user_info = $api->getInfo2($info['access_token'],$info['openid']);
                        $user_info = $api->getInfo($info['access_token'],$info['openid']);
                        Yii::warning($user_info);
                        if ($user_info) {
                            $this->registerJs("window.localStorage.setItem('nickname', '{$user_info['nickname']}');");
                            $this->registerJs("window.localStorage.setItem('head_img', '{$user_info['headimgurl']}');");
                            $this->registerJs("window.localStorage.setItem('union_id', '{$user_info['unionid']}');", View::POS_HEAD);
                        }
                    } catch (Exception $e) {
                        Yii::warning($e->getMessage());
                        throw new Exception('无法获取用户OpenId。');
                    }
                }
                ?>

            },
            //getCode: function () {
//                apiGet('<?php //echo Url::to(['/h5/user/get-open'])?>//', '', function(json){
//                    alert(json['url']);
//                    window.location = json['url'];
//                });
<!--                --><?php
//                $api = new WeixinMpApi();
//                $code = Yii::$app->request->get('code');
//                if (empty($code)) {
//                    $this->registerJs('window.location.href="' . $api->codeUrl(Url::current([], true)) . '";', View::POS_HEAD);
//                    return;
//                } else {
//                    // 根据微信code获取openid
//                    try {
//                        $openid = $api->code2Openid($code);
//                        $this->registerJs("window.localStorage.setItem('open_id', '{$openid}');", View::POS_HEAD);
//                    } catch (Exception $e) {
//                        throw new Exception('无法获取用户OpenId。');
//                    }
//                }
//                ?>
            //},
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

            var open_id = localStorage.getItem('open_id');
            var ua = navigator.userAgent.toLowerCase();
            //通过正则表达式匹配ua中是否含有MicroMessenger字符串
            if(ua.match(/MicroMessenger/i)=="micromessenger") {
                if (((typeof(open_id) == 'string')) || this.code.length > 2) {
//                alert(2);
//                this.getOpenid(this.code);
//                alert(localStorage.getItem('open_id'));
                    //this.checkUser(localStorage.getItem('open_id'));
                    this.checkUserNew(localStorage.getItem('open_id'), localStorage.getItem('union_id'));
                } else {
                    //localStorage.setItem('token', '');
                    //this.getCode();
                    this.getInfo();
                    this.UserRegisterForm.nickname = localStorage.getItem('nickname');
                    this.UserRegisterForm.avatar = localStorage.getItem('head_img');
                }
            }
        }
});

    function page_init() {
        $('.b_gzhao').click(function(){
            $('.b_maskss').addClass('b_show');
        });
        $('.b_closebtn , .state_g').click(function(){
            $('.b_maskss').removeClass('b_show');
        });
        $('.b_gzhaos').click(function(){
            $('.b_maskss_s').addClass('b_show');
        });
        $('.b_closebtn , .state_g').click(function(){
            $('.b_maskss_s').removeClass('b_show');
        });
    }
</script>
