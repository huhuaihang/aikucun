// 需要在全局定义变量 API_VERSION 接口版本号
// 需要在全局定义变量 WS_URL WebSocket地址
var ws;//websocket实例
var lockReconnect = false;//避免重复连接
function getNewToken()
{
    apiGet('/api/user/new-token', {}, function (json) {
        localStorage.setItem('token', json['token']);
    });
}
function createWebSocket(url) {
    url = url + '?version=' + API_VERSION;
    if (url.indexOf('token') === -1) {
        var token = localStorage.getItem('token');
        if (token !== undefined && token !== null) {
            url = url + '&token=' + encodeURIComponent(token);
        }
        if (url.indexOf('token') === -1) {
            getNewToken();
        }
    }
    try {
        ws = new WebSocket(url);
        initEventHandle();
    } catch (e) {
        reconnect(url);
    }
}
function initEventHandle() {
    if (!window.ws_onOpen) {
        window.ws_onOpen = [];
    }
    if (!window.ws_onMessage) {
        window.ws_onMessage = [];
    }
    ws.onclose = function () {
        reconnect(WS_URL);
    };
    ws.onerror = function () {
        reconnect(WS_URL);
    };
    ws.onopen = function () {
        //心跳检测重置
        heartCheck.reset().start();
        if (window.ws_onOpen) {
            window.ws_onOpen.forEach(function (callback) {
                callback();
            });
        }
    };
    ws.onmessage = function (event) {
        //如果获取到消息，心跳检测重置
        //拿到任何消息都说明当前连接是正常的
        heartCheck.reset().start();
        if (window.ws_onMessage) {
            window.ws_onMessage.forEach(function (callback) {
                callback(event.data);
            });
        }
    };
}
function reconnect(url) {
    if(lockReconnect) return;
    lockReconnect = true;
    //没连接上会一直重连，设置延迟避免请求过多
    setTimeout(function () {
        createWebSocket(url);
        lockReconnect = false;
    }, 2000);
}
//心跳检测
var heartCheck = {
    timeout: 49876, // 毫秒
    timeoutObj: null,
    serverTimeoutObj: null,
    reset: function(){
        clearTimeout(this.timeoutObj);
        clearTimeout(this.serverTimeoutObj);
        return this;
    },
    start: function() {
        var self = this;
        this.timeoutObj = setTimeout(function(){
            //这里发送一个心跳，后端收到后，返回一个心跳消息，
            //onmessage拿到返回的心跳就说明连接正常
            ws.send(JSON.stringify({type:'heart_beat'}));
            self.serverTimeoutObj = setTimeout(function(){//如果超过一定时间还没重置，说明后端主动断开了
                ws.close();//如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
            }, self.timeout);
        }, this.timeout);
    }
};
createWebSocket(WS_URL);
