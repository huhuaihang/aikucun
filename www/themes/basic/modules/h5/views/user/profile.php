<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\assets\VueRouterAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
VueRouterAsset::register($this);

$this->title = '个人中心';
?>
<div id="app">
    <router-view></router-view>
</div>
<script type="text/x-template" id="profile">
    <div class="box">
        <header class="mall-header">
            <div class="mall-header-left">
                <a href="<?php echo Url::to(['/h5/user']);?>"><img src="/images/11_1.png" alt="返回"></a>
            </div>
            <div class="mall-header-title">个人中心</div>
        </header>
        <div class="container" style="width: 94%; margin-top: 1.2rem; border-bottom: none;">
            <div class="grzx_xg">
                <div class="div1_box">
                    <div class="div1" id="avatar_box" style="height: 1.7rem; line-height: 1.7rem; border: 8px; border-radius: 8px;">
                        <p class="left">
                            <span class="span2">头像</span>
                        </p>
                        <p class="right">
                            <span class="head_icon span1" id="localImag" style=" position:relative">
                                <img :src="user.avatar" alt="" id="user_avatar" onclick="$('#file_user_avatar').click()" />
                                <input id="file_user_avatar" type="file" name="files" style="display:none;" @change="uploadAvatar" />
                            </span>
<!--                            <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
                        </p>
                    </div><!--div1-->
                    <div class="div1" style="margin-top: .2rem; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <a href="#/nickname">
                            <p class="left">
                                <span class="span2">昵称</span>
                            </p>
                            <p class="right">
                                <span class="span3">{{user.nickname}}</span>
                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                    <div class="div1">
                        <a href="#/gender">
                            <p class="left">
                                <span class="span2">性别</span>
                            </p>
                            <p class="right">
                                <span class="span3">{{user.gender_str}}</span>
                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                    <div class="div1" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                        <a href="<?php echo Url::to(['/h5/user/change-mobile']);?>">
                            <p class="left">
                                <span class="span2">登录手机号码</span>
                            </p>
                            <p class="right">
                                <span class="span3">{{user.mobile}}</span>
                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                </div><!--div1_box-->
                <div class="div2_box">
                    <!--<div class="div1">-->
                        <!--<a href="<?php echo Url::to(['/h5/user/address']);?>">-->
                            <!--<p class="left">-->
                                <!--<span class="span2">收货地址</span>-->
                            <!--</p>-->
                            <!--<p class="right">-->
                                <!--<span class="span3"><?php // TODO:显示用户收货地址?></span>-->
                                <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
                            <!--</p>-->
                        <!--</a>-->
                    <!--</div>-->
                    <div class="div1" style="border-radius: 8px; border-bottom: none;">
                        <a href="<?php echo Url::to(['/h5/user/password']);?>">
                            <p class="left">
                                <span class="span2">修改密码</span>
                            </p>
                            <p class="right">
                                <span class="span3"><span v-if="user.have_password != 1">没有设置</span></span>
                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                    <!--   <div class="div1" style="margin-top: .2rem; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                        <a href="<?php echo Url::to(['/h5/user/level-info']);?>">
                            <p class="left">
                                <span class="span2">我的成长值</span>
                            </p>
                            <p class="right">
                                <span class="span3">{{user.level_name}}</span>
                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div>
                    <div class="div1" style="border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                        <a href="#/nickname">
                            <p class="left">
                                <span class="span2">我的积分</span>
                            </p>
                            <p class="right">
                                <span class="span3">{{user.score}}</span>
                                <!--                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                            </p>
                        </a>
                    </div>  -->
<!--                    <div class="div1">-->
<!--                        <a href="--><?php //echo Url::to(['/h5/user/payment-password']);?><!--">-->
<!--                            <p class="left">-->
<!--                                <span class="span2">支付密码</span>-->
<!--                            </p>-->
<!--                            <p class="right">-->
<!--                                <span class="span3"><span v-if="user.have_payment_password != 1">没有设置</span></span>-->
<!--                                <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                            </p>-->
<!--                        </a>-->
<!--                    </div>-->
                    <!--div1-->
<!--                    <a class="b_log_out1" href="javascript:void(0)" @click="logout()">退出登录</a>-->
                </div><!--div1_box-->
            </div><!--grzx_xg-->
        </div>
    </div>
