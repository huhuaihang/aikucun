<?php

namespace app\assets;

use app\commands\SocketController;
use Yii;
use yii\web\View;

/**
 * Websocket客户端组件
 * Class SocketAsset
 * @package app\assets
 */
class SocketAsset extends BaseAsset
{
    public $js = [
        'js/socket.js?v=2018020101',
    ];
    public $depends = [
        'app\assets\ApiAsset',
    ];

    /**
     * @inheritdoc
     */
    public static function register($view)
    {
        ApiAsset::register($view);
        $url = preg_replace('/http/', 'ws', Yii::$app->params['site_host'])
            . ':' . SocketController::getPort()
            . '/';
        $view->registerJs('var WS_URL = "' . $url . '";', View::POS_HEAD);
        return parent::register($view);
    }
}
