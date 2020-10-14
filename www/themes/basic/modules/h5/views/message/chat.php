<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\SocketAsset;
use app\assets\VueAsset;
use app\models\ChatMessage;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
SocketAsset::register($this);
VueAsset::register($this);

$this->title = '聊天';
?>
<div class="container" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">{{shop.name}}</div>
        <div class="mall-header-right">
            <a :href="'<?php echo Url::to(['/h5/shop/view']);?>?id=' + shop.id"><img src="/images/talk_06.png"></a>
        </div>
    </header>
    <div class="container">
        <div class="chat-list" id="chat">
            <div v-for="(message, message_index) in message_list">
                <div class="clear"></div>
                <p class="time" v-if="message.create_time > 0">{{message.create_time | datetimeFormat}}</p>
                <!-- 文本消息 开始 -->
                <!-- 我发出的 开始 -->
                <div class="message mr" v-if="message.from == self_member && message.type == <?php echo ChatMessage::TYPE_TEXT;?>">
                    <div class="picture fr"><img :src="user.avatar"></div>
                    <div class="content fr">{{message.content}}</div>
                </div>
                <!-- 我发出的 结束 -->
                <!-- 我收到的 开始 -->
                <div class="message ml" v-if="message.from == shop.member && message.type == <?php echo ChatMessage::TYPE_TEXT;?>">
                    <div class="picture fl"><img :src="shop.avatar"></div>
                    <div class="content fl">{{message.content}}</div>
                </div>
                <!-- 我收到的 结束 -->
                <!-- 商品消息 开始 -->
                <div class="message mr goods" v-if="message.type == <?php echo ChatMessage::TYPE_GOODS;?>">
                    <div class="picture fr"><img :src="user.avatar"></div>
                    <div class="content fr">
                        <div class="goods-img fl">
                            <img :src="message.goods.main_pic" width="80">
                        </div>
                        <div class="fr goods-info">
                            <p>{{message.goods.title}}</p>
                            <p>￥{{message.goods.price}}</p>
                        </div>
                    </div>
                </div>
                <!-- 商品消息 结束 -->
            </div>
        </div>
        <div class="bottom">
            <form @submit.prevent="sendMsg()">
                <textarea id="send_message" v-model="message" placeholder="消息内容" @keyup.13="sendMsg()"></textarea>
                <button>提交</button>
            </form>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            cid: 0, // 会话编号
            member_list: [], // 成员列表
            shop: {}, // 店铺信息
            user: {}, // 用户信息
            self_member: '', // 自身聊天成员号
            message_list: [], // 消息列表
            time_after: 0, // 获取历史消息时后面一条消息的时间，用来判断是否需要显示时间
            time_before: 0, // 最后一条消息时间，用来判断是否需要显示时间
            message: '' // 准备发送的内容
        },
        methods: {
            getChat: function () {
                ws.send(JSON.stringify({
                    msg_id: -1,
                    type: 'command',
                    command: 'get_chat',
                    sid: Util.request.get('sid')
                }));
            },
            getChatMemberList: function () {
                ws.send(JSON.stringify({
                    msg_id: -2,
                    type: 'command',
                    command: 'get_chat_member_list',
                    cid: app.cid
                }));
            },
            getMessageList: function (id) {
                ws.send(JSON.stringify({
                    msg_id: -3,
                    type: 'command',
                    command: 'get_chat_msg',
                    cid: app.cid,
                    end_id: id
                }));
            },
            sendMsg: function () {
                if (/^\s*$/.test(this.message)) {
                    return;
                }
                ws.send(JSON.stringify({
                    msg_id: -4,
                    type: 'chat_msg',
                    msg: {
                        type: 'text',
                        cid: this.cid,
                        content: this.message
                    }
                }));
                this.message = '';
            },
            pushMsg: function (msg, pos) {
                if (pos == 1) {
                    if (msg['create_time'] - app.time_before > 100) {
                        app.time_before = msg['create_time'];
                    } else {
                        msg['create_time'] = 0;
                    }
                    app.message_list.push(msg);
                } else {
                    if (app.time_after == 0 || app.time_after - msg['create_time'] > 100) {
                        app.time_after = msg['create_time'];
                    } else {
                        msg['create_time'] = 0;
                    }
                    app.message_list.splice(0, 0, msg);
                }
            },
            scrollToBottom: function () {
                app.$nextTick(function () {
                    document.getElementById('chat').scrollTo(0, document.getElementById('chat').scrollHeight);
                });
            }
        },
        mounted: function () {
            if (!window.ws_onOpen) {
                window.ws_onOpen = [];
            }
            window.ws_onOpen.push(function () {
                app.cid = 0;
                app.member_list = [];
                app.shop = {};
                app.user = {};
                app.message_list = [];
                app.getChat();
            });
            if (!window.ws_onMessage) {
                window.ws_onMessage = [];
            }
            window.ws_onMessage.push(function (data) {
                var json = JSON.parse(data);
                if (json['type'] == 'response' && json['error_code'] == 20000) {
                    // Token失效，需要重新登录
                    apiGet('<?php echo Url::to(['/api/user/new-token']);?>', {}, function (json) {
                        if (callback(json)) {
                            localStorage.setItem('token', json['token']);
                        }
                    });
                }
                if (json['msg_id'] == -1) {
                    // 获取会话
                    app.self_member = json['member'];
                    app.cid = json['cid'];
                    app.getChatMemberList();
                } else if (json['msg_id'] == -2) {
                    // 获取成员列表
                    app.member_list = json['member_list'];
                    app.member_list.forEach(function (member) {
                        if (/shop_/.test(member['member'])) {
                            app.shop = member['data'];
                            app.shop['member'] = member['member'];
                        } else if (/user_/.test(member['member'])) {
                            app.user = member['data'];
                            app.user['member'] = member['member'];
                        }
                    });
                    app.getMessageList();
                } else if (json['msg_id'] == -3) {
                    // 获取聊天记录
                    json['msg_list'].forEach(function (msg) {
                        app.pushMsg(msg, -1);
                    });
                    app.scrollToBottom();
                    if (json['msg_list'].length > 0) {
                        app.getMessageList(json['msg_list'][json['msg_list'].length - 1]['id']);
                    } else {
                        try {
                            var gid = Util.request.get('gid');
                            if (!Util.isEmpty(gid)) {
                                ws.send(JSON.stringify({
                                    msg_id: -4,
                                    type: 'chat_msg',
                                    msg: {
                                        type: 'goods',
                                        cid: app.cid,
                                        content: JSON.stringify({
                                            'goods': {
                                                'id': gid
                                            }
                                        })
                                    }
                                }));
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    }
                } else if (json['msg_id'] == -4) {
                    // 刚刚发送的消息
                    app.pushMsg(json['msg'], 1);
                    app.scrollToBottom();
                } else if (json['type'] == 'chat_msg') {
                    app.pushMsg(json['msg'], 1);
                    app.scrollToBottom();
                }
            });
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
