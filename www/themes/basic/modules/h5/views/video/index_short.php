<?php
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\assets\UtilAsset;
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);
$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);
$this->title = '宣传短片';
?>



<div   class="box" id="app" >
    <?php echo Html::beginForm(['/h5/video/short'], 'get', ['id' => 'search_form']);?>
    <header class="mall-header mall-search" style="z-index: 1">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">
            <?php echo Html::textInput('keywords', Yii::$app->request->get('keywords'), ['placeholder' => '输入素材标题', 'id' => 'keywords']);?>
        </div>
        <div class="mall-header-right">
            <a href="javascript:void(0)" onclick="submitSearch()">搜索</a>
        </div>
    </header>
    <?php echo Html::endForm();?>
    <div    class="new" >
    <div  id="wrap_vi">
        <div id="tit_vi" >
            <div><span ><a href="/h5/video" > 带货视频</a></span></div>
            <div><span class="select_vi"><a href="/h5/video/short" > 宣传短片</a></span></div>
            <div><span ><a href="/h5/video/market" >营销课堂</a></span></div>

        </div>
        <div  ref="wrapper"   id="login_vi">
            <!--宣传短片列表-->

            <div class="login_vi show_vi " >

                <div v-if="video_short_list.length == 0" style="text-align: center; padding-top: 20px;">暂时没有内容</div>
                <div class="video_s"  v-for="video_short in video_short_list">
                    <a :href="'/h5/video/view?id='+video_short.id" >
                        <div class="video_s_z">
                            <h2>{{video_short.name}}</h2>
                            <p>{{video_short.desc}}</p>
                        </div>
                    </a>
                    <div class="video_s_x">
                        <video :poster='video_short.cover_image'   width="50%" height="50%"  controls preload="load">
                            <source :src="video_short.video" type='video/mp4'  />
                        </video>
                    </div>
                    <div class="video_s_w">
                        <p>{{video_short.create_time | timeFormat}}</p>
                        <p class="top-head-y"  @click = 'wx_rel(video_short.id)'>一键分享<img src="/images/fenxiang1.png" alt=""></p>
                    </div>
                </div>

            </div>


        </div>
        <div class="kePublic_s" style="display: none;">
            <div class="gb_resLay_s clearfix_s" style="width: 90%">
                <div class="bdsharebuttonbox_s">
                    <img src="/images/fengxiang_11.png" alt="" class="fenxiang">
                </div>
                <div class="clear"></div>
            </div>
        </div>
    </div>
</div>



</div>


<script>


    var app = new Vue({

        el: '#app',
        data: {

            video_short_list: [],//宣传短片列表
            SearchForm: {
                keyword:'<?php echo Yii::$app->request->get('keywords') ?>',
                page: 1
            }, // 搜索表单

            page: {}, // 分页
            scroll: false, // 滚动监听器
        },
        methods: {

            <?php if(Yii::$app->request->get('keywords')=='') {?>

            //获取宣传短片列表
            getshortList: function () {
                apiGet('/api/video/list?cid=3', this.SearchForm, function (json) {
                    if (callback(json)) {
                        console.log(json)
                        json['list'].forEach(function (videoinfo) {

                            app.video_short_list.push(videoinfo);
                        });

                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {

                                    if (pos.y < this.maxScrollY + 30) {

                                        if (app.SearchForm.page > json['page']['pageCount']) {

                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.getshortList();

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
            <?php }else{?>
            //获取宣传短片搜索列表
            getshortList: function () {
                apiGet('/api/video/search?cid=3', this.SearchForm, function (json) {
                    if (callback(json)) {
                        console.log(json)
                        json['list'].forEach(function (videoinfo) {

                            app.video_short_list.push(videoinfo);
                        });

                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {

                                    if (pos.y < this.maxScrollY + 30) {

                                        if (app.SearchForm.page > json['page']['pageCount']) {

                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.getshortList();

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

            <?php }?>



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

            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 115) + 'px';

            this.getshortList();


        },

        updated: function () {

            wx_fx();

        },

    });




    var wx_rel=function (id) {

        apiGet('/api/default/weixin-mp-js-config', {url:window.location.href}, function (json) {
            if (callback(json)) {

                var wxConfig = json['wxConfig'];
                wxConfig['jsApiList'] = [
                    'onMenuShareAppMessage',
                    'onMenuShareTimeline'
                ];

                wx.config(wxConfig);
                wx.ready(function () {

                    apiGet('/api/video/detail?id='+id, {}, function (json) {
                        if (callback(json)) {
                            var  source=json.detail;


                            wx.onMenuShareAppMessage({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/video/view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: source.cover_image, // 分享图标
                                type: 'video', // 分享类型,music、video或link，不填默认为link
                                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                success: function () {
                                    $(".kePublic_s").hide();
                                },
                                cancel: function () {
                                    alert("分享取消")
                                },
                                fail: function (res) {
                                }
                            });

                            wx.onMenuShareTimeline({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/video/view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: source.cover_image, // 分享图标
                                type: 'video', // 分享类型,music、video或link，不填默认为link
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
    var wx_fx=function() {
        $(".top-head-y").click(function () {

            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });
    }
</script>



