</script>
<script type="text/x-template" id="nickname">
    <div class="box">
        <header class="mall-header">
            <div class="mall-header-left">
                <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
            </div>
            <div class="mall-header-title">修改昵称</div>
            <div class="mall-header-right">
                <button @click="saveNickname()">确定</button>
            </div>
        </header>
        <div class="container"  style="width: 94%;">
            <!--修改昵称-->
            <div class="b_magt b_rewrite ubb ubt ">
                <input class="b_rewrite1 " type="text" placeholder="输入新的昵称" v-model="user.nickname"/>
                <div class="b_cancle_img">
                    <img src="/images/b_cancle.png"/>
                </div>
            </div>
            <p class="b_write_tips">4-20个字符，可由中英文、数字、“_”、“-”组成</p>
        </div>
    </div>
</script>
<script type="text/x-template" id="gender">
    <div class="box">
        <header class="mall-header">
            <div class="mall-header-left">
                <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
            </div>
            <div class="mall-header-title">修改性别</div>
        </header>
        <div class="container"  style="width: 94%;">
            <div class="sex">
                <div class="div1" :class="{aw: user.gender == 1}"
                     @click="saveGender(1, '男')">
                    <span class="span1">男</span>
                    <span class="span2"><img src="/images/sex.jpg"></span>
                </div>
                <div class="clear"></div>
                <div class="div1" :class="{aw: user.gender == 2}"
                     @click="saveGender(2, '女')">
                    <span class="span1">女</span>
                    <span class="span2"><img src="/images/sex.jpg"></span>
                </div>
                <div class="clear"></div>
                <div class="div1" :class="{aw: user.gender == 9}"
                     @click="saveGender(9, '保密')">
                    <span class="span1">保密</span>
                    <span class="span2"><img src="/images/sex.jpg"></span>
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div><!--box-->
</script>
<script>
    var bus = new Vue();

    const Profile = {
        template: '#profile',
        data: function () {
            return {
                user: {}
            };
        },
        created: function () {
            var self = this;
            bus.$on('user_info', function (user) {
                self.user = user;
            })
        },
        mounted: function () {
            bus.$emit('get_user');
        },
        methods: {
            uploadAvatar: function (e) {
                var self = this;
                apiFile('<?php echo Url::to(['/api/default/upload', 'dir' => 'user_avatar']);?>', e.target.files[0], function (json) {
                    if (callback(json)) {
                        var url = json['url'];
                        self.user.avatar = url;
                        apiPost('<?php echo Url::to(['/api/user/save-avatar']);?>', {'avatar':json['uri']}, function (json) {
                            if (callback(json)) {
                                $('#user_avatar').attr('src', url);
                            }
                        });
                    }
                });
            },
            logout: function () {
                localStorage.removeItem('token');
                window.location.href = '<?php echo Url::to(['/h5/user/logout']);?>';
            }
        }
    };
    const Nickname = {
        template: '#nickname',
        data: function () {
            return {
                user: {}
            };
        },
        created: function () {
            var self = this;
            bus.$on('user_info', function (user) {
                self.user = user;
            })
        },
        mounted: function () {
            bus.$emit('get_user');
        },
        methods: {
            saveNickname: function () {
                var self = this;
                if (self.user.nickname === '') {
                    return false;
                }
                if (!/^.{1,32}$/.test(self.user.nickname)) {
                    layer.msg('昵称格式错误。', function () {});
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/save-nickname']);?>', {'nickname':self.user.nickname}, function (json) {
                    if (callback(json)) {
                        layer.msg('修改成功。', function() {
                            window.history.go(-1);
                        });
                    }
                });
            }
        }
    };
    const Gender = {
        template: '#gender',
        data: function () {
            return {
                user: {}
            };
        },
        created: function () {
            var self = this;
            bus.$on('user_info', function (user) {
                self.user = user;
            })
        },
        mounted: function () {
            bus.$emit('get_user');
        },
        methods: {
            saveGender: function (gender, str) {
                this.user.gender = gender;
                this.user.gender_str = str;
                apiPost('<?php echo Url::to(['/api/user/save-gender']);?>', {'gender':gender}, function(json) {
                    if (callback(json)) {
                        layer.msg('修改成功。', function(){
                            window.history.go(-1);
                        });
                    }
                });
            }
        }
    };

    const router = new VueRouter({
        routes: [
            { path: '/', component: Profile },
            { path: '/nickname', component: Nickname },
            { path: '/gender', component: Gender }
        ]
    });

    var app = new Vue({
        el: '#app',
        data: function () {
            return {
                user: {}
            };
        },
        router: router,
        created: function () {
            // 获取用户信息
            apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                if (callback(json)) {
                    app.user = json['user'];
                    bus.$emit('user_info', app.user);
                    bus.$on('get_user', function () {
                        bus.$emit('user_info', app.user);
                    });
                }
            });
        }
    });
</script>
