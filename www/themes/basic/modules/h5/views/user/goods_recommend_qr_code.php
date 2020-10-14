<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '商品分享海报';
$this->registerJsFile('/js/html2canvas.js', ['position' => $this::POS_HEAD]);
$this->registerCssFile('/style/swiper.min.css');
$this->registerJsFile('/js/swiper-3.2.5.min.js', ['position' => $this::POS_HEAD]);
$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => $this::POS_HEAD]);


//$this->registerJsFile('/js/canvas2image.js',['position' => $this::POS_HEAD]);
//$this->registerJsFile('/js/base64.js',['position' => $this::POS_HEAD]);
header("Access-Control-Allow-Origin: *");
?>
<style>

    .swiper-container {
        width: 100%;
        height: 100%;

    }

    .swiper-slide {
        top: 1rem;
        text-align: center;
        font-size: 18px;
        margin-bottom: 50px;
        height: 50%;
        /* Center slide text vertically */
        display: -webkit-box;
        display: -ms-flexbox;
        display: -webkit-flex;
        display: flex;
        -webkit-box-pack: center;
        -ms-flex-pack: center;
        -webkit-justify-content: center;
        justify-content: center;
        -webkit-box-align: center;
        -ms-flex-align: center;
        -webkit-align-items: center;
        align-items: center;
        transition: 300ms;
        transform: scale(0.8);
    }

    .swiper-slide-active, .swiper-slide-duplicate-active {
        transform: scale(1);
    }
</style>

<div class="box" id="app">




    <div class="new_header" style="z-index: 9999999;position: relative">
        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">商品海报</a>
    </div><!--new_header-->
    <div id="shareContent" style="position: relative;">
        <div class="spbj">
            <div class="spbj_n">
                <div class="spbj_p">
                    <img :src="user.avatar" crossOrigin='anonymous'>
                    <!--                <img id="img1" :src="getImage(user.avatar,'img1')" />-->
                    <p>{{user.nickname}}</p>
                </div>
                <div class="spbj_m">
                    <img :src="goods.main_pic" crossOrigin='anonymous'>
                </div>
                <div class="spbj_y">
                    <p>产品介绍：{{goods.desc}}</p>
                    <h2>产品名称: {{goods.title}}</h2>
                </div>
                <div class="spbj_t">
                    <p>¥{{goods.price}}</p>
                    <p>市场价：<span>128.00</span></p>
                </div>
                <!--            <div class="spbj_r">-->
                <!--                <p>原价¥<span>99.00</span></p>-->
                <!--            </div>-->
            </div>
            <div class="spbj_k">
                <img :src="img" alt="">
            </div>
        </div>
    </div>
    <!-- Swiper -->
    <div class="swiper-container" style="top: -1rem">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="fxb1" style="width: 5.2rem;">

                </div>

            </div>
            <div class="swiper-slide">
                <div class="spbj1">
                    <p>商品文案</p>
                    <p>{{goods.bill}}</p>
                </div>
            </div>
            <div class="swiper-slide" v-for="goods_pic in goods.detail_pic_list">
                <div class="spbj2" style="margin-top: 1rem">
                    <img :src="goods_pic" alt="">
                </div>
            </div>


        </div>

    </div>


    <!--    <div class="spbj"   >-->
    <!--        <img v-if="url" class="new-pic" :src="url" />-->
    <!--        <div id="scaleContent" class="scale-content"></div>-->
    <!--    </div>-->
    <!--    <div class="spbj_w">-->
    <!--        <ul  >-->
    <!--            <li>-->
    <!--                <a href="#"  @click = 'wx_rel(goods.id)' >-->
    <!--                    <img src="/images/sphb5.png" alt="">-->
    <!--                    <p>微信好友</p>-->
    <!--                </a>-->
    <!--            </li>-->
    <!--            <li>-->
    <!--                <a href="#" @click = 'wx_rel(goods.id)'>-->
    <!--                    <img src="/images/sphb6.png" alt="">-->
    <!--                    <p>微信朋友圈</p>-->
    <!--                </a>-->
    <!--            </li>-->
    <!---->
    <!--            <li>-->
    <!--                <a href="#" @click = 'wx_rel(goods.id)'>-->
    <!--                    <img src="/images/sphb7.png" alt="">-->
    <!--                    <p>分享链接</p>-->
    <!--                </a>-->
    <!--            </li>-->
    <!---->
    <!--        </ul>-->
    <!--    </div>-->
</div>

<div class="kePublic_s" style="display: none;">
    <div class="gb_resLay_s clearfix_s" style="width: 95%">
        <div class="bdsharebuttonbox_s">
            <img src="/images/fengxiang_11.png" alt="" class="fenxiang">
        </div>
        <div class="clear"></div>
    </div>
