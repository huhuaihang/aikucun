<?php

use app\assets\LayerAsset;
use app\models\System;
use app\widgets\AdWidget;
use yii\helpers\Url;
use app\assets\VueAsset;
use yii\web\View;
/**

 * @var $this \yii\web\View
 * @var $goods_list \app\models\Goods[]
 * @var $today_list \app\models\Goods[]
 * @var $best_list \app\models\Goods[]
 * @var $notice_list \app\models\Notice[]
 * @var $nav_list \app\models\Ad[]
 * @var $is_skip integer 0 1
 */

$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');
LayerAsset::register($this);
VueAsset::register($this);
$this->registerCssFile('/style/swiper.min.css');
$this->registerJsFile('/js/swiper-3.2.5.min.js',['position' => $this::POS_HEAD]);
$this->title = System::getConfig('site_index_title', '主页');
$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);
?>
<style>
    .y_nav li a.aa{
        color: #cc1000;!important;
        /*border-bottom: 2px solid #cc1000;*/
        font-size: 1.5em;
        position: relative;

    }
    .aa:after{
        content:'';
        display:block;
        /*开始时候下划线的宽度为0*/
        width: 0.5em;
        height:3px;
        position:absolute;
        left:40%;
        bottom:-10px;
        background:#cc1000;
        /*这里我们设定所有改变都有动画效果，可以自己指定样式才有动画效果*/
        transition:all 0.3s ease-in-out;
    }
    #layui-layer1{
        top:0!important;
        left: 0!important;
        width: 100%!important;
    }
    .layui-layer-setwin{
        top:32px!important;
        right:30px!important;
    }
    .layui-layer-msg{
     z-index:9999999998!important;
    }

</style>


<div class="box" id="app">
    <!-- app广告下载 -->
    <div id="app-banner" style="display: none"><a href="https://sj.qq.com/myapp/detail.htm?apkName=com.yunshang.yuntaob"><img src="images/app.png"  style="width: 100%"/></a> </div>
    <div class="top-head" style="top: 2.2rem;" >
        <div class="sign" @click="sign()">
            <img src="/images/Sign_s1.png" alt="" v-if="is_sign == 0">
            <img src="/images/Sign_s1_1.png" alt="" v-if="is_sign == 1">
        </div>
        <header class="mall-header mall-header-index" style="width: 70% !important;top: 2.2rem; margin-left: 1.15rem;">
            <div class="mall-header-title" style="right: .1rem !important;">
                <a href="<?php echo Url::to(['/h5/default/search']);?>">输入商家名称或品类</a>
                <p>云淘帮</p>
            </div>
        </header>
        <div class="top-head-y" style="background: #fff;">
            <a href="<?php echo Url::to(['/h5/notice/list']);?>">
                <img src="/images/xiaoxi.png" alt="">
            </a>
        </div>
        <div class="y_nav">
            <nav>
            <ul :style={width:setwidth+'rem'} >

                <li   v-for="(nav,index) in navList" @click="loadGoodsList(index,nav.id,1)" >

                    <a  :class="{'aa':nowIndex===index}"  href="#">{{nav.name}}</a>
                </li>
            </ul>
            </nav>
        </div>
    </div>
    <div class="container"  style="margin-top:3.2rem;">
        <div class="y_nav"></div>

        <div class="Y_banner" v-if="banner_list.length>0" >
            <div class="block_home_slider">

                <div :id="'home_slider_'+nowIndex" class="flexslider">
                    <ul class="slides">
                        <li v-for="banner in banner_list">
                            <div class="slide">
                                <a :href="banner.url"><img :src="banner.img" /></a>
                            </div>
                        </li>
                    </ul>
                    <ol class="flex-control-nav flex-control-paging"></ol>
                </div><!--home_slider-->
            </div><!--block_home_slider-->
        </div><!--Y_banner-->
        <div class="clear"></div>

        <div>
        <div class="Y_banner" v-if="nowIndex==0 ">
            <div class="block_home_slider">
                <div id="home_slider" class="flexslider">
                    <ul class="slides">

                        <li v-for="banner_index in index_list.ad1_list">
                            <div class="slide">
                                <a :href="banner_index.url"><img :src="banner_index.img" /></a>
                            </div>
                        </li>

                    </ul>
                    <ol class="flex-control-nav flex-control-paging"></ol>
                </div><!--home_slider-->
            </div><!--block_home_slider-->
        </div><!--Y_banner-->
        </div>




        <div class="swiper-container swiper_con" >
            <div class="swiper-wrapper" style="background-color: #fafafa;">
                <!-- 第一个swiper -->
                <div class="swiper-slide" ref="viewBox">

                <div class="clear"></div>
                <div class="index_s" style="width: 94%; margin: .2rem auto 0; border-radius: 10px;">
            <ul>


                <li style="position: relative;" v-for="nav in index_list.nav_list">
                    <a class="login" :data-id="nav.id" v-if="nav.name=='商学院'" href="#" @click="layerNotice(nav.url)">
                       <img v-if=" have_new_msg == true" :src="xin_pic" style="position: absolute; top: 0; right: .5rem;z-index: 99; width: .35rem; height: .35rem">
                        <img :src="nav.img" />
                        <p>{{nav.name}}</p>
                    </a>

                    <a class="login" v-else :data-id="nav.id" :href="nav.url">
                        <img :src="nav.img" />
                        <p>{{nav.name}}</p>
                    </a>
                </li>

            </ul>
        </div>
