<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * 文件上传组件
 * Class FileUploadAsset
 * @package app\assets
 */
class FileUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/blueimp/jquery-file-upload';
    public $css = [
        'css/jquery.fileupload.css',
    ];
    public $js = [
        'js/jquery.iframe-transport.js',
        'js/vendor/jquery.ui.widget.js',
        'js/jquery.fileupload.js',
    ];
    public $depends = [
        'app\assets\ApiAsset',
        'yii\web\JqueryAsset',
    ];
}
