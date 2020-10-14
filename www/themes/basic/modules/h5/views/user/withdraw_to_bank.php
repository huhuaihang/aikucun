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

$this->title = '提现到银行卡';

?>
<div class="box" id="app">
    <!--头部-->
    <div class="b_nav1 clearfix">
        <a class="b_fanhui" href="javascript:void(0);" onClick="window.location.href='<?php echo Url::to(['/h5/user/withdraw-method'])?>'"><img src="/images/b_arrow_left_03.png"></a>
        <h5>提现到银行卡</h5>
    </div>
    <form class="b_txalipay" @submit.prevent="submit">
        <div class="tx1">
            <ul>
                <li>
                    <div>姓名</div>
                    <input type="text" placeholder="请输入姓名" v-model="WithdrawForm.account_name">
                </li>
                <li>
                    <div>卡号</div>
                    <input type="text" placeholder="请输入银行卡号" v-model="WithdrawForm.account_no">
                </li>
                <li>
                    <div>确认卡号</div>
                    <input type="text" placeholder="请确认银行卡号" v-model="WithdrawForm.re_account_no">
                </li>
                <li>
                    <div>银行</div>
                    <input type="text" placeholder="请输入银行名称" v-model="WithdrawForm.bank_name">
                </li>
                <li>
                    <div>开户行地区</div>
                    <input type="text" placeholder="请输入开户行地址" v-model="WithdrawForm.bank_address">
                </li>
            </ul>
        </div>
        <div class="tx2">
            <ul>
                <li>
                    <div>提现金额</div>
                    <div>
                        <input type="text" placeholder="请输入提现金额" v-model="WithdrawForm.money">
                    </div>
                </li>
                <li>
                    <div>支付密码</div>
                    <div>
                        <input type="password" placeholder="请输入支付密码" v-model="WithdrawForm.payment_password">
                    </div>
                </li>
            </ul>
            <p class="tx-y">可用金额：<span>{{account.commission}}</span>元</p>
        </div>
        <button class="tx-q">确定</button>
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
                bank_name: '', //银行名称
                bank_address: '', //开户行地址
                account_no: '', // 银行卡号
                re_account_no: '', //确认卡号
                account_name: '', // 用户名
                money: 0, // 提现金额
                payment_password: '', // 支付密码
                remark: '提现到银行卡' // 备注
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
                    layer.msg('请输入银行卡账号。', function () {});
                    return false;
                }
                if (this.WithdrawForm.account_no !== this.WithdrawForm.re_account_no) {
                    layer.msg('两次输入的银行卡号不一致，请重新输入。', function () {});
                    return false;
                }
                if (this.WithdrawForm.money <= 0) {
                    layer.msg('请输入正确的提现金额。', function () {});
                    return false;
                }
                if (this.WithdrawForm.payment_password == '') {
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
