<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * 无限滚动组件
 * Class InfiniteScrollAsset
 * @package app\assets
 */
class InfiniteScrollAsset extends AssetBundle
{
    public $sourcePath = '@app/themes/basic/assets/js/infinitescroll';
    public $js = [
        'jquery.infinitescroll.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset'
    ];
}
