<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\widgets\AdWidget;
use app\assets\VueAsset;
use yii\helpers\Url;
use app\assets\MintUIAsset;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);
MintUIAsset::register($this);
$this->registerJsFile('/js/html2canvas.js',['position' => $this::POS_HEAD]);
$this->registerJsFile('/js/jquery.qrcode.min.js');
$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');
$this->title = '限时抢购列表';
?>
<div class="box1" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo  Url::to(['/h5']) ; ?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">{{title}}</div>
    </header>

    <div  class="container" v-if="goods_list.length>0" style="overflow: visible">
        <!--分类菜单-->
        <div class="Y_banner" >
            <div class="block_home_slider">
                <div id="home_slider" class="flexslider">
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
        <!--产品列表-->
    </div>
    <div   v-infinite-scroll="loadMore"
           infinite-scroll-disabled="loading"
           infinite-scroll-distance="110"
           infinite-scroll-immediate-check="false">
        <div   class="Y_div4 vip_div" >
            <dl  v-for="goods in goods_list">
                <a :href="'/h5/goods/view?id='+goods.id">
                    <dt style="position: relative;">
                        <div class="limited_ly" v-if="goods.amount-goods.sold_amount<=0" style="background:#c2c2c2">
                            <p >已抢光</p>
                        </div>
                        <div  class="limited_ly" v-else>
                            <p v-if="date.status == 1">{{goods.countTime}}</p>
                            <p v-if="date.status == 0">已结束</p>
                        </div>
                        <img :src="goods.main_pic">
                    </dt>
                    <div class="s-list">
                        <dd class="dd1"><span class="span1">{{goods.title}}</span></dd>
                        <!--                            <dd class="dd3 s-list-t">精品甄选 {{goods.desc}}</dd>-->
                        <div class="limited">
                            <div class="limited_s">
                                <div class="limited_w" :style="{width:(goods.sold_amount/goods.amount*100)+'%'}"></div>
                            </div>
                            <div class="limited_y" v-if="goods.sold_amount > 0">已售{{Math.ceil(goods.sold_amount/goods.amount*100)}}%</div>
                        </div>
                        <dd class="dd3">

                            <div class="dd3_s_s">
                                <p class="span1">¥{{goods.price}}<span class="dd3_s_span">原价：¥{{goods.cost_price}}</span></p>
                            </div>
                            <div class="commission_sr commission_s_s">
                                <p  v-if="goods.is_pack!=1 && goods.share_commission!=0" class="commission_p_s">分佣¥{{parseFloat(goods.share_commission)}}</p>
                            </div>

                        </dd>
                    </div>
                </a>

            </dl>

        </div>
        <div class="loading-box tc" style="margin-left: 45%" v-if="isLoading">
            <mt-spinner type="snake" class="loading-more"></mt-spinner>
            <span class="loading-more-txt">加载中...</span>
        </div>
    </div>
    <div class="exchange_l" style="height: 100%;display: none">
        <div class="exchange_b">
            <ul style="min-height:8rem;">
                <li v-for="goods in poster_goods_list">
                    <div class="exchange_m">
                        <img :src="goods.main_pic" alt="" height="150">
                        <p>{{goods.title | ellipsis}}</p>
                        <p>{{goods.desc |ellipsis}}</p>
                        <p>¥{{goods.price}}<span> 原价：{{goods.cost_price}}</span></p>

                    </div>
                </li>

            </ul>
        </div>
        <div class="exchange_yw">
            <div class="exchange_yv">
                <img src="/images/exchange4.png" alt="">
            </div>

            <div class="exchange_yk" >
                <div id="code" style="width:1.3rem;height:1.3rem;border: 3px solid #fff;overflow: hidden">

                </div>
<!--                <img :src="'/site/qr?content='+code_url" alt="">-->
<!--                <img src="/images/b_qr_code_03.jpg" alt="">-->
            </div>
        </div>
        <div class="exchange_fd">
            <p>活动截止时间</p>
            <p>{{end_time|timeFormat}}</p>
        </div>
    </div>
    <div style="height: 1rem;"></div>
    <div class="flash_dy">
        <a  @click="loadGoodsPoster(2)">点击分享限时抢购活动</a>
    </div>
<!--    </div>-->
<!---->
<!--    <div style="margin-top: 2rem;text-align: center" v-else>暂时没有限时活动商品...</div>-->

