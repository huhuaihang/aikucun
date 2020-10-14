<?php
/**
 * Created by PhpStorm.
 * User: tenda
 * Date: 2019/4/18
 * Time: 14:43
 */


namespace app\assets;

use yii\web\View;

/**
 * 前端页面资源
 * Class H5Asset
 * @package app\assets
 */
class PhotoAsset extends BaseAsset
{

    public $js = [
        'js/photoswipe.min.js?v=4.1.3-1.0.4',
        'js/photoswipe-ui-default.min.js?v=4.1.3-1.0.4',
        'js/initPhotoSwipeFrom.js',
    ];

    public $css=[

        'style/photoswipe.css?v=4.1.3-1.0.4',
         'style/default-skin.css?v=4.1.3-1.0.4'
    ];

    public $depends = [
        'app\assets\ApiAsset',
        'app\assets\UtilAsset',
        'app\assets\VueAsset',
    ];
}
