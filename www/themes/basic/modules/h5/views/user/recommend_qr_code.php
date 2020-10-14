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
$this->registerJsFile('/js/html2canvas.js',['position' => $this::POS_HEAD]);
$this->registerCssFile('/style/swiper.min.css');
$this->registerJsFile('/js/swiper-3.2.5.min.js',['position' => $this::POS_HEAD]);
$this->title = '我的邀请函';

?>
<style>

    .swiper-container {
        width: 100%;
        min-height: 550px;

    }
    .swiper-slide {
        top:1rem;
        text-align: center;
        font-size: 18px;

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
    .swiper-slide-active,.swiper-slide-duplicate-active{
        transform: scale(1);
    }
</style>

<div class="box" id="app">
    <div class="new_header"  >
        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">我的邀请函</a>
    </div><!--new_header-->

<!-- Swiper -->
<div class="swiper-container" >
    <div class="swiper-wrapper">
        <div class="swiper-slide">
            <div class="fxb1" >

            </div>

        </div>
        <div class="swiper-slide" >

            <div class="fxb2"  >

            </div>
        </div>
        <div class="swiper-slide" >
            <div class="fxb3"  >

            </div>
        </div>
        <div class="swiper-slide">
            <div class="fxb4" >

            </div>
        </div>

    </div>

</div>

    <div class="fxa1"    style="width: 6.2rem;position: absolute;left:0.8rem;top:100px;">
        <!--        <img class="fxabj" src="/images/fenxiangbj.png" alt="">-->
        <div class="fxnr">
            <img class="fxuser" :src="user.avatar" >
            <p class="fxatext">{{ user.nickname }}</p>
            <img class="fxewm" :src="'/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/register?invite_code=' + invite_code" alt="">
            <p class="fxaxz">邀请码：{{ user.invite_code }} </p>
            <p>微信长按识别二维码即可注册</p>
        </div>
    </div>
    <div class="fxa2" style="width:6.2rem;position: absolute;left:0.8rem;top:100px;">
        <!--        <img class="fxabj" src="/images/fenxiangbj.png" alt="">-->
        <div class="fxnr">
            <img class="fxuser" :src="user.avatar"  >
            <p class="fxatext">{{ user.nickname }}</p>
            <img class="fxewm" :src="'/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/register?invite_code=' + invite_code" alt="">
            <p class="fxaxz">邀请码：{{ user.invite_code }} </p>
            <p>微信长按识别二维码即可注册</p>
        </div>
    </div>
    <div class="fxa3"   style="width: 6.2rem;position: absolute;left:0.8rem;top:100px;">
        <!--        <img class="fxabj" src="/images/fenxiangbj.png" alt="">-->
        <div class="fxnr">
            <img class="fxuser" :src="user.avatar" alt="">
            <p class="fxatext">{{ user.nickname }}</p>
            <img class="fxewm" :src="'/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/register?invite_code=' + invite_code" alt="">
            <p class="fxaxz">邀请码：{{ user.invite_code }} </p>
            <p>微信长按识别二维码即可注册</p>
        </div>
    </div>
    <div class="fxa4" style="width: 6.2rem;position: absolute;left:0.8rem; top:100px;">
        <!--        <img class="fxabj" src="/images/fenxiangbj.png" alt="">-->
        <div class="fxnr">
            <img class="fxuser" :src="user.avatar" alt="">
            <p class="fxatext">{{ user.nickname }}</p>
            <img class="fxewm" :src="'/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/register?invite_code=' + invite_code" alt="">
            <p class="fxaxz">邀请码：{{ user.invite_code }} </p>
            <p>微信长按识别二维码即可注册</p>
        </div>
    </div>
</div>


<!--    <div class="code">-->
<!--        <img :src="'/site/qr?content=--><?php //echo Yii::$app->params['site_host'];?><!--/h5/register?invite_code=' + invite_code">-->
<!--        <p>扫描二维码 加入云淘帮</p>-->
<!--        <p>长按可发送或者保存二维码</p>-->
<!--    </div>-->


<script>

    var app = new Vue({
        el: '#app',
        data: {
            invite_code: '',
            user: {}, // 用户信息
            bb:true,
        },
        mounted: function() {
            var invite_code = "<?php echo Yii::$app->request->get('invite_code');?>";
            this.invite_code = invite_code;

        },
        created: function () {

            // 获取用户信息
            apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                if (callback(json)) {
                    layer.load(3,{time:1500});
                    app.user = json['user'];
                    app.$nextTick(function () {
                        var u = navigator.userAgent;
                       if(u.indexOf('Android') > -1 || u.indexOf('Adr') > -1)//android终端
                       {
                        var swiper = new Swiper('.swiper-container', {
                            slidesPerView: 1.3,
                            spaceBetween: 50,
                            centeredSlides: true,
                            loop: true,
                            pagination: {
                                el: '.swiper-pagination',
                                clickable: true,
                            },
                            onSlideChangeStart:function(){


                            }
                        });
                       }else{
                           var swiper = new Swiper('.swiper-container', {
                               slidesPerView: 1.3,
                               spaceBetween: 0,
                               centeredSlides: true,
                               loop: true,
                               pagination: {
                                   el: '.swiper-pagination',
                                   clickable: true,
                               },
                               onSlideChangeStart:function(){


                               }
                           });
                       }

                       takeScreenshot(1);
                       takeScreenshot(2);
                        takeScreenshot(3);
                       takeScreenshot(4);


                    });


                }
            });
        },
        updated:function () {


        }

    });

    // $(document).ready(function(){
    //     $(".fxa1").height($(window).height()-$(".new_header").height());
    // });

    function takeScreenshot(id) {

        const w = $('.fxa2').width();
              h = $('.fxa2').outerHeight();


        //要将 canvas 的宽高设置成容器宽高的 2 倍
        var canvas = document.createElement("canvas");
        canvas.width = w * 2;
        canvas.height = h * 2;
        canvas.style.width = w + "px";
        canvas.style.height = h + "px";
        var context = canvas.getContext("2d");
        //然后将画布缩放，将图像放大两倍画到画布上
        context.scale(2,2);

        //延时300ms,等待放大版html加载图片完毕

        html2canvas($('.fxa'+id), {
            allowTaint: false,
            taintTest: false,
            useCORS:true,
            dpi: 300,
            width: w,

            // window.devicePixelRatio是设备像素比

            onrendered: function(canvas) {
                const dataUrl = canvas.toDataURL("image/png", 1.0),
                    newImg = document.createElement("img");
                newImg.width = w;
                newImg.height = h;
                newImg.src = dataUrl;

                // $('.nn').empty();
                $('.fxb'+id).append(newImg);
                $('.fxa'+id).hide();



            }
        });


    }

</script>
