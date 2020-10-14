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
$this->registerJsFile("/js/selectFilter.js");

$this->title = '我的收益';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user/my-agent']);?>'"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">我的收益</div>
    </header>
    <div class="ge" style="margin-top: 1rem"></div>
    <div class="shouyi">
        <p>当前余额(元)</p>
        <p>{{ account.commission }}</p>
        <a href="<?php echo Url::to(['/h5/user/withdraw-method'])?>">提现</a>
    </div>
    <div class="ge"></div>

    <div class="item">
        <div class="filter-box">
            <div class="filter-text">
                <input class="filter-title" type="text" readonly placeholder="pleace select" />
                <i class="icon icon-filter-arrow"></i>
            </div>
            <select name="filter" id="ce_id">
                <option value="mx1" id="mx1" selected>收益明细</option>
                <option value="tx1" id="tx1">提现历史</option>
                <option value="zc1" id="zc1">支出明细</option>
            </select>
        </div>
    </div>
    <div class="mingxi" id="mx" ref="wrapper">
        <ul>
            <li v-for="commission in commission_list">
                <img :src="commission.logo" alt="">
                <div>
                    <p>{{ commission.nickname }}</p>
                    <p>{{ commission.time | timeFormat }}</p>
                </div>
                <div>
                    +{{ commission.commission }}
                </div>
            </li>
        </ul>
    </div>
    <div class="mingxi" id="tx" ref="wrapper">
        <ul>
            <li v-for="withdraw in withdraw_list">
                <a :href="'/h5/user/withdraw-detail?withdraw_id=' + withdraw.id">
                <img v-if="withdraw.bank_logo != ''" :src="withdraw.bank_logo" alt="">
                <img v-else src="/images/tixian4.png" >
                <div>
                    <p>余额提现</p>
                    <p>{{ withdraw.create_time | timeFormat }}</p>
                </div>
                <div>
                    {{ withdraw.money }}
                </div>
                </a>
            </li>
        </ul>
    </div>
    <div class="mingxi1" id="zc" ref="wrapper">
        <ul>
            <li v-for="pay in pay_list">
                <div>
                    <p>{{ pay.remark }}</p>
                    <p>{{ pay.time | timeFormat }}</p>
                </div>
                <div>
                    {{ pay.commission }}
                </div>
            </li>
        </ul>
    </div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            account: {
                commission: 0
            }, // 账户金额
            commission_list: [], // 佣金列表
            withdraw_list: [], // 提现列表
            pay_list: [], // 支出列表
            current_page: 1, // 当前页码
            scroll: false // 滚动监听器
        },
        methods: {
            getAccount: function () {
                apiGet('<?php echo Url::to(['/api/user/account'])?>', {}, function (json) {
                    if (callback(json)) {
                        app.account = json['account'];
                        app.getCommissionList();
                        app.getWithdrawList();
                        app.getPayList();
                    }
                });
            },
            getCommissionList: function () {
                apiGet('<?php echo Url::to(['/api/user/commission-list'])?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['commission_list'].forEach(function (commission) {
                            app.commission_list.push(commission);
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
                                            app.getCommissionList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },
            getWithdrawList: function() {
                apiGet('<?php echo Url::to(['/api/user/withdraw-list'])?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['withdraw_list'].forEach(function (withdraw) {
                            app.withdraw_list.push(withdraw);
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
                                            app.getCommissionList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },
            getPayList: function () {
                apiGet('/api/user/account-commission-list', {}, function(json) {
                    if (callback(json)) {
                        json['list'].forEach(function (pay) {
                            app.pay_list.push(pay);
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
                                            app.getCommissionList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
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
                return y + '-' + M + '-' + d + ' ' + h + ':' + m + ':' + s;
            }
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 125) + 'px';
            this.getAccount();
        }
    });
</script>
<script type="text/javascript">

    function page_init() {
        $('.filter-box').selectFilter({
            callBack : function (val){
                //返回选择的值
                //console.log(val+'-是返回的值')
                //var selected=$(this).children('option:selected').val()
                //alert(val);
                if(val=="mx1"){
                    $("#mx").show();
                    $("#tx").hide();
                    $("#zc").hide();
                }else if(val=="tx1"){
                    $("#zc").hide();
                    $("#mx").hide();
                    $("#tx").show();
                }else if(val=="zc1"){
                    $("#zc").show();
                    $("#mx").hide();
                    $("#tx").hide();
                }
            }
        });
    }
</script>