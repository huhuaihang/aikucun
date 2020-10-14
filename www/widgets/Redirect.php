<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\bootstrap\Alert;
use yii\helpers\Html;

/**
 * 自动跳转
 * Class Redirect
 * @package app\widgets
 *
 * 设置自动跳转：
 * Yii::$app->session->setFlash('redirect', json_encode([
 *     'sec' => 3, // 倒计时秒
 *     'url' => '/', // 跳转时的地址
 *     'txt' => '主页', // 跳转时的文字
 * ]));
 * 设置地址列表：
 * Yii::$app->session->setFlash('redirect', json_encode([
 *     'url_list' => [
 *         '<a href="/">转到主页</a>',
 *         '<a href="/user">转到用户中心</a>',
 *     ]
 * ]));
 */
class Redirect extends Widget
{
    public $sec = 0;

    /**
     * @inheritdoc
     */
    public function run()
    {
        $redirect = Yii::$app->session->getFlash('redirect');
        if (!empty($redirect)) {
            if (is_string($redirect)) {
                $redirect = json_decode($redirect, true);
            }
            if (!is_array($redirect)) {
                return;
            }
            $sec = 0;
            if (isset($redirect['sec'])) {
                $sec = $redirect['sec'];
            } elseif ($this->sec == 0 && isset(Yii::$app->params['redirect_sec'])) {
                $sec = Yii::$app->params['redirect_sec'];
            }
            if ($sec > 0) { // 自动跳转
                $url = $redirect['url'];
                $txt = $redirect['txt'];
                $id = $this->id;
                Yii::$app->view->registerJs("window.setInterval(function() {var sec = $('#redirect_{$id}_sec').html();if (--sec > 0) { $('#redirect_{$id}_sec').html(sec);} else {window.location = '{$url}';}}, 1000);");
                echo Alert::widget([
                    'body' => '<i class="icon fa fa-info big-120"></i> <span id="redirect_' . $id . '_sec">' . $sec . '</span>秒后自动转到 ' . Html::a($txt, $url),
                    'closeButton' => ['label' => '<i class="fa fa-times"></i>'],
                    'options' => [
                        'class' => 'alert-info',
                        'id' => $id . '-redirect'
                    ],
                ]);
            } else {
                $url_list = $redirect['url_list'];
                if (!empty($url_list)) {
                    echo Alert::widget([
                        'body' => implode('&nbsp;&nbsp;', $url_list),
                        'closeButton' => ['label' => '<i class="fa fa-times"></i>'],
                        'options' => [
                            'class' => 'alert-info',
                        ],
                    ]);
                }
            }
        }
    }
}
