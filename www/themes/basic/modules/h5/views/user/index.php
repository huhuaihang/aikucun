<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
$this->registerJsFile('/js/clipboard.min.js');

$this->title = '用户中心';
?>
<div class="box" id="app">
<!--    <div class="new_me_top">会员</div>-->
<!--    <div class="new_me_head">-->
<!--        <div class="dl" v-if="user.length === 0">-->
<!--            <div class="dt">-->
<!--                <img src="/images/toux.png">-->
<!--            </div>-->
<!--            <div class="dd" v-if="user.length === 0">-->
<!--                <a :href="'/h5/user/login'">登录/</a>-->
<!--                <a :href="'/h5/user/register'">注册</a>-->
<!--                <a :href="'/h5/user/login'" class="a3"><img src="/images/new_me_16.png"></a>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="dl" v-else>-->
<!--            <div class="dt">-->
<!--                 <img :src="user.avatar">-->
<!--            </div>-->
<!--            <div class="dd">-->
<!--                <a :href="'/h5/user/profile'">{{ user.nickname }}</a>-->
<!--                <div class="huiyuan">-->
<!--                    <a :href="'/h5/user/level-info'">{{ user.level_name }}</a>-->
<!--                </div>-->
<!--                <a :href="'/h5/user/profile'" class="a3"><img src="/images/new_me_16.png"></a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="me_dingdan">-->
<!--        <div class="div1">-->
<!--            <span class="left">我的订单</span>-->
<!--            <a href="--><?php //echo Url::to(['/h5/order']);?><!--"><span class="right">全部订单 <img src="/images/dingdan_03.jpg"></span></a>-->
<!--        </div><!--div1-->
<!--        <div class="div2_box">-->
<!--            <div class="div2">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/order', 'search_status' => Order::STATUS_CREATED]);?><!--">-->
<!--                    <!--<dl>-->
<!--                        <!--<dt><img src="/images/new_me_02.png"></dt>-->
<!--                        <!--<dd>待付款</dd>-->
<!--                    <!--</dl>-->
<!--                <!--</a>-->
<!--                <a href="--><?php //echo Url::to(['/h5/order', 'search_status' => Order::STATUS_DELIVERED]);?><!--">-->
<!--                    <dl>-->
<!--                        <dt><img src="/images/new_me_03.png"></dt>-->
<!--                        <dd>待发货</dd>-->
<!--                    </dl>-->
<!--                </a>-->
<!--                <a href="--><?php //echo Url::to(['/h5/order', 'search_status' => Order::STATUS_RECEIVED]);?><!--">-->
<!--                    <dl>-->
<!--                        <dt><img src="/images/new_me_04.png"></dt>-->
<!--                        <dd>待收货</dd>-->
<!--                    </dl>-->
<!--                </a>-->
<!--                <a href="--><?php //echo Url::to(['/h5/order', 'search_status' => Order::STATUS_RECEIVED]);?><!--">-->
<!--                    <dl>-->
<!--                        <dt><img src="/images/new_me_05.png"></dt>-->
<!--                        <dd>待评价</dd>-->
<!--                    </dl>-->
<!--                </a>-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/order/refund']);?><!--">-->
<!--                    <!--<dl>-->
<!--                        <!--<dt><img src="/images/new_me_06.png"></dt>-->
<!--                        <!--<dd>退款/售后</dd>-->
<!--                    <!--</dl>-->
<!--                <!--</a>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="me">-->
<!--        <div class="text1">-->
<!--            <!--<div class="div1">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/user/fav-goods']);?><!--">-->
<!--                    <!--<p class="left">-->
<!--                        <!--<span class="span1"><img src="/images/new_me_07.png"></span>-->
<!--                        <!--<span class="span2">我的收藏</span>-->
<!--                    <!--</p>-->
<!--                    <!--<p class="right">-->
<!--                        <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    <!--</p>-->
<!--                <!--</a>-->
<!--            <!--</div>-->
<!--            <div class="clear"></div>-->
<!--            <!--<div class="div1">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/message']);?><!--">-->
<!--                    <!--<p class="left">-->
<!--                        <!--<span class="span1"><img src="/images/new_me_08.png"></span>-->
<!--                        <!--<span class="span2">我的消息</span>-->
<!--                    <!--</p>-->
<!--                    <!--<p class="right">-->
<!--                        <!--<span class="span1" v-if="have_new_msg">新消息</span>-->
<!--                        <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    <!--</p>-->
<!--                <!--</a>-->
<!--            <!--</div>-->
<!--            <div class="clear"></div>-->
<!--            <div class="div1">-->
<!--                <a href="--><?php //echo Url::to(['/h5/user/address']);?><!--">-->
<!--                    <p class="left">-->
<!--                        <span class="span1"><img src="/images/new_me_09.png"></span>-->
<!--                        <span class="span2">我的地址</span>-->
<!--                    </p>-->
<!--                    <p class="right">-->
<!--                        <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    </p>-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="clear"></div>-->
<!---->
<!---->
<!--        </div><!--text1-->
<!--        <!--<div class="text1">-->
<!--            <!--<div class="div1">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/user/my-agent']);?><!--">-->
<!--                    <!--<p class="left">-->
<!--                        <!--<span class="span1"><img src="/images/new_me_17.png"></span>-->
<!--                        <!--<span class="span2">我的代理</span>-->
<!--                    <!--</p>-->
<!--                    <!--<p class="right">-->
<!--                        <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    <!--</p>-->
<!--                <!--</a>-->
<!--            <!--</div>-->
<!--            <!--<div class="clear"></div>-->
<!--        <!--</div>-->
<!--        <div class="text1">-->
<!--             <!--<div class="div1">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/join']);?><!--">-->
<!--                    <!--<p class="left">-->
<!--                        <!--<span class="span1"><img src="/images/new_me_12.png"></span>-->
<!--                        <!--<span class="span2">我要入驻</span>-->
<!--                    <!--</p>-->
<!--                    <!--<p class="right">-->
<!--                        <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    <!--</p>-->
<!--                <!--</a>-->
<!--            <!--</div>-->
<!--            <div class="clear"></div>-->
<!--            <div class="div1">-->
<!--                <a href="--><?php //echo Url::to(['/h5/user/service-center']);?><!--">-->
<!--                    <p class="left">-->
<!--                        <span class="span1"><img src="/images/new_me_13.png"></span>-->
<!--                        <span class="span2">客服中心</span>-->
<!--                    </p>-->
<!--                    <p class="right">-->
<!--                        <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    </p>-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="clear"></div>-->
<!--        </div>-->
<!--        <div class="text1">-->
<!--            <div class="div1" v-if="user.status === 1">-->
<!--<!--                <a href="--><?php ////echo Url::to(['/h5/user/share']);?><!--<!--">-->
<!--                <a :href="'--><?php //echo Url::to(['/h5/user/recommend-qr-code']);?><!--?invite_code=' + user.invite_code">-->
<!--                    <p class="left">-->
<!--                        <span class="span1"><img src="/images/new_me_14.png"></span>-->
<!--                        <span class="span2">分享</span>-->
<!--                    </p>-->
<!--                    <p class="right">-->
<!--                        <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    </p>-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="div1" v-else="user.status === 2">-->
<!--                <a class="tip" onclick="tip()">-->
<!--                    <p class="left">-->
<!--                        <span class="span1"><img src="/images/new_me_14.png"></span>-->
<!--                        <span class="span2">分享</span>-->
<!--                    </p>-->
<!--                    <p class="right">-->
<!--                        <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    </p>-->
<!--                </a>-->
<!--            </div>-->
<!--            <!--div1-->
<!--            <div class="clear"></div>-->
<!--            <!--<div class="div1">-->
<!--                <!--<a href="--><?php //echo Url::to(['/h5/user/profile']);?><!--">-->
<!--                    <!--<p class="left">-->
<!--                        <!--<span class="span1"><img src="/images/new_me_15.png"></span>-->
<!--                        <!--<span class="span2">设置</span>-->
<!--                    <!--</p>-->
<!--                    <!--<p class="right">-->
<!--                        <!--<span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    <!--</p>-->
<!--                <!--</a>-->
<!--            <!--</div>-->
<!--        </div>-->
<!--        <div class="text1">-->
<!--            <div class="div1" v-if="user.is_sale ==1">-->
<!--                <a href="--><?php //echo Url::to(['/h5/user/shop']);?><!--">-->
<!--                    <p class="left">-->
<!--                        <span class="span1"><img src="/images/new_me_18.png"></span>-->
<!--                        <span class="span2">我的店铺</span>-->
<!--                    </p>-->
<!--                    <p class="right">-->
<!--                        <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                    </p>-->
<!--                </a>-->
<!--            </div>-->
<!--            <div class="clear"></div>-->
<!--        </div>-->
<!--    </div>-->

    <div class="user_a">
        <div class="dl" v-if="user.length === 0">
            <div class="dt">
                <img src="/images/toux.png">
            </div>
            <div class="dd" v-if="user.length === 0">
                <a :href="'/h5/user/login'">登录/</a>
                <a :href="'/h5/user/register'">注册</a>
                <a :href="'/h5/user/login'" class="a3"><img src="/images/new_me_16.png"></a>
            </div>
        </div>
        <div class="user_a_s" v-else>
            <div class="user_a_v">
                <a :href="'/h5/user/profile'"><img :src="user.avatar"></a>
            </div>
            <div class="user_a_d">
                <a :href="'/h5/user/profile'">{{ user.nickname }}</a>
                <div class="user_a_yr">
                    <img :src="user.level_logo" alt="">
                </div>
