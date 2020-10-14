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

$this->title = '提现进度';
?>
<div class="box" id="app">
    <div class="about_head">
        <a href="javascript:void(0)" onClick="window.history.go(-1)"><p class="p1"><img src="/images/11_1.png"></p></a>
        <p class="p2">提现进度</p>
    </div><!--about_head-->
    <div class="z_progress">
    	<div class="div1">提现详情</div>
    	<div class="div2">
    		<div class="dls">
	    		<dl>
	    			<dt class="dt1"><img src="/images/z_progress1.png"></dt>
	    			<dd>提交申请</dd>
	    		</dl>
	    		<dl class="dl2">
	    			<dt class="dt1"><img src="/images/z_progress2.png"></dt>
	    			<dd>审核中</dd>
	    		</dl>
	    		<dl>
	    			<dt class="dt1" v-if="withdraw.status == 2"><img src="/images/z_progress3.png"></dt>
	    			<dt class="dt1" v-else-if="withdraw.status == 9"><img src="/images/withdraw_fail.png"></dt>
                    <dt calss="dt1" v-else><img src="/images/z_progress32.png"></dt>
	    			<dd v-if="withdraw.status == 9">提现失败</dd>
                    <dd v-else>到账成功</dd>
	    		</dl>
	    		<div class="progress_border">
	    			<div class="blue" style="width: 50%;" v-if="withdraw.status == 1"></div>
                    <div class="blue" style="width: 100%;" v-else></div>
	    		</div><!--progress_border-->
    		</div><!--dls-->
    		<div class="div3">
    			<span class="span1">{{withdraw.create_time | timeFormat}}</span>
    			<span class="span2" v-if="withdraw.status == 1">预计{{withdraw.create_time + 24 * 3600 | timeFormat}}前</span>
                <span class="span2" v-else>{{withdraw.apply_time | timeFormat}}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
    		</div><!--div3-->
    	</div><!--div2-->
    	<div class="div4">
    		<span class="span1">资金去向</span>
    		<span class="span2">{{withdraw.bank_name}}({{withdraw.account_no | noFormat}})</span>
    	</div><!--div4-->
    	<div class="div4">
    		<span class="span1">提现金额</span>
    		<span class="span2">￥{{withdraw.money}}</span>
    	</div><!--div4-->
    </div><!--z_progress-->
</div><!--box-->
<script>
 var app = new Vue({
     el: "#app",
     data: {
         withdraw: {
             account_no: '', // 提现账号
             bank_name: '', // 银行名称
             money: 0, // 提现金额
             status: 0 // 提现状态
         } // 提现记录信息
     },
     methods: {
         getWithdrawDetail: function () {
             var withdraw_id = Util.request.get('withdraw_id');
             apiGet('<?php echo Url::to(['/api/user/withdraw-detail']);?>', {withdraw_id: withdraw_id}, function (json) {
                 if (callback(json)) {
                     app.withdraw = json['withdraw'];
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
             var h = date.getHours();
             var m = date.getMinutes();
             var s = date.getSeconds();
             if (M < 10) {
                 M = '0' + M;
             }
             if (d < 10) {
                 d = '0' + d;
             }
             if (h < 10) {
                 h = '0' + h;
             }
             if (m < 10) {
                 m = '0' + m;
             }
             if (s < 10) {
                 s = '0' + s;
             }
             return M + '-' + d + ' ' + h + ':' + m;
         },
         noFormat: function (value) {
             if (value.indexOf('@') > -1) {
                 return value.substr(0,3) + '****' + value.substr(value.indexOf('@'));
             } else if (value.length >= 16) {
                 return value.substr(0,4) + '****' + value.substr(-4);
             } else {
                 return value.substr(0,3) + '****' + value.substr(-4);
             }
         }
     },
     mounted: function () {
         this.getWithdrawDetail();
     }
 });
</script>
