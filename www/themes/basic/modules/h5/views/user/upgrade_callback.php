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

$this->title = '升级卡支付成功页面';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">购买成功</div>
    </header>
    <div style="height: 1.3rem;"></div>
    <div class="upgrade_er">
        <img src="/images/upgrade1.png" alt="">
        <h2>恭喜您已经成长为{{level_name}}</h2>
        <p>您已成功升级为{{level_name}}，{{count}}个礼包已到账请我的店铺中查看</p>
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
    var count=Util.request.get('count');
    var level_name=Util.request.get('level_name');
    var app = new Vue({
        el: '#app',
        data: {
            count:count,
            level_name:level_name,


        },
        methods: {

        },




    });

</script>