<!--                <p v-if="user.invite_code!=''">邀请码：<span id="codeNum">{{ user.invite_code }}</span></p>-->
<!--                <p v-if="user.invite_code!=''" class="user-span" id="codeBtn" data-clipboard-target="#input">复制</p>-->
            </div>
            <div class="user_a_f">
                <a href="<?php echo Url::to(['/h5/notice/list']);?>"><img src="/images/user_s.png" alt=""></a>
                <div v-if="!is_sign" class="user_q_d" @click="sign()">
                    <p>签到</p>
                </div>
                <div v-if="is_sign" class="user_q_d">
                    <p>已签到</p>
                </div>
            </div>
        </div>
<!--        <div class="user_ji">-->
<!--            <ul>-->
<!--                <li>-->
<!--                    <p>{{ user.subsidy_money }}</p>-->
<!--                    <p>补贴</p>-->
<!--                </li>-->
<!--                <li>-->
<!--                    <p>{{ user.commission }}</p>-->
<!--                    <p>佣金</p>-->
<!--                </li>-->
<!--                <li>-->
<!--                    <p>{{ user.team_count }}</p>-->
<!--                    <p>团队</p>-->
<!--                </li>-->
<!--            </ul>-->
<!--        </div>-->
    </div>
    <div class="order_ss" style="position: relative">
        <div class="order_dd">
            <p>我的订单</p>
            <a href="<?php echo Url::to(['/h5/order']);?>"><span>全部订单<img src="/images/dingdan_03.jpg" alt=""></span></a>
        </div>
        <ul>
            <li>
                <a href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_PACKED]);?>">
                    <img src="/images/new_me_03.png" alt="">
                    <p>待发货</p>
                    <span class="amount"  v-if="order_count.wait_send>0">{{order_count.wait_send}}</span>
                </a>
            </li>
            <li>
                <a href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_DELIVERED]);?>">
                    <img src="/images/new_me_04.png" alt="">
                    <p>待收货</p>
                    <span class="amount" v-if="order_count.wait_receive>0">{{order_count.wait_receive}}</span>
                </a>
            </li>
            <li>
                <a href="<?php echo Url::to(['/h5/order', 'search_status' => Order::STATUS_RECEIVED]);?>">
                    <img src="/images/new_me_05.png" alt="">
                    <p>已收货</p>
                    <span class="amount"  v-if="order_count.receive>0">{{order_count.receive}}</span>
                </a>
            </li>
            <li>
                <a href="<?php echo Url::to(['/h5/order/refund']);?>">
                    <img src="/images/new_me_06.png" alt="">
                    <p>退款/售后</p>
                    <span class="amount" v-if="order_count.refund>0">{{order_count.refund}}</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="user_list">
