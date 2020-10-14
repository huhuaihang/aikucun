<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use app\models\Order;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->title = '套餐卡支付成功页面';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">购买成功</div>
    </header>
    <div style="height: 1.3rem;"></div>
    <div class="callback_re">
        <img src="/images/callback_s.png" alt="">
        <p>购买成功</p>
    </div>
    <div class="callback_we">
        <p>{{title}}</p>
        <p>¥{{money}}</p>
    </div>
    <div class="callback_qw">
        <ul>
            <li>
                <a href="/h5/user/shop">我的店铺</a>
            </li>
            <li>
                <a href="/h5">返回首页</a>
            </li>
        </ul>
    </div>
</div><!--box-->

<script>
    var money=Util.request.get('money');
    var title=Util.request.get('title');
    var app = new Vue({
        el: '#app',
        data: {
            money:money,
            title:title,
        },
        methods: {
            getInfo: function () {

                //// 获取用户信息
                //apiPost('<?php //echo Url::to(['/api/user/check-child-mobile']);?>//', {mobile:this.mobile}, function (json) {
                //
                //    if (callback(json)) {
                //        app.mobile = json['mobile'];
                //        app.real_name = json['real_name'];
                //        app.nickname = json['nickname'];
                //        $(".sj-t").show();
                //        $(".ts1").show();
                //        $(".ts2").hide();
                //    }
                //});
            },

        },
        watch: {

        },

       mounted:function () {
           //this.getInfo();
       },

    });

</script>
