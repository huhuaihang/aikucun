<?php

namespace app\assets;

use yii\web\View;

/**
 * MintUI组件
 * Class MintUIAsset
 * @package app\assets
 */
class MintUIAsset extends BaseAsset
{
    public $js = [
        'js/mintui.js',
    ];
    public  $css=[
        'style/mintui_style.css',
    ];
    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

}
