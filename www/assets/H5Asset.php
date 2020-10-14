<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * 前端页面资源
 * Class H5Asset
 * @package app\assets
 */
class H5Asset extends AssetBundle
{
    public $baseUrl = '/';
    public $css = [
        'style/style.css?v=2020011803',
        'style/reset.css?v=2018022704',
    ];
    public $js = [
        'js/fontsize.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
