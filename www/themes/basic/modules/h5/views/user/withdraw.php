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

$this->title = '提现';

?>
<div class="box" id="app">
    <div class="about_head">
        <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user/withdraw-method']);?>'"><p class="p1"><img src="/images/11_1.png"></p></a>
        <p class="p2" v-if="user_bank.bank_name == '支付宝'">提现到支付宝</p>
        <p class="p2" v-else>提现到银行卡</p>
    </div><!--about_head-->
    <div class="cash_payment">
        <div class="top">
            <div class="div1">{{user_bank.account_name}}</div>
            <div class="div2">{{user_bank.account_no | noFormat}}</div>
            <div class="div3">{{user_bank.bank_name}}</div>
        </div>
        <form @submit.prevent="submit">
            <div class="center">
                <div class="div1">
                    <label>提现金额</label>
                    <input type="number" placeholder="输入提现金额" v-model="money">
                </div>
                <div class="div1">
                    <label>支付密码</label>
                    <input type="password" placeholder="输入支付密码" v-model="payment_password">
                </div>
                <div class="div2">
                    <span>可用余额</span>
                    <span class="span2">{{account.commission}}</span>
                    <span>元</span>
                </div>
            </div>
            <button class="cash_payment_but">确定</button>
        </form>
    </div><!--cash_payment-->
</div><!--box-->
<script>
    var app = new Vue({
        el: "#app",
        data: {
            account: {commission: 0},
            user_bank: {
                id: 0, // 用户绑定银行卡id
                bank_name: '', // 银行名称
                account_name: '', // 姓名
                account_no: '' // 账号
            },
            money: 0, // 提现金额
            payment_password: '' // 支付密码
        },
        methods: {
            getAccount: function () {
                apiGet('<?php echo Url::to(['/api/user/account']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.account = json['account'];
                        app.getBankList();
                    }
                });
            },
            getBankList: function () {
                var self = this;
                apiGet('<?php echo Url::to(['/api/user/withdraw-bank-list']);?>', {}, function (json) {
                    if (callback(json)) {
                        var bank_id = Util.request.get('bank_id');
                        json['withdraw_bank_list'].forEach(function (bank) {
                            if (bank.id == bank_id) {
                                self.user_bank = bank;
                            }
                        });
                    }
                });
            },
            submit: function () {
                if (app.money <= 0) {
                    layer.msg('请填写正确的提现金额。', function () {});
                    return false;
                }
                if (app.payment_password == '') {
                    layer.msg('请输入支付密码。', function () {});
                    return false;
                }
                apiPost('<?php echo Url::to(['/api/user/withdraw']);?>', {bank_id:app.user_bank.id, money:app.money, payment_password: app.payment_password}, function (json) {
                    if (callback(json)) {
                        window.location.href='<?php echo Url::to(['/h5/user/commission-list']);?>';
                    }
                });
            }
        },
        filters: {
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
            this.getAccount();
        }
    });
</script>
