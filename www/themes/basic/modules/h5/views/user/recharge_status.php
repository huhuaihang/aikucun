<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */
ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);
$this->title = '支付成功';
?>
<div class="box" id="app">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.history.go(-1);"" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">支付成功</a>
        <a href="#" class="a3">完成</a>
    </div><!--new_header-->
    <div class="payment">
        <div class="payment-sh" v-if="recharge.status == 2">
            <img src="/images/wancheng.png">
            <h2 class="sediao">支付成功</h2>
            <p>￥{{ recharge.money }}</p>
        </div>
        <div class="payment-sh" v-else-if="recharge.status == 1">
             <img src="/images/dai.png">
            <h2 class="sediao">待支付</h2>
            <p>￥{{ recharge.money }}</p>
        </div>
        <div class="payment-sh" v-else>
             <img src="/images/shibai.png">
            <h2 class="sediao">支付失败</h2>
            <p>￥{{ recharge.money }}</p>
        </div>
        <div class="payment-x">
            <p>支付方式<span>支付宝</span></p>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            recharge: {
                money: 0, // 金额
                status: '', // 状态
                create_time: '' // 创建时间
            }
        },
        methods: {
            loadRecharge: function (id) {
                apiGet('/api/user/recharge-detail', {id:id}, function(json) {
                    if (callback(json)) {
                        app.recharge = json['detail'];
                    }
                });
            }
        },
        mounted: function() {
            var id = '<?php echo Yii::$app->request->get('id')?>';
            this.loadRecharge(id);
        }
    });
</script>
