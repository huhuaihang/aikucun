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

$this->title = '选择提现方式';
?>
<div class="box" id="app">
    <!--头部-->
    <div class="b_nav1 clearfix">
        <a class="b_fanhui" href="javascript:void(0);" onClick="window.location.href='<?php echo Url::to(['/h5/user/commission-list']);?>'"><img src="/images/b_arrow_left_03.png"></a>
        <h5>选择提现方式</h5>
    </div>
    <div class="clear"></div>
    <div class="tixian-1">
        <ul>
            <li>
                <a :href="'<?php echo Url::to(['/h5/user/withdraw-to-bank']);?>'">
                    <img src="/images/tixian1.png">
                    <p>提现到银行卡</p>
                    <img src="/images/tixian5.png">
                </a>
            </li>
            <li>
                <a :href="'<?php echo Url::to(['/h5/user/withdraw-to-zfb']);?>'">
                    <img src="/images/tixian2.png">
                    <p>提现到支付宝</p>
                    <img src="/images/tixian5.png">
                </a>
            </li>
        </ul>
    </div>
    <div class="tixian-2">
        <p>最近</p>
    </div>
    <div class="tixian-3 wrapper" ref="wrapper">
        <ul>
            <li v-for="bank in withdraw_bank_list">
                <a :href="'<?php echo Url::to(['/h5/user/withdraw'])?>?bank_id=' + bank.id">
                    <img :src="bank.bank_logo" v-if="bank.bank_name != '支付宝'">
                    <img src="/images/tixian4.png" v-else>
                    <div class="tixian-4">
                        <p>{{bank.account_name}}</p>
                        <p>{{bank.bank_name}}（{{bank.account_no | noFormat}}）</p>
                    </div>
                    <img src="/images/tixian5.png">
                </a>
            </li>
        </ul>
    </div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            withdraw_bank_list: [], // 银行卡列表
            current_page: 1, // 当前页码
            scroll: false // 滚动监听器
        },
        methods: {
            getBankList: function () {
                apiGet('<?php echo Url::to(['/api/user/withdraw-bank-list']);?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        if (json['withdraw_bank_list'].length == 0) {
                            app.checkPaymentPassword();
                        } else {
                            json['withdraw_bank_list'].forEach(function (bank) {
                                app.withdraw_bank_list.push(bank);
                            });
                            app.$nextTick(function () {
                                if (!app.scroll) {
                                    app.scroll = new BScroll(this.$refs.wrapper, {
                                        click: true,
                                        probeType: 1 // 非实时派发滚动事件
                                    });
                                    app.scroll.on('scrollEnd', function (pos) {
                                        if (pos.y < this.maxScrollY + 30) {
                                            if (app.current_page >= json['page']['pageCount']) {
                                                layer.msg('没有更多数据了。');
                                            } else {
                                                app.current_page++;
                                                app.getBankList();
                                            }
                                        }
                                    });
                                } else {
                                    app.scroll.refresh();
                                }
                            });
                        }
                    }
                });
            },
            checkPaymentPassword: function () {
                apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                    if (callback(json)) {
                        if (json['user']['have_payment_password'] == 0) {
                            layer.msg('请先设置支付密码。', function () {
                                window.location.href ='<?php echo Url::to(['/h5/user/payment-password']);?>';
                            });
                        }
                    }
                });
            }
        },
        filters: {
            noFormat: function (value) {
                if (value.indexOf('@') > -1) {
                    return value.substr(0,3) + '****' + value.substr(value.indexOf('@'));
                } else if(value.length < 16) {
                    return value.substr(0,3) + '****' + value.substr(-4);
                } else {
                    return value.substr(-4);
                }
            }
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 150) + 'px';
            this.getBankList();
        }
    });
</script>
