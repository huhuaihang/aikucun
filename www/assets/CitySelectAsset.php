<?php

namespace app\assets;

use yii\web\View;

/**
 * 城市选择组件
 * Class CitySelectAsset
 * @package app\assets
 */
class CitySelectAsset extends BaseAsset
{
    public $js = [
        'js/jquery.citys.js?v=2017110101',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
    public $depends = [
        'app\assets\ApiAsset',
        'yii\web\JqueryAsset',
    ];
}