<!--        <div class="notice">-->
<!--            <img src="/images/hot.png" alt="">-->
<!--            <p>云商公告：</p>-->
<!--                <a v-for=" notice in index_list.notice_list" :href="notice.url">{{notice.title}}</a>-->
<!--        </div>-->
        <div class="advertising">
<!--            <div class="advertising_top">-->
<!---->
<!--           <a v-for="ad in  index_list.ad2_list" :href="ad.url"><img :src="ad.img" /></a>-->
<!---->
<!--            </div>-->
<!--            <div class="advertising_bottom">-->
<!--                <div class="advertising_left">-->
<!---->
<!--                    <a v-for="ad in  index_list.ad3_list" :href="ad.url"><img :src="ad.img" /></a>-->
<!--                </div>-->
<!--                <div class="advertising_right">-->
<!--                    <div class="advertising_z">-->
<!--                        <a v-for="ad in  index_list.ad4_list" :href="ad.url"><img :src="ad.img" /></a>-->
<!--                    </div>-->
<!--                    <div class="advertising_z">-->
<!--                        <a v-for="ad in  index_list.ad5_list" :href="ad.url"><img :src="ad.img" /></a>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
            <div class="advertising_up">
                <div class="advertising_up_s">
                    <a :href="ad.url" v-for="(ad,index) in  index_list.ad6_list" v-if="index<2">
                        <img :src="ad.img" alt="">
                    </a>

                </div>
                <div class="advertising_up_x">
                    <a :href="ad.url" v-for="(ad,index) in  index_list.ad6_list" v-if="index>=2">
                        <img :src="ad.img" alt="">
                    </a>
                </div>
            </div>
        </div>

        <div class="Yindex" style="margin-top: 0px;">

            <div class="Y_div4 vip_div" v-if="sale_list.length>0">
<!--                <h4 class="biaoti">-->
<!--                    <img src="/images/tuijian1_1.png" class="limited_img">-->
<!--                    <a href="/h5/goods/goods-sale-list" class="limited_a">查看更多</a>-->
<!--                </h4>-->
<!--                限时抢购广告-->
                <div class="limited_lh" v-for="ad in index_list.sale_ad">
                <a :href="ad.url"><img :src="ad.img" /></a>
                </div>
                <dl  v-for="goods in sale_list">
                    <a :href="'/h5/goods/view?id='+goods.id">
                        <dt style="position: relative;">
                            <div class="limited_ly" v-if="goods.amount-goods.sold_amount<=0" style="background:#c2c2c2">
                                <p >已抢光</p>
                            </div>
                            <div  class="limited_ly" v-else>
                                <p v-if="date.status == 1">{{goods.countTime}}</p>
                                <p v-if="date.status == 0">已结束</p>
                            </div>
