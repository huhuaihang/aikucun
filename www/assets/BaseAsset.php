<?php

namespace app\assets;

use Yii;
use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * 主题资源基类
 * Class BaseAsset
 * @package app\assets
 */
class BaseAsset extends AssetBundle
{
    public $sourcePath = '@app/themes/basic/assets';
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    /**
     * 获取根URL（需要使用register方法后）
     * @param string $url = '' 如果指定此参数则返回此路径的最终url
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function baseUrl($url = '')
    {
        return Yii::$app->assetManager->getBundle('app\assets\BaseAsset')->baseUrl . $url;
    }

    /**
     * 注册脚本文件
     * @param $view \yii\web\View
     * @param $js_file string
     * @throws \yii\base\InvalidConfigException
     */
    public static function registerJsFile($view, $js_file)
    {
        $view->registerJsFile(static::baseUrl($js_file), ['depends' => 'app\assets\BaseAsset']);
    }

    /**
     * 注册CSS文件
     * @param $view \yii\web\View
     * @param $css_file string
     * @throws \yii\base\InvalidConfigException
     */
    public static function registerCssFile($view, $css_file)
    {
        $view->registerCssFile(static::baseUrl($css_file), ['depends' => 'app\assets\BaseAsset']);
    }

    /**
     * 注册AJAX调用URL
     * @param \yii\web\View $view 调用页面中的View
     * @param string $name 名称
     * @param string|array $url URL
     */
    public static function registerAJAXURL($view, $name, $url)
    {
        if (is_array($url)) {
            $url = Url::to($url);
        }
        $view->registerJs('var AJAXURL = {};', View::POS_HEAD, 'AJAXURL');
        $view->registerJs('AJAXURL["' . $name . '"] = "' . $url . '";');
    }
}
