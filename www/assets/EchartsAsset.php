<?php

namespace app\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * 百度Echarts图表组件
 * Class EchartsAsset
 * @package app\assets
 */
class EchartsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/bower/echarts';
    public $js = [
        'dist/echarts.min.js',
        'theme/macarons.js'
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];

    /**
     * 注册地图数据
     * @param $view yii\web\View
     * @throws \yii\base\InvalidConfigException
     */
    public static function registerMap($view)
    {
        $base_url = Yii::$app->assetManager->getBundle(EchartsAsset::className())->baseUrl;
        $view->registerJsFile($base_url . '/map/js/china.js', ['depends' => 'app\assets\EchartsAsset']);
    }
}
