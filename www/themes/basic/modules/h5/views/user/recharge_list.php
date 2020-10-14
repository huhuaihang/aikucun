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
$this->title = '进货记录';
?>
<div class="box" id="app">
    <div class="new_header">
        <a href="<?php echo Url::to(['/h5'])?>" class="a1"><img src="/images/new_header.png"></a>
        <a href="javascript:;" class="a2">进货记录</a>
    </div><!--new_header-->
    <div class="stock" ref="wrapper">
        <ul>
            <li v-for="recharge in recharge_list">
                <div class="stock-z">
                    <img src="/images/zhifu.png">
                </div>
                <div class="stock-zh">
                    <h2>支付宝</h2>
                    <p>{{ recharge.create_time | timeFormat }}</p>
                </div>
                <div class="stock-y">
                    <p class="color">支付成功</p>
                    <p>{{ recharge.money }}</p>
                </div>
            </li>
        </ul>
    </div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            recharge_list: [], // 充值列表
            current_page: 1, // 当前页码
            scroll: false // 滚动监听器
        },
        methods: {
            getRechargeList: function () {
                apiGet('<?php echo Url::to(['/api/user/recharge-list']);?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['recharge_list'].forEach(function (recharge) {
                            app.recharge_list.push(recharge);
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
            this.getRechargeList();
        }
    });
</script>
