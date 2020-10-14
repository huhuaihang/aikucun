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

$this->title = '我的推荐';
?>
<div class="box" id="app">
    <div class="sh-head">
        <img onClick="window.location.href='<?php echo Url::to(['/h5/user/my-agent']);?>'" src="/images/return.png">
        <span>我的推荐</span>
    </div>
    <div class="tj-t">
        <ul>
            <li>
                <span>一级推荐人</span>
            </li>
            <li>
                <span>二级推荐人</span>
            </li>
            <li>
                <span>三级推荐人</span>
            </li>
        </ul>
    </div>
    <div id="wrap" ref="wrapper">
        <div id="tit">
            <ul>
                <li :class="{select: index == 0}" v-for="(superior, index) in superior_list" @click="select($event);">
                    <span><img :src="superior.avatar" alt="">{{ superior.nickname }}</span>
                </li>
            </ul>
        </div>
        <div id="con_1">
            <div class="con_1" :class="{show2: index == 0}" v-for="(superior, index) in superior_list">
                <div class="erji">
                    <ul>
                        <li :class="{select1: i == 0}" v-for="(sc, i) in superior.c_list" @click="select2($event);">
                            <span><img :src="sc.avatar" alt="">{{ sc.nickname }}</span>
                        </li>
                    </ul>
                </div>
                <div class="sanji">
                    <div class="con1" :class="{show1: i == 0}" v-for="(sc, i) in superior.c_list">
                        <ul>
                            <li v-for="scc in sc.c_list">
                                <span class="sanj"><img :src="scc.avatar" alt="">{{ scc.nickname }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: "#app",
        data: {
            superior_list: [], // 充值列表
            current_page: 1, // 当前页码
            scroll: false // 滚动监听器
        },
        methods: {
            page_init: function() {
                $("#wrap").height($(window).height()-($(".tj-t").height()+$(".sh-head").height()));
                $("#tit").height($(window).height()-($(".tj-t").height()+$(".sh-head").height()));
                $(".con_1").height($(window).height()-($(".tj-t").height()+$(".sh-head").height()));
                $(".con1").height($(window).height()-($(".tj-t").height()+$(".sh-head").height()));
            },
            select: function (event) {
                var _this = event.currentTarget;
                var i = $(_this).index();
                $(_this).addClass('select').siblings().removeClass('select');
                $('.con_1').eq(i).show().siblings().hide();
            },
            select2: function (event) {
                var _this = event.currentTarget;
                var i = $(_this).index();
                $(_this).addClass('select1').siblings().removeClass('select1');
                $(_this).parent().parent().parent().find('.con1').eq(i).show().siblings().hide();
            },
            getSuperiorList: function () {
                apiGet('<?php echo Url::to(['/api/user/superior-list']);?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['superior_list'].forEach(function (superior) {
                            app.superior_list.push(superior);
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
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 300) + 'px';
            this.page_init();
            this.getSuperiorList();
        }
    });
</script>