</div>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            title: '限时抢购',
            goods_list: [], // 商品列表
            poster_goods_list: [], // 海报商品列表
            code_url:'',//海报二维码链接
            banner_list: [], // 轮播列表
            poster:true,
            SearchForm: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            isLoading : false,
            loading:false,
            pageCount:0,// 滚动
            end_time:0,
            date:{ //倒计时相关
                d:'',
                h:'',
                m:'',
                s:'',
                end_time:'',
                status:1,
            },
        },
        methods: {
            /**
             * 加载限时抢购列表
             */
            loadGoodsList: function () {

                apiGet('/api/goods/discount-goods-list', this.SearchForm, function (json) {
                    if (callback(json)) {
                        app.banner_list = json['banner_list'];
                        json['goods_list'].forEach(function (goods) {
                            app.goods_list.push(goods);
                        });
                        app.goods_list.map( (obj,index)=>{
                            app.$set(
                                obj,"countTime",
                                // obj,"countTime",InitTime(obj.over_time)
                            );
                        });
                        app.pageCount=json['page']['pageCount'];
                        //app.date.end_time= app.goods_list[0]['end_time'];
                        console.log(app.goods_list)
                        app.$nextTick(function () {

                            //app.countTime();//倒计时

                            $('#home_slider').flexslider({//轮播
                                animation : 'slide',
                                controlNav : true,
                                directionNav : true,
                                animationLoop : true,
                                slideshow : true,
                                slideshowSpeed: 3000,
                                useCSS : false
                            });
                        });
                    }
                });
            },

            //加载更多优品
            loadMore: function () {
                this.loading = true;
                app.isLoading = true;
                setTimeout(() => {
                    if (app.SearchForm.page >= app.pageCount) {
                        this.loading = true;
                        layer.msg('没有更多数据了。');
                    } else {
                        app.SearchForm.page++;
                        app.loadGoodsList();
                        this.loading = false;
                    }
                    app.isLoading = false;
                }, 1000);
            },

            /**
             * 加载限时抢购海报商品列表
             */
            loadGoodsPoster : function (status) {
                if(status === 1) {
                    apiGet('/api/goods/discount-goods-poster', this.SearchForm, function (json) {
                        if (callback(json)) {
                            app.end_time = json['end_time'];
                            app.code_url = json['code_url'];
                            app.poster_goods_list = json['goods_list'];
                            console.log(app.poster_goods_list);
                            app.$nextTick(function () {

                                const w = $('#code').width(),
                                    h = $('#code').height();

                                make_qrcode(app.code_url,w,h);
                                // console.log(status)
                                // if(status)
                                // {
                                //     alert(1111)
                                //  app.poster=false;
                                // }


                            });
                        }
                    });
                }
             if(status === 2)
             {
                 takeScreenshot();
             }
            },


            // //倒计时
            // countTime: function() {
            //     //获取当前时间
            //     var date = new Date();
            //     var now = date.getTime();
            //     //设置截止时间
            //     //var endDate = new Date('2019-10-22 23:23:23');
            //     var timer = null;
            //     var end = this.date.end_time*1000;
            //     //时间差
            //     var leftTime = end - now;
            //     //console.log(leftTime)
            //     //定义变量 d,h,m,s保存倒计时的时间
            //     if (leftTime >= 0) {
            //         this.date.d = Math.floor(leftTime / 1000 / 60 / 60 / 24);//天数我没用到，暂且写上
            //         this.date.h = Math.floor((leftTime / 1000 / 60 / 60) % 24);
            //         this.date.m = Math.floor((leftTime / 1000 / 60) % 60);
            //         this.date.s = Math.floor((leftTime / 1000) % 60);
            //     }else
            //     {
            //         this.date.status=0;
            //         clearTimeout(timer);
            //     }
            //     // console.log(this.s);
            //     //递归每秒调用countTime方法，显示动态时间效果
            //     timer=setTimeout(this.countTime, 1000);
            // },

        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();
                var h = date.getHours();
                var m = date.getMinutes();
                var s = date.getSeconds();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }
                if (h < 10) {
                    h = '0' + h;
                }
                if (m < 10) {
                    m = '0' + m;
                }
                if (s < 10) {
                    s = '0' + s;
                }
                return y + '-' + M + '-' + d + ' ' + h +':'+ m +':'+s;
            },
            ellipsis (value) {
                if (!value) return ''
                if (value.length > 8) {
                    return value.slice(0,8) + '...'
                }
                return value
            }
        },
        mounted: function () {

            //this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 95) + 'px';
            this.loadGoodsList();
            this.loadGoodsPoster(1);
        },
        created:function(){

            setInterval(() => {
                for (var key in app.goods_list) {
                    var end = parseInt(app.goods_list[key]['end_time'] * 1000);
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
                        app.goods_list[key]['countTime'] = d+"天"+h+":"+m+":"+s;
                    }else
                    {
                        app.goods_list[key]['countTime'] = h+":"+m+":"+s;
                    }
                    if(rightTime<=0){
                        app.goods_list[key]['countTime']='已结束';
                    }


                }
            }, 1000);
        },

    });
    //生成二维码
    function make_qrcode(url,w,h) {
        console.log(w,h)
        $("#code").qrcode({
            width: w, //宽度
            height:h, //高度
            text: url //任意内容
        });
    }

    function takeScreenshot() {
        layer.load(3,{time:1500});
        $('.exchange_l').show();
        const w = $('.exchange_l').width(),
            h = $('.exchange_l').height();

        //要将 canvas 的宽高设置成容器宽高的 2 倍
        var canvas = document.createElement("canvas");
        canvas.width = w * 2;
        canvas.height = h * 2;
        canvas.style.width = w + "px";
        canvas.style.height = h + "px";
        var context = canvas.getContext("2d");
        //然后将画布缩放，将图像放大两倍画到画布上
        context.scale(2, 2);
        //延时300ms,等待放大版html加载图片完毕
        html2canvas($(".exchange_l"), {
            allowTaint: false,
            taintTest: false,
            useCORS: true,
            dpi: 300,
            width: w,
            height: h,
            // window.devicePixelRatio是设备像素比

            onrendered: function (canvas) {
          const dataUrl = canvas.toDataURL("image/png", 1.0),
                newImg = document.createElement("img");
                newImg.width = w;
                newImg.height = h;
               // newImg.src = dataUrl;


               // alert(newImg);
                openPoster(dataUrl,w*0.9,h*0.9);
               // $('.fxb1').append(newImg);
                $('.exchange_l').hide();
               // $('#shareContent').hide();
            }
        });


    }

    /**
     * 弹出海报
     * */

    function  openPoster(dataUrl,w,h) {
        console.log(dataUrl)
        var img='<img src='+dataUrl+' height='+h+' width='+w+' />';
        //页面层
        layer.open({
            type: 1,
            title:false,
            content:img,
            anim: 'up',
            shadeClose: true,
        });
    }
    function page_init() {

    }
</script>