</div>
<!--放大版canvas-->

<script>
    var invite_code = Util.request.get('invite_code');

    var gid = Util.request.get('id');
    var app = new Vue({
        el: '#app',
        data: {
            SearchForm: {
                invite_code: invite_code,
                id: gid,
            },
            user: [], // 用户信息
            goods: [],
            img: {},
            count: 2,
        },
        methods: {
            getUser: function () {
                // 获取用户信息
                apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.user = json['user'];
                        if (app.user.status == 2) {
                            apiGet('<?php echo Url::to(['/api/user/parent']);?>', {}, function (json) {

                                if (callback(json)) {
                                    app.user = json['user'];
                                    app.user.nickname = json['user']['nick_name'];
                                    app.$nextTick(function () {
                                        app.img = '/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/goods/view?id=' + app.goods.id + '&invite_code=' + app.user.invite_code;
                                    });
                                }

                            });

                        } else {

                            app.$nextTick(function () {

                                app.img = '/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/goods/view?id=' + app.goods.id + '&invite_code=' + app.user.invite_code;

                            });

                        }

                    }


                    console.log(app.user)

                });
            },


            getGoods: function () {
                // 获取商品信息
                apiGet('<?php echo Url::to(['/api/goods/detail']);?>', this.SearchForm, function (json) {
                    if (callback(json)) {
                        layer.load(3,{time:1500});
                        app.goods = json['goods'];
                        app.$nextTick(function () {
                            var u = navigator.userAgent;
                            if (u.indexOf('Android') > -1 || u.indexOf('Adr') > -1)//android终端
                            {
                                var swiper = new Swiper('.swiper-container', {
                                    slidesPerView: 1.5,
                                    spaceBetween: 50,
                                    centeredSlides: true,
                                    loop: true,
                                    pagination: {
                                        el: '.swiper-pagination',
                                        clickable: true,
                                    },
                                    onSlideChangeStart: function () {


                                    }
                                });
                            } else {
                                var swiper = new Swiper('.swiper-container', {
                                    slidesPerView: 1.5,
                                    spaceBetween: 0,
                                    centeredSlides: true,
                                    loop: true,
                                    pagination: {
                                        el: '.swiper-pagination',
                                        clickable: true,
                                    },
                                    onSlideChangeStart: function () {


                                    }
                                });
                            }
                            setTimeout(function(){  takeScreenshot();alert('请长按识别保存海报') }, 1000);


                        });
                    }
                    console.log(app.goods)

                });
            },


        },
        mounted: function () {

            this.getUser();
            this.getGoods();

        },
        created: function () {

        },
        updated: function () {

           // wx_fx();

            // if(app.count==1)
            // {
            //
            //     takeScreenshot();
            //     alert('请长按识别保存海报')
            //
            // }
            //  app.count--;
        },

        watch: {

            img: function () {
                this.$nextTick(function () {
                    //alert('请长按识别保存海报')


                });

            },

        },

    });


    var wx_rel = function (id) {


        apiGet('/api/default/weixin-mp-js-config', {url: window.location.href}, function (json) {
            if (callback(json)) {

                var wxConfig = json['wxConfig'];
                wxConfig['jsApiList'] = [
                    'onMenuShareAppMessage',
                    'onMenuShareTimeline'
                ];

                wx.config(wxConfig);
                wx.ready(function () {

                    apiGet('/api/goods/detail?id=' + id, {}, function (json) {
                        if (callback(json)) {
                            var goods = json.goods;
                            wx.onMenuShareAppMessage({
                                title: goods.title, // 分享标题
                                desc: goods.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/goods/view?id=' + id, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: goods.main_pic, // 分享图标
                                type: '', // 分享类型,music、video或link，不填默认为link
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
                                title: goods.title, // 分享标题
                                desc: goods.desc, // 分享描述
                                link: '<?php echo Yii::$app->params['site_host'];?>/h5/goods/view?id=' + id, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                imgUrl: goods.main_pic, // 分享图标
                                type: '', // 分享类型,music、video或link，不填默认为link
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

</script>

<script>

    function takeScreenshot() {

        const w = $('.spbj').width(),
            h = $('.spbj').height();

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

        html2canvas($(".spbj"), {
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
                newImg.src = dataUrl;


                $('.fxb1').append(newImg);
                $('#shareContent').hide();
            }
        });


    }

    var wx_fx = function () {
        $(".spbj_w").click(function () {

            $(".kePublic_s").show();
        });
        $(".gb_resLay_s").click(function () {
            $(".kePublic_s").hide();
        });
    }

</script>