<!--        <div class="div1" v-if="user.is_sale ==1">-->
<!--            <a href="--><?php //echo Url::to(['/h5/user/shop']);?><!--">-->
<!--                <p class="left">-->
<!--                    <span class="span1"><img src="/images/user_l1.png"></span>-->
<!--                    <span class="span2">我的店铺</span>-->
<!--                </p>-->
<!--                <p class="right">-->
<!--                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                </p>-->
<!--            </a>-->
<!--        </div>-->
        <div class="div1">
            <a href="<?php echo Url::to(['/h5/user/address']);?>">
                <p class="left">
                    <span class="span1"><img src="/images/user_l2.png"></span>
                    <span class="span2">地址管理</span>
                </p>
                <p class="right">
                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                </p>
            </a>
        </div>
<!--        <div class="div1" v-if="user.status === 1">-->
<!--            <a :href="'--><?php //echo Url::to(['/h5/user/recommend-qr-code']);?><!--?invite_code=' + user.invite_code">-->
<!--                <p class="left">-->
<!--                    <span class="span1"><img src="/images/user_l3.png"></span>-->
<!--                    <span class="span2">生成个人海报</span>-->
<!--                </p>-->
<!--                <p class="right">-->
<!--                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                </p>-->
<!--            </a>-->
<!--        </div>-->
<!--        <div class="div1" v-else="user.status === 2">-->
<!--            <a class="tip" onclick="tip()">-->
<!--                <p class="left">-->
<!--                    <span class="span1"><img src="/images/user_l3.png"></span>-->
<!--                    <span class="span2">生成个人海报</span>-->
<!--                </p>-->
<!--                <p class="right">-->
<!--                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>-->
<!--                </p>-->
<!--            </a>-->
<!--        </div>-->
        <div class="div1">
            <a href="<?php echo Url::to(['/h5/user/service-center']);?>">
                <p class="left">
                    <span class="span1"><img src="/images/user_l4.png"></span>
                    <span class="span2">客服</span>
                </p>
                <p class="right">
                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                </p>
            </a>
        </div>
        <div class="div1">
            <a href="<?php echo Url::to(['/h5/user/score']);?>">
                <p class="left">
                    <span class="span1"><img src="/images/user_l6.png"></span>
                    <span class="span2">我的积分</span>
                </p>
                <p class="right">
                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                </p>
            </a>
        </div>
        <div class="div1">
            <a href="<?php echo Url::to(['/h5/user/pack-coupon']);?>">
                <p class="left">
                    <span class="span1"><img src="/images/user_l7.png"></span>
                    <span class="span2">我的卡券</span>
                </p>
                <p class="right">
                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                </p>
            </a>
        </div>
        <div class="div1">
            <a href="/h5/user/profile">
                <p class="left">
                    <span class="span1"><img src="/images/user_l5.png"></span>
                    <span class="span2">更多设置</span>
                </p>
                <p class="right">
                    <span class="span2"><img src="/images/huiyuanzhongxin_16.jpg"></span>
                </p>
            </a>
        </div>
    </div>
    <div class="sj-t_s1" >
        <div class="sign_c1">
            <div class="sign_c2">
                <img src="/images/sign_s3.png" alt="">
                <p>已累计签到{{total_day}}天，继续加油！</p>
            </div>
            <div class="sign_c3">
                <ul>
                    <li >日</li>
                    <li>一</li>
                    <li>二</li>
                    <li>三</li>
                    <li>四</li>
                    <li>五</li>
                    <li>六</li>
                    <li v-for="sign in signList" :class="sign.is_sign === 1 ? 'sign_c3_li' : ''">{{sign.day}}</li>

                </ul>
            </div>
            <div class="sign_c4">
                <div class="sign_c4_s1">
                    <img src="/images/sign_s6.png" alt="">
                </div>
                <p v-html="sign_reword_word"></p>
            </div>
        </div>
        <div class="sign_c1_g">
            <img src="/images/sign_s7.png" alt="" class="sign_gb" >
        </div>
    </div>