<!--                            <div class="limited_tr">-->
<!--                                <p>每人限购3件</p>-->
<!--                            </div>-->
                            <img :src="goods.main_pic">
                        </dt>
                        <div class="s-list">
                            <dd class="dd1"><span class="span1">{{goods.title}}</span></dd>
                            <div class="limited">
                                <div class="limited_s">
                                    <div class="limited_w" :style="{width:(goods.sold_amount/goods.amount*100)+'%'}"></div>
                                </div>
                                <div class="limited_y" v-if="goods.sold_amount > 0">已售{{Math.ceil(goods.sold_amount/goods.amount*100)}}%</div>
                            </div>
                            <dd class="dd3">

                                <div class="dd3_s_s">
                                    <p class="span1">¥<span class="font_price">{{goods.price}}</span><span class="dd3_s_span"> ¥{{goods.cost_price}}</span></p>
                                </div>
                                <div class="commission_sr commission_s_s">
                                    <p  v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p_s">分佣¥{{parseFloat(goods.share_commission)}}</p>
                                </div>
                            </dd>
                        </div>
                    </a>

                </dl>
                <div class="limited_tb">
                    <a href="/h5/goods/goods-sale-list">
                        <p>查看更多抢购商品</p>
                        <img src="/images/limit_s2.png" alt="">
                    </a>
                </div>
            </div>

            <div class="clear"></div>
                <div class="Y_div4 vip_div">
                    <h4 class="biaoti">
                        <img src="/images/tuijian5.png">
                    </h4>

                            <dl v-for="goods in index_list.today_list">
                                <a :href="'/h5/goods/view?id='+goods.id">
                                    <dt style="position: relative;">
                                        <div class="limited_tr" v-if="goods.limit.is_limit == 1">
                                            <p>每人限购{{goods.limit.limit_amount}}件</p>
                                        </div>
                                        <img :src="goods.main_pic">
                                    </dt>
                                    <div class="s-list">
                                        <dd class="dd1"><span class="span1">{{goods.title}}</span></dd>
                                        <dd class="dd3 s-list-t">精品甄选 {{goods.desc}}</dd>
                                        <dd class="dd3">

                                            <div class="dd3_s">
                                                <span class="span1">¥<span class="font_price">{{goods.price}}</span></span>
                                            </div>
                                            <div class="commission commission_s">
                                                <p  v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p">分佣¥{{parseFloat(goods.share_commission)}}</p>
                                            </div>
                                            <div class="limited_jr">
                                                <p  style="min-height: .4rem"><span v-if="goods.sale_amount > 0">已售{{goods.sale_amount}}件</span></p>
                                            </div>
                                        </dd>
                                    </div>
                                </a>

                            </dl>

                </div>

                <div class="Y_div4 vip_div">
                    <h4 class="biaoti">
                        <img src="/images/tuijian6.png">
                    </h4>
                    <div class="invite">

                            <div class="invite_s" v-for="goods in index_list.best_list">
                                <a :href="'/h5/goods/view?id='+goods.id">
                                    <div class="invite_img" style="position: relative;">
                                        <div class="limited_tp" v-if="goods.limit.is_limit == 1">
                                            <p>每人限购{{goods.limit.limit_amount}}件</p>
                                        </div>
                                        <img  :src="goods.main_pic">
                                    </div>
                                    <div class="s-list_x">
                                        <dd class="dd1"><span class="span1">{{goods.title}}</span></dd>
                                        <dd class="dd3 s-list-t">{{goods.desc}}</dd>
                                        <dd class="dd3">

                                            <div class="dd3_ty">
                                                <span class="span1">¥<span class="font_price">{{goods.price}}</span></span>
                                            </div>

                                                <div class="commission">
                                  <p   v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p1">分佣¥{{goods.share_commission}}</p>

                                                </div>

                                        </dd>
                                        <div class="limited_shy">
                                            <p  style="min-height: .4rem"><span v-if="goods.sale_amount > 0">已售{{goods.sale_amount}}件</span></p>
                                        </div>
                                    </div>
                                </a>
                            </div>

                    </div>
                </div>
            <div class="Y_div4 vip_div">
                <h4 class="biaoti">
                    <img src="/images/tuijian7.png">
                </h4>
                <div class="invite">

                    <div class="invite_s" v-for="goods in index_list.like_list">
                        <a :href="'/h5/goods/view?id='+goods.id">
                            <div class="invite_img" style="position: relative;">
                                <div class="limited_tp" v-if="goods.limit.is_limit == 1">
                                    <p>每人限购{{goods.limit.limit_amount}}件</p>
                                </div>
                                <img  :src="goods.main_pic">
                            </div>
                            <div class="s-list_x">
                                <dd class="dd1"><span class="span1">{{goods.title}}</span></dd>
                                <dd class="dd3 s-list-t">{{goods.desc}}</dd>
                                <dd class="dd3">

                                    <div class="dd3_ty">
                                        <span class="span1">¥<span class="font_price">{{goods.price}}</span></span>
                                    </div>

                                    <div class="commission">
                                        <p   v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p1">分佣¥{{parseFloat(goods.share_commission)}}</p>

                                    </div>

                                </dd>
                                <div class="limited_shy">
                                    <p  style="min-height: .4rem"><span v-if="goods.sale_amount > 0">已售{{goods.sale_amount}}件</span></p>
                                </div>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
            <div class="Y_div4 vip_div" >
                <h4 class="biaoti">
                    <img src="/images/tuijian8.png">
                </h4>
                <div class="invite" >
                    <div class="invite_s" v-for="goods in best_more_list">
                        <a :href="'/h5/goods/view?id='+goods.id">
                            <div class="invite_img">
                                <img  :src="goods.main_pic">
                            </div>
                            <div class="s-list_x">
                                <dd class="dd1">{{goods.title}}</dd>
                                <dd class="dd3 s-list-t">{{goods.desc}}</dd>
                                <dd class="dd3">

                                    <div class="dd3_ty">
                                        <span class="span1">¥<span class="font_price">{{goods.price}}</span></span>
                                    </div>

                                    <div class="commission">
                                        <p   v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p1">分佣¥{{goods.share_commission}}</p>

                                    </div>

                                </dd>
                                <div class="limited_shy">
                                    <p  style="min-height: .4rem"><span v-if="goods.sale_amount > 0">已售{{goods.sale_amount}}件</span></p>
                                </div>
                            </div>

                        </a>
                    </div>
                    <div class="more_btn"   @click="tabClick2"  v-if="more_btn2">
                        <a>
                            <div class="classify-s"><h2>点击加载更多</h2></div>
                        </a>
                    </div>
                </div>
            </div>

             </div>
                </div>
                <!-- 第二个swiper -->
                <div v-for="(nav_swiper,index) in swiperList" class="swiper-slide" >
                    <div class="box1" >
                            <!--分类菜单-->

                            <!--产品列表-->

               <div v-if="goods_list.length == 0" style="text-align: center; padding-top: 20px;">
                   <img src="/images/shangpinmoren.png" alt="" style="width: 2.5rem; margin-top: 2rem;">
                   <p style="font-size: .26rem; margin-top: .2rem">没找到商品，去看看其他商品？</p>
               </div>
                            <ul class="search_result">
                                <li v-for="goods in goods_list">
                                    <a :href="'/h5/goods/view?id='+goods['id']">
                                        <div class="cp_pic">
                                            <img :src="goods['main_pic']+'_100x100'"/>
                                        </div>
                                        <div class="cp_text">
                                            <h3>{{goods['title']}}</h3>
                                            <dd class="dd3 s-list-t">{{goods['desc']}}</dd>
                                            <p><span>¥</span><span class="price"><span class="font_price">{{goods.price}}</span></span></p>
                                            <div class="limited_jr">
                                                <span v-if="goods.sale_amount > 0" style="color: #999; text-align: right; display: block; margin-top: .07rem;">已售{{goods.sale_amount}}件</span>
                                            </div>
                                            <?php
                                            $type = Yii::$app->request->get('type');
                                            if ($type == 'score') {?>
                                                <p style="text-align: right;">积分<span class="price-s">{{ goods['score'] }}</span></p>
                                            <?php } else {?>
                                                <p v-if=" goods['share_commission']!=0" style="text-align: right; float: right; margin-top: .1rem; width: 35%; padding-right: .2rem; height: .5rem; font-size: .22rem; color: #fff; line-height: .5rem;" class="sdfwe">分佣<span class="price-s" style="color: #fff; margin-top: -.05rem; font-size: .2rem;">{{ goods['share_commission'] }}</span></p>
                                            <?php }?>

                                        </div>
                                    </a>
                                </li>

                            </ul>
                        <div class="more_btn"   @click="tabClick"  v-if="more_btn">
                            <a>
                                <div class="classify-s"><h2>点击加载更多</h2></div>
                            </a>
                        </div>

                </div>
            </div>
        </div>
        </div>
        <?php echo $this->render('../layouts/_bottom_nav');?>
    </div>
    <div  class="sj-t" style="z-index: 999; display: block;margin-top: 1rem;" v-if="is_pop">
        <div class="popup">
            <img src="/images/popup_g.png" alt="" class="popup_sy" >
            <a v-for="ad_img in pop_ad_img" :href="ad_img.url" >
                <img :src="ad_img.img" alt="" class="popup_ty" style="width:90%;margin-top: .5rem;" >
            </a>
        </div>
    </div>
    <div class="sj-t_s1" style="z-index:999999999">
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




    <div class="announcement" v-if="is_open == 1 && notice_list.length > 0 " style="height:.7rem;">
        <div class="announcement_s">
