<?php

namespace app\assets;

use yii\web\View;

/**
 * Vuex组件
 * Class VuexAsset
 * @package app\assets
 */
class VuexAsset extends BaseAsset
{
    public $js = [
        'js/vuex.min.js',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $depends = [
        'app\assets\VueAsset',
    ];
}
