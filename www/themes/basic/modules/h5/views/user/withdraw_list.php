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

$this->title = '我的收入';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user'])?>'"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">我的收入</div>
    </header>
    <div class="container" id="app">
        <div class="z_me_shouru">
            <div class="div1">
                <p class="p1">当前余额(元)</p>
                <p class="p2">{{account.commission}}</p>
                <p class="p3"><a :href="'<?php echo Url::to(['/h5/user/withdraw-method']);?>'">提现</a></p>
            </div>
            <div class="div2_box">
                <div class="div2_tab">
                    <a :href="'<?php echo Url::to(['/h5/user/commission-list']);?>'" class="a1">收入</a>
                    <a :href="'<?php echo Url::to(['/h5/user/withdraw-list']);?>'" class="color">提现</a>
                </div>
                <div ref="wrapper" class="wrapper">
                    <ul>
                        <li class="div3" v-for="withdraw in withdraw_list">
                            <a :href="'/h5/user/withdraw-detail?withdraw_id=' + withdraw.id">
                                <div class="left">
                                 <p>{{withdraw.remark}}</p>
                                    <p class="p2">{{withdraw.create_time | timeFormat}}</p>
                                </div>
                                <div class="right">{{withdraw.money}}</div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div><!--div2_box-->
        </div><!--z_me_shouru-->
    </div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            account: {
                commission: 0
            },
            current_page: 1, // 当前页码
            scroll: false, // 滚动监听器
            withdraw_list: []
        },
        methods: {
            getAccount: function () {
                apiGet('<?php echo Url::to(['/api/user/account']);?>', {}, function (json) {
                    if (callback(json)) {
                        app.account = json['account'];
                        app.getWithdrawList();
                    }
                });
            },
            getWithdrawList: function () {
                apiGet('<?php echo Url::to(['/api/user/withdraw-list']);?>', {page: this.current_page}, function (json) {
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
                                            app.getWithdrawList();
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
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 300) + 'px';
            this.getAccount();
        }
    });
</script>
