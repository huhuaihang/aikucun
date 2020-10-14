<?php

/**
 * @var $this \yii\web\View
 */
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);
$this->title = '详情';
?>

<div class="box" id="app">
    <header class="mall-header" style="z-index: 1;">
        <div class="mall-header-left">
            <a   href="/h5/source"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">详情</div>
<!--        <div class="top-head-y">-->
<!--            <span class="span1" @click = 'wx_rel(--><?php //echo Yii::$app->request->get('id') ?><!--)'><img src="/images/dingdan_03.png"></span>-->
<!--        </div>-->
    </header>
    <div class="view_x">
        <div class="view_s">
            <h2>{{news.title}}</h2>
            <p>{{news.create_time| timeFormat}}</p>
        </div>
        <div class="view_e">
            <p v-html="news.content"></p>
        </div>
    </div>
    <div class="kePublic_s" style="display: none;">
        <div class="gb_resLay_s clearfix_s" style="width: 98%">
            <div class="bdsharebuttonbox_s">
                <img src="/images/fengxiang_11.png" alt="" class="fenxiang">
            </div>
            <div class="clear"></div>
        </div>
    </div>
</div>

<script>
    var app = new Vue({
        el: '#app',
        data: {
            news: {
                id: 0,
                title: '',
                content: '',
                main_pic: '',
                create_time: ''
            }
        },
        methods: {

            loadSource: function (id) {
                apiGet('/api/new-hand/new-detail', {id:id}, function (json) {
                    console.log(json)
                    if (callback(json)) {
                        app.news = json['detail'];
                    }
                });
            },
            <?php if (empty(Yii::$app->request->get('app'))) {?>
            changeNewHand:function (id) {
                apiGet('/api/new-hand/change-new-hand', {id:id}, function (json) {

                    if (callback(json)) {
                        console.log(json)
                    }
                });
            }
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
            var id = '<?php echo Yii::$app->request->get('id')?>';
            this.loadSource(id);
            this.changeNewHand(id);
        }
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

                    apiGet('/api/source/detail?id='+id, {}, function (json) {
                        if (callback(json)) {
                            var  source=json.detail;


                            wx.onMenuShareAppMessage({
                                title: source.name, // 分享标题
                                desc: source.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/source/new_view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: source.img_list[0], // 分享图标
                                type: 'link', // 分享类型,music、video或link，不填默认为link
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
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/source/new_view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
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
    function page_init() {
        <?php if (!empty(Yii::$app->request->get('app'))) {?>
        $('.mall-header').hide();
        $('.container').css('margin-top','0');
        <?php }?>
        $(".span1").click(function () {
            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });
    }
</script>












































