<?php

return [
    'adminEmail' => 'a761208@gmail.com',
    'appName' => '云淘帮',
    'companyName' => '云淘帮',
    'redirect_sec' => 2, // 管理后台操作完成后自动跳转倒计时秒
    'site_host' => 'http://yuntaobang.ysjjmall.com',

    'upload_path' => dirname(__DIR__) . '/web/uploads/',
    'upload_url' => '/uploads/',

    'api_timeout' => 0, // 接口时间戳超时时间
    'api_sign_repeat_timeout' => 86400, // 接口签名判断重复的时间
];
