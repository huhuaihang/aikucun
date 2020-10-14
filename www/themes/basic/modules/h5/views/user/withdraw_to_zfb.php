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

$this->title = '提现到支付宝';

?>
<div class="box" id="app">
    <!--头部-->
    <div class="b_nav1 clearfix">
        <a class="b_fanhui" href="javascript:void(0);" onClick="window.location.href='<?php echo Url::to(['/h5/user/withdraw-method']);?>'"><img src="/images/b_arrow_left_03.png"></a>
        <h5>提现到支付宝</h5>
    </div>
    <!--提现到支付宝-->
    <form class="b_txalipay" @submit.prevent="submit">
        <label class="b_magt1 b_underline ">
            <span>支付宝</span>
            <input type="text" v-model="WithdrawForm.account_no" placeholder="请输入支付宝账号"/>
        </label>
        <label class="">
            <span>真实姓名</span>
            <input type="text" v-model="WithdrawForm.account_name" placeholder="支付宝认证的真实姓名"/>
        </label>
        <label class="b_magt1 ">
            <span>提现金额</span>
            <input type="text" v-model="WithdrawForm.money" placeholder="请输入提现金额"/>
            <span>支付密码</span>
            <input type="password" v-model="WithdrawForm.payment_password" placeholder="请输入支付密码"/>
            <p>可用余额<span>{{account.commission}}</span>元</p>
        </label>
        <button class="b_confrim">确定</button>
    </form>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            account: {
                commission: 0 // 佣金
            },
            WithdrawForm: {
                bank_name: '支付宝', //银行名称
                bank_address: '', // 开户行地址
                account_no: '', // 支付宝账号
                account_name: '', // 支付宝账户名
                money: 0, // 提现金额
                payment_password: '', // 支付密码
                remark: '提现到支付宝' // 备注
            }
        },
        methods: {
            getAccount: function () {
                apiGet('<?php echo Url::to(['/api/user/account']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.account = json['account'];
                    }
                });
            },
            submit: function () {
                if (this.WithdrawForm.account_no == '') {
                    layer.msg('请输入支付宝账号。', function () {});
                    return false;
                }
                if (this.WithdrawForm.money <= 0) {
                    layer.msg('请输入正确的提现金额。', function () {});
                    return false;
                }
                if (this.WithdrawForm.payment_password === '') {
                    layer.msg('请输入支付密码。', function () {});
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/withdraw'])?>', this.WithdrawForm, function (json) {
                   if (callback(json)) {
                       window.location.href = '<?php echo Url::to(['/h5/user/withdraw-list']);?>';
                   }
                });
            }
        },
        mounted: function () {
            this.getAccount();
        }
    });
</script>
