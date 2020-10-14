<?php
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\assets\PhotoAsset;
use app\widgets\AdWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/**
 * @var $this \yii\web\View
 * @var $history_list \app\models\UserSearchHistory[]
 */
PhotoAsset::register($this);
ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);

?>
<?php //if (empty(Yii::$app->request->get('app'))) {?>
<!--<div class="box">-->
<!--    <header class="mall-header">-->
<!--        <div class="mall-header-left">-->
<!--            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>-->
<!--        </div>-->
<!--        <div class="mall-header-title">问卷调查</div>-->
<!--    </header>-->
<!--    <div class="container">-->
<!--        <p class="b_sys_mcont">暂无内容 ~ </p>-->
<!--        <div class="b_sys_maintain1">-->
<!--            <img src="/images/questionaire_03.png"/>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<?php //} else {?>
<!--    <div class="container">-->
<!--        <p class="b_sys_mcont">暂无内容 ~ </p>-->
<!--        <div class="b_sys_maintain1">-->
<!--            <img src="/images/questionaire_03.png"/>-->
<!--        </div>-->
<!--    </div>-->
<?php //}?>

<head>
    <meta name="viewport" content="width = device-width, initial-scale = 1.0">

</head>
<div class="box" id="app">
    <?php echo Html::beginForm(['/h5/source/search'], 'get', ['id' => 'search_form']);?>
    <header class="mall-header mall-search" style="z-index: 1">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">
            <?php echo Html::textInput('keywords', Yii::$app->request->get('keywords'), ['placeholder' => '输入素材标题', 'id' => 'keywords']);?>
        </div>
        <div class="mall-header-right">
            <a href="javascript:void(0)" onclick="submitSearch()">搜索</a>
        </div>
    </header>
    <?php echo Html::endForm();?>
    <div ref="wrapper" id="wrap_vi">
        <div id="tit_vi">
            <div><span class="select_vi">商品推广</span></div>
            <div><span>营销素材</span></div>
        </div>
        <div id="login_vi">
            <div class="login_vi show_vi">
                <div v-if="source_goods_list.length == 0" style="text-align: center; padding-top: 20px;">暂时没有您要搜索的素材内容</div>

                <div class="video_s" v-for="source_goods in source_goods_list">
                    <div class="video_s_z">
                        <h2>{{source_goods.name}}</h2>
                        <p>{{source_goods.desc}}</p>
                    </div>
                    <div class="video_s_x">
                        <div id="demo-test-gallery" class="demo-gallery">

                            <a v-for="source_goods_img in source_goods.img_list" :href="source_goods_img" data-size="1600x1068" :data-med="source_goods_img" data-med-size="1024x683" data-author="Samuel Rohl"><img :src="source_goods_img" alt=""></a>


                        </div>
                    </div>
                    <div class="video_s_w"  >
                        <p>{{source_goods.create_time | timeFormat}}</p>
                        <p class="top-head_s" @click ='wx_rel(source_goods.id)'>一键分享<img src="/images/fenxiang1.png" alt=""></p>
                    </div>
                </div>
            </div>
            <div class="login_vi">
                <div v-if="source_matketing_list.length == 0" style="text-align: center; padding-top: 20px;">暂时没有您要搜索的素材内容</div>

                <div class="video_s" v-for="source_matketing in source_matketing_list">
                    <div class="video_s_z">
                        <h2>{{source_matketing.name}}</h2>
                        <p>{{source_matketing.desc}}</p>
                    </div>
                    <div class="video_s_x">
                        <div id="demo-test-gallery" class="demo-gallery">

                            <a v-for="source_matketing_img in source_matketing.img_list" :href="source_matketing_img" data-size="1600x1068" :data-med="source_matketing_img" data-med-size="1024x683" data-author="Samuel Rohl"><img :src="source_matketing_img" alt=""></a>
                        </div>

                    </div>
                    <div class="video_s_w">
                        <p>{{source_matketing.create_time | timeFormat}}</p>
                        <p class="top-head_s"  @click = 'wx_rel(source_matketing.id)' >一键分享<img src="/images/fenxiang1.png" alt=""></p>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div class="kePublic_s" style="display: none;">
        <div class="gb_resLay_s clearfix_s" style="width: 95%">
            <div class="bdsharebuttonbox_s">
                <img src="/images/fengxiang_11.png" alt="" class="fenxiang">
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>


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

                <button class="pswp__button pswp__button--share" title="Share"></button>

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
<script>

    var app = new Vue({

        el: '#app',
        data: {
            source_goods_list: [],//商品推广素材列表
            source_matketing_list: [],//营销素材列表
            SearchForm: {
                keyword:'<?php echo Yii::$app->request->get('keywords') ?>',
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: true // 滚动监听器
        },
        methods: {
            //获取默认带货视频列表
            loadsourceGoodsList: function () {
                apiGet('/api/source/search', this.SearchForm, function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (sourceinfo) {
                            app.source_goods_list.push(sourceinfo);
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.SearchForm.page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.loadBuyList();

                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },

            //获取营销素材图片列表
            getmatketingList: function () {
                apiGet('/api/source/search?cid=2', this.SearchForm, function (json) {
                    if (callback(json)) {

                        json['list'].forEach(function (sourceinfo) {

                            app.source_matketing_list.push(sourceinfo);
                        });
                        console.log(app.source_matketing_list);
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.SearchForm.page >= json['page']['pageCount']) {

                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.getuserMessageList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },


        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();
                // var h = date.getHours();
                //  var m = date.getMinutes();
                //  var s = date.getSeconds();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }
                // if (h < 10) {
                //     h = '0' + h;
                // }
                // if (m < 10) {
                //     m = '0' + m;
                // }
                // if (s < 10) {
                //     s = '0' + s;
                // }
                // return y + '-' + M + '-' + d + ' ' + h + ':' + m + ':' + s;
                return y + '-' + M + '-' + d;
            },

        },
        mounted: function () {


            //this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 95) + 'px';
            this.loadsourceGoodsList();
            this.getmatketingList();


        },



        updated: function () {
            initPhotoSwipeFromDOM('.demo-gallery');
            wx_fx();

        },
    });



    var wx_rel=function (id) {

        apiGet('/api/default/weixin-mp-js-config', {url:window.location.href}, function (json) {
            if (callback(json)) {

                console.log(json)
                var wxConfig = json['wxConfig'];
                wxConfig['jsApiList'] = [
                    'onMenuShareAppMessage',
                    'onMenuShareTimeline'
                ];

                wx.config(wxConfig);
                wx.ready(function () {

                    apiGet('/api/source/detail?id='+id, {}, function (json) {
                        if (callback(json)) {
                            var  source=json.detail;


                            wx.onMenuShareAppMessage({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: 'http://yuntaobang.ysjjmall.com/h5/source/view', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: source.img_list[0], // 分享图标
                                type: 'link', // 分享类型,music、video或link，不填默认为link
                                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                success: function () {
                                    $(".kePublic_s").hide();
                                },
                                cancel: function () {
                                    alert('分享取消');
                                },
                                fail: function (res) {
                                }
                            });

                            wx.onMenuShareTimeline({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: 'http://yuntaobang.ysjjmall.com/h5/source/view', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: source.img_list[0], // 分享图标
                                type: 'link', // 分享类型,music、video或link，不填默认为link
                                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                success: function () {
                                    // 用户确认分享后执行的回调函数
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


    function submitSearch() {
        if ($('#keywords').val() !== '') {
            $('#search_form').submit();
        } else {
            var keyword = '<?php echo empty($history_list) ? '' : $history_list[0]->keyword;?>';
            if (keyword !== '') {
                $('#keywords').val(keyword);
                $('#search_form').submit();
            }
        }
    }
    /**
     * 清空搜索历史
     */
    function clear_history() {
        $.getJSON('<?php echo Url::to(['/h5/default/delete-history'])?>', function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
    $('#tit_vi div').click(function() {
        var i = $(this).index();//下标第一种写法
        $(this).find('span').addClass('select_vi');
        $(this).siblings('div').find('span').removeClass('select_vi');
        $('.login_vi').eq(i).show().siblings().hide();
    });



    var wx_fx=function() {
        $(".top-head_s").click(function () {
            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });

    }
</script>




