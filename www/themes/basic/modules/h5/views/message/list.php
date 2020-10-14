<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\UserMessage;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->title = '消息列表';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to('/h5/message')?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">系统消息</div>
    </header>
    <div class="container" id="app" ref="wrapper">
        <div class="system_message">
            <div v-for="(message, index) in message_list">
                <p class="time">{{message.create_time | datetimeFormat }}</p>
                <div class="div1">
                    <div class="top">{{message.title}}</div>
                    <div class="system_bottom">{{message.content}}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            current_page: 1, // 当前页码
            message_list: [], // 消息列表
            scroll: false // 滚动监听器
        },
        methods: {
            loadMore: function () {
                apiGet('<?php echo Url::to(['/api/user/message-list']);?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['message_list'].forEach(function (message) {
                            app.message_list.push(message);
                            if (message['status'] == <?php echo UserMessage::STATUS_NEW;?>) {
                                app.setRead(message['id']);
                            }
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true, //
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.current_page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.current_page++;
                                            app.loadMore();
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
            setRead: function (id) {
                apiGet('<?php echo Url::to(['/api/user/set-message-read']);?>', {id:id}, function (json) {});
            }
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 65) + 'px';
            this.loadMore();
        },
        filters: {
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
        }
    });
</script>
