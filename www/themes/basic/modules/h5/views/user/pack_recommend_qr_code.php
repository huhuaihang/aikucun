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

$this->title = '会员礼包分享海报';
$this->registerJsFile('/js/html2canvas.js',['position' => $this::POS_HEAD]);
?>

<div class="box" id="app">
    <div class="new_header" >
        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">会员礼包分享海报</a>
    </div><!--new_header-->
    <div class="recommend_qr">
        <div class="recommend_qr_s">
            <img src="/images/xinshou_tou.png" alt="">
            <p>HI 我是{{user.nickname}}，邀您一起到云淘帮赚钱</p>
        </div>
        <div class="recommend_qr_y">
            <img :src="'/site/qr?content=<?php echo Yii::$app->params['site_host'];?>/h5/register?invite_code=' + user.invite_code" alt="">
        </div>
    </div>


<!--    <div class="code">-->
<!--        <img :src="'/site/qr?content=--><?php //echo Yii::$app->params['site_host'];?><!--/h5/register?invite_code=' + invite_code">-->
<!--        <p>扫描二维码 加入云淘帮</p>-->
<!--        <p>长按可发送或者保存二维码</p>-->
<!--    </div>-->
</div>

<script>

    var app = new Vue({
        el: '#app',
        data: {
            invite_code: '',
            user: {}, // 用户信息
            bb:true,
        },
        mounted: function() {
           // var invite_code = "<?php echo Yii::$app->request->get('invite_code');?>";
           // this.invite_code = invite_code;
        },
        created: function () {
            // 获取用户信息
            apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                if (callback(json)) {
                    app.user = json['user'];
                }
            });
        },
        updated:function () {



        }

    });
    // $(document).ready(function(){
    //     $(".fxa1").height($(window).height()-$(".new_header").height());
    // });


</script>
