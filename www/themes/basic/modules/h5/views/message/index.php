<?php

use app\assets\SocketAsset;
use app\assets\VueAsset;
use app\models\ChatMessage;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

SocketAsset::register($this);
VueAsset::register($this);

$this->title = '消息中心';
?>
<style>
    body {background:#fff;}
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/user']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">消息中心</div>
    </header>
    <div class="container" id="app">
        <div class="message">
            <div v-if="message_list.length == 0 && chat_list.length == 0">
                <div class="message_no">
                    <dl>
                        <dt><img src="/images/message_no.png"></dt>
                        <dd>当前没有任何消息</dd>
                    </dl>
                </div><!--message-->
            </div>
            <div v-for="(message, message_index) in message_list">
                <div class="div1">
                    <a href="<?php echo Url::to(['/h5/message/list']);?>">
                        <dl>
                            <dt><img src="/images/message_01.jpg"></dt>
                            <dd class="dd1"><span class="span1">系统消息</span><span class="span2">{{message.create_time | dateFormat}}</span></dd>
                            <dd class="dd2">{{message.title}}</dd>
                        </dl>
                    </a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="data_list">
                <div v-for="(chat, chat_index) in chat_list">
                    <div class="div1">
                        <a :href="'<?php echo Url::to(['/h5/message/chat']);?>?sid=' + chat.shop.id">
                            <dl>
                                <dt><img :src="chat.shop.logo"></dt>
                                <dd class="dd1">
                                    <span class="span1">{{chat.shop.name}}</span>
                                    <span class="span2" v-if="chat.last_msg !== undefined">{{chat.last_msg.create_time | dateFormat}}</span>
                                </dd>
                                <dd class="dd2" v-if="chat.last_msg !== undefined">{{chat.last_msg.content | messageFormat(chat.last_msg.type)}}</dd>
                            </dl>
                        </a>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            message_list: [], // 消息列表
            chat_list: [] // 聊天会话列表
        },
        methods: {
            getMessageList: function () {
                apiGet('<?php echo Url::to(['/api/user/message-list']);?>', {}, function (json) {
                    if (json['message_list'].length > 0) {
                        app.message_list = [json['message_list'][0]];
                        //app.dates();
                    }
                });
            },
            getChatList: function () {
                ws.send(JSON.stringify({
                    msg_id: -1,
                    type: 'command',
                    command: 'get_chat_list'
                }));
            }
        },
        mounted: function () {
            if (!window.ws_onOpen) {
                window.ws_onOpen = [];
            }
            window.ws_onOpen.push(function () {
                app.getChatList();
            });
            if (!window.ws_onMessage) {
                window.ws_onMessage = [];
            }
            window.ws_onMessage.push(function (data) {
                var json = JSON.parse(data);
                if (json['msg_id'] == -1) { // 会话列表
                    app.chat_list = json['chat_list'];
                }
            });
            this.getMessageList();
        },
        filters: {
            dateFormat: function (timestamp) {
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
            },
            messageFormat: function (message, type) {
                switch (type) {
                    case <?php echo ChatMessage::TYPE_GOODS;?>:
                        var json = JSON.parse(message);
                        return json['goods']['title'];
                    default:
                        return message;
                }
            }
        }
    });
</script>
