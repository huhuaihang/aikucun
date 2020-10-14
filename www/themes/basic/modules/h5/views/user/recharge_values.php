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

$this->title = '激活账号';
?>
<script src="/js/jquery.js" type="text/javascript"></script>
<div class="box" id="app">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user/my-agent']);?>';" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">我要进货</a>
        <a href="<?php echo Url::to(['/h5/user/recharge-list']);?>" class="a3">进货记录</a>
    </div><!--new_header-->
    <div class="new_active">
        <div class="div1" @click="choose($event)" v-for="value in recharge_values"><span>￥</span><span class="span2">{{ value }}</span></div>
    </div>
    <div class="new_active_button">
        <a @click="active()">确认激活</a>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            recharge_values: [] // 充值金额列表
        },
        methods: {
            getRechargeValues: function () {
                apiGet('/api/user/recharge-values', {}, function (json) {
                    if (callback(json)) {
                        json['recharge_list'].forEach(function (recharge) {
                            app.recharge_values.push(recharge);
                        });
                    }
                });
            },
            choose: function (event) {
                $(event.currentTarget).addClass("bg").siblings().removeClass("bg");
            },
            active: function () {
                var money = $(".bg .span2").html();
                if (money === undefined || money === '' || money === null) {
                    layer.msg('请选择进货金额。');
                } else {
                    window.location.href = "<?php echo Url::to(['/h5/user/recharge-method']);?>?money="+money;
                }
            }
        },
        mounted: function () {
            this.getRechargeValues();
        }
    });
</script>
