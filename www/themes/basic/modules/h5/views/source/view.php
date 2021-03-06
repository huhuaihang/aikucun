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
<div class="box" id="app">
    <header class="mall-header" style="z-index: 1;">
        <div class="mall-header-left">
            <a   href="/h5/source"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">详情</div>
        <div class="top-head-y">
            <span class="span1" @click = 'wx_rel(<?php echo Yii::$app->request->get('id') ?>)'><img src="/images/dingdan_03.png"></span>
        </div>
    </header>
    <div class="view_x">
        <div class="view_s">
            <h2>{{source.name}}</h2>
            <p>{{source.create_time| timeFormat}}</p>
        </div>
        <div class="view_e">
            <p>{{source.desc}}</p>

            <img v-for="source_img in source.img_list"  :src="source_img" alt="">

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
            source: {
                id: 0,
                name: '',
                desc: '',
                img_list: '',
                create_time: ''
            }
        },
        methods: {
            loadSource: function (id) {
                apiGet('/api/source/detail', {id:id}, function (json) {
                    console.log(json)
                    if (callback(json)) {
                        app.source = json['detail'];
                    }
                });
            }
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
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/source/view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
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
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/source/view?id='+id,  // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
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
        $(".span1").click(function () {
            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });
    }
</script>












































