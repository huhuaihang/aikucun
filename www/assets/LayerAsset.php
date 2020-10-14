<?php

namespace app\assets;

/**
 * Layer弹层组件
 * Class LayerAsset
 * @package app\assets
 */
class LayerAsset extends BaseAsset
{
    public $sourcePath = '@app/themes/basic/assets/js/layer';
    public $js = [
        'layer.js',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        $view->registerCss('.layui-layer-btn a {font-size:small;}');
        return parent::register($view);
    }
}
