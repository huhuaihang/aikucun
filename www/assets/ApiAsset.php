<?php

namespace app\assets;

use yii\web\View;

/**
 * 接口组件
 * Class ApiAsset
 * @package app\assets
 */
class ApiAsset extends BaseAsset
{
    public $js = [
        'js/axios.min.js',
        'js/api.js?v=2018013102',
    ];
    public $depends = [
        'app\assets\Md5Asset',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        $view->registerJs('var API_VERSION = "1.0.5";', View::POS_HEAD);
        $view->registerJs('var API_APP_ID = "h5";', View::POS_HEAD, 'API_APP_ID');
        $view->registerJs('var API_APP_SECRET = "S60AXNHwp32D5L0S8QdVFRvlLwrTIw8k";', View::POS_HEAD, 'API_APP_SECRET');
        return parent::register($view);
    }
}
