<?php
/**
 * @var $this \yii\web\View
 */
use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
$this->title = '积分';
?>
<div class="box" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">个人积分</div>
    </header>
    <div class="score">
        <div class="score_w">
            <p>当前积分</p>
            <p>{{user_info.score}}</p>
        </div>
        <div class="score_e">
            <p>已使用</p>
            <p>{{user_info.used_score}}</p>
        </div>
    </div>
    <div class="score_y">
        <h2>全部记录</h2>
        <div class="score_t" ref="wrapper">
            <ul >
                <li v-for="log in score_list">
                    <div class="score_t_y">
                        <p style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">{{log.remark}}</p>
                        <p>{{log.time | datetimeFormat}}</p>
                    </div>
                    <div class="score_t_q">
                        <p class="score_yl" v-if="log.score<0">{{log.score}}</p>
                        <p class="score_ys" v-if="log.score>0">{{log.score}}</p>
                    </div>
                </li>

            </ul>
        </div>
    </div>
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            user_info:[],
            score_list: [], // 个人积分记录
            SearchForm: {
                page: 1,
            },
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {
            loadUser: function () {

                apiGet('/api/user/detail', {}, function (json) {
                    if (callback(json)) {
                        app.user_info = json['user'];

                    }
                    console.log(app.user_info)
                });

            },
            getScoreList: function () {

                apiGet('/api/user/account-score-list', this.SearchForm, function (json) {
                    if (callback(json)) {
                        json['list'].forEach(function (log) {
                            app.score_list.push(log);
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.SearchForm.page >= json['page']['pageCount']) {
                                           // layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.getScoreList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                    console.log(app.score_list)
                });

            }
        },
        filters: {
            numberToInt: function (value) {
                return parseInt(value);
            },
            datetimeFormat: function (timestamp) {
                var date = new Date(timestamp * 1000);
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
            this.loadUser();
            this.getScoreList();
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 190) + 'px';
        },
        updated:function () {


        }
    });

</script>