<!--            <img src="/images/announcement_s1.png" alt="">-->
            <p>云淘帮公告：</p>
        </div>
        <marquee behavior="scroll" direction="left"  style="height:.7rem;">
            <a v-for="notice in notice_list" :href="notice.url">
                <table style="height:.7rem;"><tr style="margin-top: -.1rem; display: block;"><td>{{notice.title}}</td></tr></table></a>
        </marquee>
        <div class="announcement_y" @click="close">
            <img src="/images/announcement_s2.png" alt="" class="announcement_gb">
        </div>
    </div>



</div><!--box-->

<script>


    var app = new Vue({
        el: '#app',
        data: {
            navList: [
                {'name': '推荐'}
            ],
            more_btn:false,
            more_btn2:false,
            nowIndex: 0,
            cid: 0,
            pop_ad_img:'',
            is_pop:true,
            setwidth: 15,
            swiperList: [],
            goods_list: [], // 商品列表
            index_list: [],//首页全部内容列表
            banner_list: [], //轮播列表
            best_more_list: [],//更多优品
            is_open:0,//公告是否显示 0否 1显示
            notice_list:[],//公告列表
            have_new_msg: false,//是否有新消息
            sale_list:[],
            xin_pic:'',
            date:{ //倒计时相关
              d:'',
              h:'',
              m:'',
              s:'',
              end_time:'',
              status:1,
            },
            signList:[],//签到日历
            is_sign:0,//0未签到 1已签到
            total_day:0,//累计签到
            sign_reword_word:'',//签到规则
            SearchForm: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        components: {},

        methods: {

            /**
             * 加载弹窗广告
             */
            loadPopAd: function () {

                apiGet('/api/page/default/pop-ad','', function (json) {
                    if (callback(json)) {
                        if (json['pop_ad'].length < 1) {
                            app.is_pop = false;
                        } else {
                            app.pop_ad_img = json['pop_ad'];
                            sessionStorage.setItem("pop", '1');//判断是否显示弹窗
                        }
                    }
                });
            },
            /**
             * 签到
             */
            sign: function () {
                if (localStorage.getItem('token')) {
                    if (this.is_sign === 0) {
                        apiGet('<?php echo Url::to(['/api/user/sign']);?>', {}, function (json) {
                            if (callback(json)) {
                                // app.sign_score = json['score'];
                                app.is_sign = 1;
                                if (json['is_sign_reword'] == 1) {
                                    layer.msg('签到成功,获得' + json['score'] + '积分,您满勤获得奖励' + json['sign_reword_score'] + '积分!')
                                } else {
                                    layer.msg('签到成功,获得' + json['score'] + '积分')
                                }

                                app.$nextTick(function () {
                                    app.loadSignList();
                                });
                            }
                        });
                    }
                }
                else {
                    window.location.href = '/h5/user';
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
            /**
             * 加载分类列表
             */
            loadnavList: function () {

                apiGet('/api/default/category','', function (json) {
                    if (callback(json)) {
                        json['category'].forEach(function (nav) {
                            app.navList.push(nav);
                            app.swiperList.push(nav);
                        });
                        app.setwidth=app.navList.length*5;
                        app.appBanner();
                    }
                });
            },
            layerNotice:function(url){

                if(localStorage.getItem('token'))
                {
                    if(localStorage.getItem('user_status') == 2)
                    {
                        layer.msg('仅限激活会员查看');
                    }else{
                        window.location.href=url;
                    }

                }else{
                    window.location.href=url;

                }

            },
            /**
             * 检查商学院是否有新消息
             */
            CheckNewHand:function () {

                if (localStorage.getItem('token')) {
                    apiGet('/api/user/check-new-hand-message', '', function (json) {
                        if (callback(json)) {

                            app.have_new_msg = json['have_new_msg'];
                        }

                        console.log(app.have_new_msg)
                    });

                    localStorage.setItem('user_status', <?php echo Yii::$app->user->identity['status'] ?>)

                } else {
                    this.have_new_msg = true;

                }
                //获取新字动态图
                apiGet('/api/da/list', {'lid':37}, function (json) {
                    if (callback(json)) {
                      app.xin_pic=json['da_list'][0]['img'];
                    }

                });

            },
            /**
             * 加载商品列表
             */
            loadGoodsList: function (index, cid, page) {
                if (page == 1) {
                    app.SearchForm.page = 1;
                }
                app.banner_list = [];
                this.nowIndex = index;

                this.mySwiper.slideTo(index, 10, false);
                if (index == 0) {
                    this.loadIndex();
                }

                if (index != 0) {
                    apiGet('/api/default/catgoods?cid=' + cid, this.SearchForm, function (json) {
                        if (cid != app.cid || page == 1) {
                            app.goods_list = [];

                        }
                        if (cid) {
                            app.cid = cid;
                        }
                        if (callback(json)) {

                            app.title = json['title'];
                            json['goods_item'].forEach(function (goods) {
                                app.goods_list.push(goods);
                            });

                            app.banner_list = json['banner_list'];
                            app.$nextTick(function () {


                                $('#home_slider_' + index).flexslider({
                                    animation: 'slide',
                                    controlNav: true,
                                    directionNav: true,
                                    animationLoop: true,
                                    slideshow: true,
                                    slideshowSpeed: 3000,
                                    useCSS: false
                                });
                                setTimeout(function () {
                                    //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                                    var activeHight = $(".swiper-slide").eq(index).height() + 300;
                                    $(".swiper-container").height(activeHight)
                                }, 400);

                                //价格字体修改
                                $(".font_price").each(function () {
                                    var con = $(this).html().split('.');
                                    if (con.length === 2) {
                                        $(this).html('<span class="big">' + con[0] + '.</span><span class="small" style="font-size:0.2rem;">' + con[1] + '</span>');
                                    }
                                });
                            });

                            //分页
                            app.page = json['page']['pageCount'];

                            if (json['page']['pageCount'] > 1) {
                                app.more_btn=true;
                            }
                            else {
                                app.more_btn=false;
                            }
                        }

                    });
                }


            },

            /**
             * 加载首页列表
             */
            loadIndex: function () {

                apiGet('/api/page/default','', function (json) {

                    if (callback(json)) {
                        console.log(json)
                        app.index_list=json;
                        app.sale_list=json['sale_list'];
                        app.sale_list.map( (obj,index)=>{
                            app.$set(
                                obj,"countTime",
                                // obj,"countTime",InitTime(obj.over_time)
                            );
                        });
                       // app.date.end_time= app.sale_list[0]['end_time'];
                        app.$nextTick(function () {

                            setTimeout(function() {
                                //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                                var activeHight = $(".swiper-slide").eq(0).height()+120;
                                $(".swiper-container").height(activeHight)
                            }, 400);
                            $('#home_slider').flexslider({
                                animation : 'slide',
                                controlNav : true,
                                directionNav : true,
                                animationLoop : true,
                                slideshow : true,
                                slideshowSpeed: 3000,
                                useCSS : false
                            });
                          //  app.countTime();
                            //价格字体修改
                            $(".font_price").each(function () {
                                var con = $(this).html().split('.');
                                if (con.length === 2) {
                                    $(this).html('<span class="big">' + con[0] + '.</span><span class="small" style="font-size:0.3rem;position: relative;top: 2px;">' + con[1] + '</span>');
                                }
                            });

                        });


                    }

                });
            },
            /**
             * 加载更多优品列表
             */
            loadMoreList: function () {

                apiGet('/api/page/default/more-list', this.SearchForm, function (json) {
                    if (callback(json)) {
                        json['best_more_list'].forEach(function (goods) {
                            app.best_more_list.push(goods);
                        });
                        //分页
                        app.page = json['page']['pageCount'];
                        console.log(app.page)
                        if (json['page']['pageCount'] > 1) {

                            app.more_btn2=true;
                        }
                        else {
                            app.more_btn2=false;
                        }
                        app.$nextTick(function () {
                            setTimeout(function() {
                                //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                                var activeHight = $(".swiper-slide").eq(0).height()+30;
                                $(".swiper-container").height(activeHight)
                            }, 200);

                            $(".font_price").each(function () {
                                var con = $(this).html().split('.');
                                if (con.length === 2) {
                                    $(this).html('<span class="big">' + con[0] + '.</span><span class="small" style="font-size:0.2rem;position: relative;top: 2px;">' + con[1] + '</span>');
                                }
                            });
                        });


                    }

                });
            },
            appBanner: function () {

                layer.open({
                    type: 1,
                    title:false,
                    content:$('#app-banner'),
                    anim: 3,
                    shade: 0,
                    end: function () {//无论是确认还是取消，只要层被销毁了，end都会执行，不携带任何参数。layer.open关闭事件
                        $('.mall-header-index').css('top','0');　//layer.open关闭刷新
                        $('.top-head').css('top','0');
                        $('.container').css('margin-top','1rem');
                    }
                });
            },
            tabClick:function(){
                    if (app.SearchForm.page < app.page) {
                        app.SearchForm.page++;
                        app.loadGoodsList(app.nowIndex,app.cid, app.SearchForm.page);
                    } else {
                        app.more_btn=false;
                        layer.msg('没有更多数据了。');
                    }
            },
            //更多优品分页加载
            tabClick2:function(){
                if (app.SearchForm.page < app.page) {
                    app.SearchForm.page++;
                    app.loadMoreList();
                } else {
                    app.more_btn2=false;
                    layer.msg('没有更多数据了。');
                }
            },
            close:function () {
                $(".announcement").hide();
            }

        },
        mounted:function(){

            if(sessionStorage.getItem("pop"))
            {
                console.log(sessionStorage.getItem("pop"))
                this.is_pop=false;
            }else{
            this.loadPopAd();
            }
            this.loadnavList();
            var that=this;
            that.mySwiper = new Swiper('.swiper-container',{
                initialSlide:0,
                autoplay:false,
                keyboardControl:true,
                autoHeight:true,
                observer:true,
                observeParents:true,
                onSlideChangeStart:function(){
                    var index=that.mySwiper.activeIndex;
                    var cid=that.navList[index].id;

                    if(that.nowIndex>index)
                    {
                        $(".y_nav").scrollLeft($(".y_nav").scrollLeft() + $('.aa').offset().left - $(".y_nav").offset().left-185);
                    }

                    if(that.nowIndex<=index)
                    {
                        $(".y_nav").scrollLeft($(".y_nav").scrollLeft() + $('.aa').offset().left - $(".y_nav").offset().left);
                    }
                     //console.log(that.mySwiper.activeIndex)
                    that.nowIndex=index;
                   app.loadGoodsList(index,cid,1);
                }

            });
            this.loadIndex();
            this.CheckNewHand();
            this.loadMoreList();
        },
        created:function(){
            console.log(localStorage.getItem('token'))
            if(localStorage.getItem('token'))
            {
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
            }
            // 获取签到规则
            apiGet('<?php echo Url::to(['/api/default/get-system?config=sign_reword_word']);?>', {}, function (json) {
                if (callback(json)) {
                    app.sign_reword_word = json['system'].replace(/\\n/gm,"<br/>");
                    }
            });
            // 获取公告通知
            apiGet('<?php echo Url::to(['/api/page/default/notice']);?>', {}, function (json) {
                if (callback(json)) {
                    console.log(json)
                    app.notice_list = json['notice_list'];
                    app.is_open = json['is_open'];
                }
            });

            setInterval(() => {
                for (var key in app.sale_list) {
                        var end = parseInt(app.sale_list[key]['end_time'] * 1000);
                        var now = new Date().getTime();
                        var rightTime = end - now;

                        if (rightTime > 0) {
                            var d = Math.floor(rightTime / 1000 / 60 / 60 / 24);
                            var h = Math.floor((rightTime / 1000 / 60 / 60) % 24);
                            var m = Math.floor((rightTime / 1000 / 60) % 60);
                            var s = Math.floor((rightTime / 1000) % 60);
                            if (h < 10 || m < 10 || s < 10) {
                                d = d < 10 ? "0" + d : d;
                                h = h < 10 ? "0" + h : h;
                                m = m < 10 ? "0" + m : m;
                                s = s < 10 ? "0" + s : s;
                            }
                        }
                        if(d>0)
                        {
                            app.sale_list[key]['countTime'] = d+"天"+h+":"+m+":"+s;
                        }else
                        {
                            app.sale_list[key]['countTime'] = h+":"+m+":"+s;
                        }
                        if(rightTime<=0){
                            app.sale_list[key]['countTime']='已结束';
                        }


                }
            }, 1000);
        },


    });




    function AutoScroll(obj) {
    $(obj).find("ul:first").animate({
            marginTop: "10px"
        },
        500,
        function() {
            $(this).css({
                marginTop: "100px"
            }).find("li:first").appendTo(this);
        });
      }
    $(document).ready(function(){
        $(".sj-t").height($(window).height());
        // $(".user_q_d").click(function(){
        //     $(".sj-t").show();
        // });
        $(".popup_sy").click(function(){
            $(".sj-t").hide();
        });
    });
    $(document).ready(function(){
        $(".sj-t_s1").height($(window).height());
        $(".sign").click(function(){
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
