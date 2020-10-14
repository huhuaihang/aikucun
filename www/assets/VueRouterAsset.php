<?php

namespace app\assets;

use yii\web\View;

/**
 * VueRouter组件
 * Class VueRouterAsset
 * @package app\assets
 */
class VueRouterAsset extends BaseAsset
{
    public $js = [
        'js/vue-router.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $depends = [
        'app\assets\VueAsset',
    ];
}
