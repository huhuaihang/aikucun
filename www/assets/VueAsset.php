<?php

namespace app\assets;

use yii\web\View;

/**
 * Vue框架组件
 * Class VueAsset
 * @package app\assets
 */
class VueAsset extends BaseAsset
{
    public $js = [
        'js/vue.min.js',
        'js/bscroll.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $depends = [
        'app\assets\ApiAsset',
        'app\assets\UtilAsset',
    ];
}