<!--    <div  class="sj-t" style="z-index: 999;">-->
<!--        <div class="user_qk">-->
<!--            <p>恭喜你获得{{sign_score}}积分</p>-->
<!--            <span class="user_qk_span">确定</span>-->
<!--        </div>-->
<!--    </div>-->
</div>
<div class="box">
    <?php echo $this->render('../layouts/_bottom_nav');?>
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            have_new_msg: false, // 是否有新消息
            user: {}, // 用户信息
            can_withdraw: false, // 是否可以提现
            sign_score:'',//签到积分
            signList:[],//签到日历
            is_sign:0,//0未签到 1已签到
            total_day:0,//累计签到
            sign_reword_word:'',//签到规则
            order_count:{},
        },
        methods:{
            sign: function () {
                if (this.is_sign === 0) {
                    apiGet('<?php echo Url::to(['/api/user/sign']);?>', {}, function (json) {
                        if (callback(json)) {
                            // app.sign_score = json['score'];
                            app.is_sign = 1;
                            if (json['is_sign_reword'] == 1) {
                                layer.msg('签到成功,获得' + json['score'] + '积分,您满勤获得奖励'+json['sign_reword_score'] + '积分!')
                            } else {
                                layer.msg('签到成功,获得' + json['score'] + '积分')
                            }

                            app.$nextTick(function () {
                                app.loadSignList();
                            });
                        }
                    });
                }

            },
            /**
             * 获取签到日历
             */
            loadSignList: function () {

                apiGet('/api/user/sign-list','', function (json) {
                    if (callback(json)) {
                        console.log(json)
                        app.signList=json['list'];
                        app.total_day=json['count'];
                    }
                });
            },


        },
        created: function () {

            // 获取订单状态数量
            apiGet('<?php echo Url::to(['/api/user/order-count']);?>', {}, function (json) {
                if (callback(json)) {
                    app.order_count=json['count'][0];
                    console.log(app.order_count)
                }
            });
            // 获取用户信息
            apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                if (callback(json)) {
                    app.user = json['user'];

                }
            });
            // 检查是否有新消息
            apiGet('<?php echo Url::to(['/api/user/check-new-message']);?>', {}, function (json) {
                if (callback(json)) {
                    app.have_new_msg = json['have_new_msg'];
                }
            });
            apiGet('<?php echo Url::to(['/api/user/check-can-withdraw']);?>', {}, function (json) {
                if (callback(json)) {
                    app.can_withdraw = json['can_withdraw'];
                }
            });
            // 获取用户是否签到状态
            apiGet('<?php echo Url::to(['/api/user/check-sign']);?>', {}, function (json) {
                if (callback(json)) {
                    if(json['is_sign'] == 1)
                    {
                        app.is_sign=1;
                        app.loadSignList();

                    }
                }
            });
            // 获取签到规则
            apiGet('<?php echo Url::to(['/api/default/get-system?config=sign_reword_word']);?>', {}, function (json) {
                if (callback(json)) {
                    app.sign_reword_word = json['system'].replace(/\\n/gm,"<br/>");
                }
            });
        }
    });

    function tip() {
        layer.msg('您还不是有效会员哦！\n' +
            '购买大礼包成为有效会员！', function () {});
    }

    function copyArticle(event) {
        const range = document.createRange();
        range.selectNode(document.getElementById('codeNum'));

        const selection = window.getSelection();
        if(selection.rangeCount > 0) selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('copy');
        alert("复制成功！");
    }
   // document.getElementById('codeBtn').addEventListener('click', copyArticle, false);
    $(document).ready(function(){
        $(".sj-t").height($(window).height());
        // $(".user_q_d").click(function(){
        //     $(".sj-t").show();
        // });
        $(".user_qk_span").click(function(){
            $(".sj-t").hide();
        });
    });
    $(document).ready(function(){
        $(".sj-t_s1").height($(window).height());
        $(".user_q_d").click(function(){
            $(".sj-t_s1").show();
        });
        $(".sign_gb").click(function(){
            $(".sj-t_s1").hide();
        });
        // $(".announcement_y").click(function(){
        //     $(".announcement").hide();
        // });
    });
</script>
