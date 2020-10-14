<?php
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\models\System;
use app\widgets\AdWidget;
use yii\helpers\Url;
use app\assets\VueAsset;
use app\assets\PhotoAsset;
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
PhotoAsset::register($this);
ApiAsset::register($this);
$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);
$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');
$this->registerCssFile('/style/swiper.min.css');
$this->registerJsFile('/js/swiper-3.2.5.min.js',['position' => $this::POS_HEAD]);
LayerAsset::register($this);
VueAsset::register($this);
$this->title = System::getConfig('site_index_title', '主页');
?>
<style>
    .y_nav li .aa{
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


</style>


<div class="box" id="app">
    <div class="new_header" style="border-bottom: 1px solid #f3f3f3;">
        <a href="<?php echo Url::to(['/h5'])?>" class="a1"><img src="/images/new_header.png"></a>
        <a href="javascript:;" class="a2">商学院</a>
    </div>




        <div class="Y_banner">
            <div class="block_home_slider">
                <div id="home_slider" class="flexslider">
                    <ul class="slides">
                        <?php AdWidget::begin(['lid' =>29]);?>
                        {foreach $ad_list as $ad}
                        <li>
                            <div class="slide">
                                <a href="#"><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}" /></a>
                            </div>
                        </li>
                        {/foreach}
                        <?php AdWidget::end();?>
                    </ul>
                    <ol class="flex-control-nav flex-control-paging"></ol>
                </div><!--home_slider-->
            </div><!--block_home_slider-->
        </div><!--Y_banner-->


        <div class="clear"></div>
    <div class="y_nav">
        <ul :style={width:setwidth+'rem'} >

            <li v-for="(nav,index) in navList" @click="loadSchoolList(index,1)" style="position: relative;">

                <a  :class="{'aa':nowIndex===index}"  href="#">{{nav.name}}</a>
                <img v-if="nav.status==1  " :src="xin_pic" alt="" style="position: absolute;top: .2rem;right: .1rem;width: .3rem;">
            </li>



        </ul>
    </div>
        <div class="swiper-container swiper_con" >
            <div class="swiper-wrapper">

                <!-- 第一个swiper 新人入门-->
                <div class="swiper-slide" ref="viewBox" >

                        <div  id="login_vi" >
                            <div   class="login_vi show_vi">

                                <div class="source_xin"  v-for="hand in hand_list">
                                    <a :href="'/h5/source/new-view?id='+hand['id']">
                                    <div class="source_xin1">
                                       <h2>{{hand.title}}</h2>
                                        <p>{{hand.create_time|timeFormat}}<span v-if="hand.read_status==1">{{hand.read_str}}</span></p>
                                    </div>
                                    <div class="source_xin2" >
                                        <img :src="hand.main_pic" alt="">
                                    </div>
                                    </a>
                                </div>
                                <ul>
                                    <li class="more_btn"  @click="tabClick" style="display: none">
                                        <a>
                                            <div class="classify-s" >
                                                <h2 >点击加载更多</h2>
                                            </div>
                                        </a></li>
                                </ul>

                            </div>
                        </div>
                </div>

                <!-- 第二个swiper 图片 -->
                <div  class="swiper-slide" v-for="nav in navList" v-if="nav.type=='img'" >

                    <div class="source_xin" v-for="source_matketing in source_matketing_list" >
                        <div class="source_xuan1">
                            <img :src="source_matketing.sduty_system.avatar" alt="">
                        </div>
                        <div class="source_xuan_2">
                            <p>{{source_matketing.sduty_system.nick_name}}<span>{{source_matketing.create_time | timeFormat}}</span></p>
                            <p>{{source_matketing.desc}}</p>
                            <div class="source_xuan4">
                                <div id="demo-test-gallery" class="demo-gallery">

                                    <a v-for="source_matketing_img in source_matketing.img_list" :href="source_matketing_img" data-size="1600x1068" :data-med="source_matketing_img" data-med-size="1024x683" data-author="Samuel Rohl"><img :src="source_matketing_img" alt=""></a>
                                </div>
                            </div>
                            <div class="source_xuan3"  @click = 'wx_rel(source_matketing.id,"source")'>
                                <img src="/images/fenxiang2.png" alt="">
                            </div>
                        </div>
                    </div>
                    <ul>
                        <li class="more_btn"  @click="tabClick" style="display: none">
                            <a>
                                <div class="classify-s" >
                                    <h2 >点击加载更多</h2>
                                </div>
                            </a></li>
                    </ul>

                </div>


                <!-- 第三个swiper 视频-->
                <div  class="swiper-slide"  v-for="nav in navList" v-if="nav.type=='video'">
                    <div class="source_xin"  v-for="video in video_list" >
                        <div class="source_xuan1">
                            <img :src="video.sduty_system.avatar" alt="">
                        </div>
                        <div class="source_xuan2">
                            <a :href="'/h5/video/view?id='+video['id']"><p>{{video.sduty_system.nick_name}}<span>{{video.create_time | timeFormat}}</span></p>
                            <p>{{video.desc}}</p></a>
                            <div class="video_s_x">
                            <video :poster='video.cover_image'  width="150" height="150"  controls preload="load">
                                <source :src="video.video" type='video/mp4'  />
                            </video>
                            </div>
