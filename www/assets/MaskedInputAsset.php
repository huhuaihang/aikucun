<?php

namespace app\assets;

/**
 * MaskedInput组件
 * Class MaskedInputAsset
 * @package app\assets
 */
class MaskedInputAsset extends \yii\widgets\MaskedInputAsset
{
    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        $view->registerJs('$("input.masked").each(function() {$(this).inputmask($(this).attr("data-mask"));});');
        return parent::register($view);
    }
}
