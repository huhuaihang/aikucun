(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            (global.Util = factory());
}(this, (function () {'use strict';
    return {
        /**
         * 检查变量是否为空
         * @param v
         * @returns {boolean}
         */
        isEmpty: function (v) {
            if (v === undefined || v === null) {
                return true;
            }
            if (Array.isArray(v) && v.length === 0) {
                return true;
            }
            if (typeof v === 'boolean' && !v) {
                return true;
            }
            if (typeof v === 'string' && v === '') {
                return true;
            }
            if (typeof v === 'object') {
                return false;
            }
            if (typeof v === 'function') {
                return false;
            }
            return false;
        },
        /**
         * 请求处理
         */
        request: {
            /**
             * 获取请求地址查询参数
             * @param name
             * @returns {*}
             */
            get: function (name) {
                var params = {};
                window.location.search.split(/\?|&/).forEach(function (item) {
                    if (item !== '') {
                        item = item.split('=');
                        if (item.length > 1) {
                            params[item[0]] = decodeURIComponent(item[1]);
                        }
                    }
                });
                if (Util.isEmpty(name)) {
                    return params;
                }
                return params[name];
            }
        },
        /**
         * Cookie处理
         */
        cookie: {
            /**
             * 获取Cookie
             * @param name
             * @returns {*}
             */
            get: function (name) {
                var value = null;
                var cookies = document.cookie;
                if (cookies !== undefined && cookies !== null) {
                    cookies = cookies.split(';');
                    for (var i = 0, j = cookies.length; i < j; i++) {
                        var cookie = cookies[i];
                        if (cookie !== undefined && cookie !== null) {
                            cookie = cookie.replace(/^\s*|\s*$/, '').split('=');
                            if (cookie.length > 0) {
                                if (cookie[0] === name && cookie.length === 2) {
                                    value = cookie[1];
                                    break;
                                }
                            }
                        }
                    }
                }
                return value;
            },
            set: function (name, value) {
                document.cookie = name + '=' + value + ';path=/';
            }
        }
    };
})));
