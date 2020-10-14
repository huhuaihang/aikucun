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

$this->title = '现金账户';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user'])?>'"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">现金账户</div>
    </header>
    <div class="cashaccount" id="app">
        <div class="top">
            <P class="p1">当前余额（元）</P>
            <P class="p2">{{account.money}}</P>
        </div><!--top-->
        <div ref="wrapper" class="wrapper">
            <div class="cashaccount_content">
                <div class="div1">账户明细</div>
                <div class="div2" :class="{pay:item['money'] < 0, add:item['money'] >= 0}" v-for="item in log_list">
                    <div class="left">
                        <p class="p1">{{item['remark']}}</p>
                        <p class="p2">{{item['time'] | timeFormat}}</p>
                    </div><!--left-->
                    <div class="right">
                        {{item['money']}}
                    </div><!--right-->
                </div><!--div2-->
            </div><!--cashaccount_content列表-->
        </div>
    </div><!--cashaccount-->
</div><!--box-->
<script>
    var app = new Vue({
        el: "#app",
        data: {
            account: {
                money: 0
            }, // 账户金额
            log_list: [], // 明细列表
            current_page: 1, // 当前页码
            scroll: false // 滚动监听器
        },
        methods: {
            /**
             * 加载账户信息
             */
            getAccount: function () {
                apiGet('<?php echo Url::to(['/api/user/account'])?>', {}, function (json) {
                    if (callback(json)) {
                        app.account = json['account'];
                        app.getMoneyList();
                    }
                });
            },
            /**
             * 加载明细列表
             */
            getMoneyList: function () {
                apiGet('<?php echo Url::to(['/api/user/account-money-list'])?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (item) {
                            app.log_list.push(item);
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
                                            app.getMoneyList();
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
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 215) + 'px';
            this.getAccount();
        }
    });
</script>
<style>
    body {
        background: #EFEFEF !important;
    }
</style>