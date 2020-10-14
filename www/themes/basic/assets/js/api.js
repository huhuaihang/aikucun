// 需要在全局定义变量 API_VERSION 接口版本号
axios.interceptors.request.use(function (config) {
    // Do something before request is sent
    var token = localStorage.getItem('token');
    if (token !== undefined && token !== null) {
        config.headers['token'] = token;
    }
    return config;
}, function (error) {
    // Do something with request error
    return Promise.reject(error);
});

axios.interceptors.response.use(function (response) {
    // Do something with response data
    return response;
}, function (error) {
    try {
        callback(error.response.data);
    } catch (e) {
        console.log(e);
    }
    // Do something with response error
    return Promise.reject(error);
});

/**
 * 检查是否存在Token
 * @return boolean
 */
function checkToken() {
    var token = localStorage.getItem('token');
    return token !== undefined && token !== null;
}

/**
 * 生成签名
 * @param query_str
 */
function makeApiSign(query_str) {
    var query_params = query_str.split('&');
    query_params.sort();
    var temp_str = '';
    for (var i = 0, j = query_params.length; i < j; i++) {
        var p = query_params[i];
        try {
            var v = p.substr(p.indexOf('=') + 1);
            if (v !== '') {
                temp_str = temp_str + decodeURIComponent(v);
            }
        } catch (e) {
        }
    }
    return md5(temp_str + API_APP_SECRET);
   　// return md5(temp_str);
}

/**
 * 生成接口调用URL
 * @param url
 * @returns {string|*}
 */
function makeApiUrl(url) {
    if (url.indexOf('?') > -1) {
        url = url + '&ajax=1';
    } else {
        url = url + '?ajax=1';
    }
    url = url + '&version=' + API_VERSION+'&appid='+API_APP_ID ;
    var timestamp = parseInt((new Date()).getTime() / 1000);
    url = url + '&timestamp=' + timestamp;
    var nonce = parseInt(Math.random() * 99999999);
    url = url + '&nonce=' + nonce;
    var sign = makeApiSign(url.substr(url.indexOf('?') + 1));
    url = url + '&sign=' + sign;
    url = url.replace('?&', '?');
    return url;
}

/**
 * 发送Api Get请求
 * @param url
 * @param params
 * @param callback
 */
apiGet = function (url, params, callback) {
    if (url.indexOf('?') === -1) {
        url = url + '?';
    }
    for (var key in params) {
        var v = encodeURIComponent(params[key]);
        url = url + '&' + key + '=' + v;
    }
    url = url.replace('?&', '?');
    url = makeApiUrl(url);
    axios.get(url).then(function (response) {
        callback(response.data);
    });
};

/**
 * 发送Api Post请求
 * @param url
 * @param json
 * @param callback
 */
apiPost = function (url, json, callback) {
    url = makeApiUrl(url);
    axios.post(url, json, {headers: {"Content-Type": "application/json"}}).then(function (response) {
        callback(response.data);
    });
};

/**
 * 上传文件
 * @param url
 * @param file
 * @param callback
 */
apiFile = function (url, file, callback) {
    url = makeApiUrl(url);
    var formData = new FormData();
    formData.append('file', file);
    axios.post(url, formData, {headers:{'Content-Type':'multipart/form-data'}}).then(function (response) {
        callback(response.data);
    });
};
apiFiles = function (url, files, callback) {
    url = makeApiUrl(url);
    var formData = new FormData();
    files.forEach(function (file) {
        formData.append('files[]', file);
    });
    axios.post(url, formData, {headers:{'Content-Type':'multipart/form-data'}}).then(function (response) {
        callback(response.data);
    });
};

/**
 * AJAX请求通用回调判断
 * @param json AJAX返回的JSON内容
 * @returns {boolean}
 */
function callback(json) {
    var error_code = json['error_code'];
    var result = json["result"];
    if ((error_code !== undefined && error_code === 0) || (error_code !== undefined && error_code === '')) {
        result = 'success';
    }
    var js = json["js"];
    var message = json["message"];
    var location = json['location'];
    if (location !== undefined) {
        window.location.href = location;
        return true;
    }
    if (result === undefined && message === undefined) {
        message = "程序出现未知错误，请刷新页面后再试。\n如果此问题连续出现，请联系管理员解决。";
        if (window.layer) {
            layer.msg(message.replace(/\n/, '<br />'), {icon: 5});
        } else {
            alert(message);
        }
        return false;
    }
    if (js !== undefined) {
        eval(js);
    }
    if (message !== undefined) {
        if (window.layer) {
            var errors = json['errors'];
            if (errors !== undefined) {
                try {
                    message = message + '<ul>';
                    for (var attr in errors) {
                        var _errors = errors[attr];
                        _errors.forEach(function (error) {
                            message = message + '<li>' + error + '</li>';
                        });
                    }
                    message = message + '</ul>';
                } catch (e) {
                    console.log(e);
                }
            }
            layer.msg(message.replace(/\n/, '<br />'), {icon: 5});
        } else {
            alert(message);
        }
    }
    if (json['errors'] !== undefined) {
        console.error(json['errors']);
    }
    return result === "success";
}
