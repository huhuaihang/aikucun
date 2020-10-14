<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->title = '用户中心';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">我的团队</div>
    </header>
<!--    <div style="height: 1.3rem;"></div>-->
<!--    <div class="sj">-->
<!--        <input type="text" placeholder="输入手机号">-->
<!--    </div>-->
<!--    <p class="sj1">此处已注册的用户显示昵称，未注册的用户显示“该手机号未注册”</p>-->
<!--    <a class="sj2">激活</a>-->
<!--    <div class="sj-t">-->
<!--        <div class="ts1">-->
<!--            <img class="tgb" src="/images/popover1.png" alt="">-->
<!--            <p>请核对信息</p>-->
<!--            <p style="text-align: center">-->
<!--                手机号：122333333<br/>-->
<!--                真实姓名：郝世民<br/>-->
<!--                昵称：郝世民-->
<!--            </p>-->
<!--            <div class="tqd">-->
<!--                <div>-->
<!--                    <a class="tsqd">确定</a>-->
<!--                </div>-->
<!--                <div>-->
<!--                    <a class="tsqx">取消</a>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="ts2">-->
<!--            <img class="tgb" src="/images/popover1.png" alt="">-->
<!--            <p>友情提示</p>-->
<!--            <p>-->
<!--                您已激活该会员，并且已经为ta下单了，请提醒ta准备收货哦！-->
<!--            </p>-->
<!--            <a class="txd">确定</a>-->
<!--        </div>-->
<!--    </div>-->
    <div id="wrap_s_z">
        <div id="tit_s_z">
            <div>
                <span class="select_s_z">100</span>
                <span class="select_s_z">总人数</span>
            </div>
            <div>
                <span>100</span>
                <span>已激活</span>
            </div>
            <div>
                <span>100</span>
                <span>未激活</span>
            </div>
        </div>
        <div id="login_s_z">
            <div class="login_s_z show_s_z">
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span">已激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span1">未激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
            </div>
            <div class="login_s_z">
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span">已激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span">已激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
            </div>
            <div class="login_s_z">
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span1">未激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
                <div class="team_s">
                    <div class="user_a_v">
                        <img src="/images/13.jpg" alt="">
                    </div>
                    <div class="team_z">
                        <p>张三<span class="team_span1">未激活</span></p>
                        <p>手机号：<span>123456874</span></p>
                        <p>加入时间：<span>2019年3月31日</span></p>
                    </div>
                    <div class="team_f">
                        <p>会员</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><!--box-->

<script>
    var app = new Vue({
        el: '#app',
        data: {
            user: {}, // 用户信息
        },
        created: function () {
            // 获取用户信息
            apiGet('<?php echo Url::to(['/api/user/check-child-mobile']);?>', {}, function (json) {
                if (callback(json)) {
                    app.user = json['user'];
                }
            });
        }
    });
     $(document).ready(function(){
         $(".sj-t").height($(window).height());
         $(".sj2").click(function(){
             $(".sj-t").show();
             $(".ts1").show();
             $(".ts2").hide();
         });
         $(".tsqd").click(function(){
             $(".ts2").show();
             $(".ts1").hide();
         });
         $(".tsqx").click(function(){
             $(".sj-t").hide();
         });
         $(".txd").click(function(){
             $(".sj-t").hide();
         });
         $(".tgb").click(function(){
             $(".sj-t").hide();
         });
     });
    $('#tit_s_z div').click(function() {
        var i = $(this).index();//下标第一种写法
        $(this).find('span').addClass('select_s_z');
        $(this).siblings('div').find('span').removeClass('select_s_z');
        $('.login_s_z').eq(i).show().siblings().hide();
    });
</script>
