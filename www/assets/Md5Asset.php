<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * 客户端MD5加密组件
 * Class Md5Asset
 * @package app\assets
 */
class Md5Asset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/blueimp-md5/js';
    public $js = [
        'md5.js',
    ];
}