<!--                            <div class="source_xuan3"  @click = 'wx_rel(video.id,"video")'>-->
<!--                                <img src="/images/fenxiang2.png" alt="">-->
<!--                            </div>-->
                        </div>
                    </div>
                    <ul>
                        <li class="more_btn"   @click="tabClick" style="display: none">
                            <a>
                                <div class="classify-s">
                                    <h2  >点击加载更多</h2>
                                </div>
                            </a></li>
                    </ul>
                </div>





            </div>
        </div>


</div><!--box-->
<!--图片组件用到的HTML-->
<div id="gallery" class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="pswp__bg"></div>

    <div class="pswp__scroll-wrap">

        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <div class="pswp__counter"></div>

                <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

                <!--                <button class="pswp__button pswp__button--share" title="Share"></button>-->

                <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

                <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="pswp__loading-indicator"><div class="pswp__loading-indicator__line"></div></div> -->

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip">
                    <!-- <a href="#" class="pswp__share--facebook"></a>
                    <a href="#" class="pswp__share--twitter"></a>
                    <a href="#" class="pswp__share--pinterest"></a>
                    <a href="#" download class="pswp__share--download"></a> -->
                </div>
            </div>

            <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
            <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
            <div class="pswp__caption">
                <div class="pswp__caption__center">
                </div>
            </div>
        </div>

    </div>


</div>
<!--结束-->

<div class="kePublic_s" style="display: none;">
    <div class="gb_resLay_s clearfix_s"    style="width: 95%">
        <div class="bdsharebuttonbox_s">
            <img src="/images/fengxiang_11.png" alt="" class="fenxiang">
        </div>
        <div class="clear"></div>
    </div>
</div>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            navList:[
            ],
            nowIndex:0,
            xin_pic:'',
            cid:0,
            setwidth:15,
            swiperList:[],
            index_list:[],
            source_matketing_list:[],//img营销素材 商品推广
            hand_list:[],//新手列表
            video_list:[],//video宣传短片 形象短片
            set_page:{},
            SearchForm: {
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        components:{
        },

        methods: {
            /**
             * 加载分类列表
             */
            loadnavList: function () {

                apiGet('/api/user/school-cat','', function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (nav) {
                            app.navList.push(nav);

                        });
                        app.setwidth=app.navList.length*5;
                        console.log(app.navList)

                        app.loadSchoolList(app.nowIndex,1);
                    }
                });
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
            loadSchoolList: function (index,page) {

                if(page==1)
                {
                    this.SearchForm.page=1;
                    this.hand_list=[];
                    this.source_matketing_list=[];
                    this.video_list=[];

                }
            //     setTimeout(function() {
            //         //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
            //         var activeHight = $(".swiper-slide").eq( index).height()+100;
            //
            //         $(".swiper-container").height(activeHight)
            //     }, 400);
            // });

                this.nowIndex = index;
                this.mySwiper.slideTo(index, 10, false);

                var type_id=this.navList[index]['cid'];
                var type=this.navList[index]['type'];
                if(type=='news')
                {

                    apiGet('/api/new-hand/new-hand-list',this.SearchForm, function (json) {

                        if (callback(json)) {
                          app.hand_list=json['list'];
                            //分页
                            app.page=json['page']['pageCount'];
                            if(json['page']['pageCount']>1)
                            {
                                $('.more_btn').show();
                            }
                            else
                            {
                                $('.more_btn').hide();
                            }
                            if(json['page']['pageCount']>1)
                            {
                                $('.more_btn').show();
                            }
                            else
                            {
                                $('.more_btn').hide();
                            }

                            app.$nextTick(function () {
                                setTimeout(function() {
                                    //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                                    var activeHight = $(".swiper-slide").eq( index).height()+100;

                                    $(".swiper-container").height(activeHight)
                                }, 400);
                            });
                        }
                    });
                }
                if(type=='img')
                {


                    setTimeout(function() {
                        //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                        var activeHight = $(".swiper-slide").eq( index).height()+100;

                        $(".swiper-container").height(activeHight)
                    }, 500);
                     var listArray = JSON.parse(localStorage.getItem('source_list_'+type_id));
                   if(listArray==null || page>1)
                   {
                    apiGet('/api/source/list?cid='+type_id, this.SearchForm, function (json) {

                        if (callback(json)) {
                            json['list'].forEach(function (source) {
                                app.source_matketing_list.push(source);
                            });

                            if(page==1) {
                                localStorage.setItem('source_list_' + type_id, JSON.stringify(json['list']));
                            }

                            localStorage.setItem('img_page_count'+type_id,json['page']['pageCount']);//储存总页数
                            //分页
                            app.page=json['page']['pageCount'];

                            if(json['page']['pageCount']>1)
                            {
                                $('.more_btn').show();
                            }
                            else
                            {
                                $('.more_btn').hide();
                            }

                        }
                    });
                  }else
                   {
                       app.source_matketing_list=listArray;

                   }

                    app.$nextTick(function () {


                        if(localStorage.getItem('img_page_count'+type_id)!=null)
                        {
                            if( localStorage.getItem('img_page_count'+type_id)>1)
                            {
                                $('.more_btn').show();
                            }
                            else
                            {
                                $('.more_btn').hide();
                            }
                        }

                    });

                }
                if(type=='video')
                {
                    setTimeout(function() {
                        //异步获取数据后再改变swiper-container的高度,用了setTimeout代替...
                        var activeHight = $(".swiper-slide").eq( index).height()+100;

                        $(".swiper-container").height(activeHight)
                    }, 500);

                    var videoArray = JSON.parse(localStorage.getItem('video_list_'+type_id));
                    if(videoArray==null || page>1)
                    {
                    apiGet('/api/video/list?cid='+type_id, this.SearchForm, function (json) {

                        if (callback(json)) {

                            json['list'].forEach(function (video) {
                                app.video_list.push(video);
                            });

                            console.log(app.video_list)
                            //分页
                            app.page=json['page']['pageCount'];

                            if(page==1)
                            {
                            localStorage.setItem('video_list_'+type_id,JSON.stringify(app.video_list));//储存视频列表
                            }
                            localStorage.setItem('video_page_count'+type_id,json['page']['pageCount']);//储存总页数
                            // localStorage.setItem('video_page'+type_id,page);//储存当前请求页

                            if(json['page']['pageCount']>1)
                            {
                                $('.more_btn').show();
                            }
                            else
                            {
                                $('.more_btn').hide();
                            }

                        }
                    });
                    }else
                    {
                        app.video_list=videoArray;
                        console.log( app.video_list)

                    }


                    app.$nextTick(function () {


                        if(localStorage.getItem('video_page_count' + type_id)!=null)
                        {
                            if (localStorage.getItem('video_page_count' + type_id) > 1) {
                                $('.more_btn').show();
                            }
                            else {
                                $('.more_btn').hide();
                            }
                        }

                    });

                }
                // this.getList();
                this.navList[index]['status']=0; //滑动显示改列表  取消新字


            },
            tabClick(){

                if (app.SearchForm.page < app.page) {
                    app.SearchForm.page++;
                    app.loadSchoolList(app.nowIndex,app.SearchForm.page);
                } else {

                    layer.msg('没有更多数据了。');
                }
            },
        },
        mounted() {

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
                    //var cid=that.navList[index].id;

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

                    app.loadSchoolList(index,1);


                }

            });
            this.loadnavList();


            // this.getList();

        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }

                return y + '-' + M + '-' + d;
            },
        },

        updated: function () {
            initPhotoSwipeFromDOM('.demo-gallery');
            wx_fx();
        },

        watch:{

        },
        created:function () {


        }


    });
    var wx_fx=function() {
        $(".source_xuan3").click(function () {
            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });

    }

    var source_read=function (id,link) {
       if(link=='source')
       {
        apiGet('/api/source/read-source', {id:id}, function (json) {
            if (callback(json)) {

            }
        });
       }
        if(link=='video')
        {

            apiGet('/api/video/read-video', {id:id}, function (json) {
                if (callback(json)) {

                }
            });
        }

    }

    var wx_rel=function (id,link) {

        source_read(id,link);//增加阅读次数

        apiGet('/api/default/weixin-mp-js-config', {url:window.location.href}, function (json) {
            if (callback(json)) {

                var wxConfig = json['wxConfig'];
                wxConfig['jsApiList'] = [
                    'onMenuShareAppMessage',
                    'onMenuShareTimeline'
                ];

                wx.config(wxConfig);
                wx.ready(function () {

                    apiGet('/api/'+link+'/detail?id='+id+'&type=wx_share', {}, function (json) {
                        if (callback(json)) {
                            var  source=json.detail;
                             if(link=='source')
                             {
                                 var imgUrl=source.img_list[0];
                                 var type='link';
                             }
                            if(link=='video')
                            {
                                var imgUrl=source.cover_image;
                                var type='video';
                            }

                            wx.onMenuShareAppMessage({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/'+link+'/view?id='+id+'&type=wx_share', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: imgUrl, // 分享图标
                                type: type, // 分享类型,music、video或link，不填默认为link
                                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                success: function () {
                                    alert('分享成功');
                                    $(".kePublic_s").hide();
                                },
                                cancel: function () {
                                    alert('分享取消');
                                },
                                fail: function (res) {
                                    alert('分享成功');
                                }
                            });

                            wx.onMenuShareTimeline({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/'+link+'/view?id='+id+'&type=wx_share', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: imgUrl, // 分享图标
                                type: type, // 分享类型,music、video或link，不填默认为link
                                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                success: function () {
                                    // 用户确认分享后执行的回调函数
                                    alert('分享成功');
                                    $(".kePublic_s").hide();
                                },
                                cancel: function () {
                                    // 用户取消分享后执行的回调函数
                                    alert('分享取消');
                                }
                            });

                        }
                    });



                });
                wx.error(function (res) {
                });
            }
        });

    }
    function page_init() {
        var token=  localStorage.getItem('token');
        window.localStorage.clear();
        localStorage.setItem('token',token);
        $('#home_slider').flexslider({
            animation : 'slide',
            controlNav : true,
            directionNav : true,
            animationLoop : true,
            slideshow : true,
            slideshowSpeed: 3000,
            useCSS : false
        });





    }

</script>
