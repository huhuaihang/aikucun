<?php

namespace app\controllers;

use app\models\Ad;
use Yii;
use yii\helpers\Url;
use yii\image\drivers\Image;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * 通用页
 * Class SiteController
 * @package app\controllers
 */
class SiteController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['img'],
                'cacheControlHeader' => 'public, max-age=864000',
                'etagSeed' => function ($action, $params) {
                    return md5(serialize($params));
                },
            ],
        ];
    }

    /**
     * 主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['/h5']);
    }

    /**
     * 广告跳转
     * @var $id integer 广告编号
     * 自动记录广告点击次数
     * @throws NotFoundHttpException
     * @return \yii\web\Response
     */
    public function actionDa()
    {
        $id = $this->get('id');
        $ad = Ad::findOne($id);
        if (empty($ad)) {
            throw new NotFoundHttpException('没有找到广告信息。');
        }
        if ($ad->status != Ad::STATUS_ACTIVE
            || ($ad->start_time > 0 && $ad->start_time > time())
            || ($ad->end_time > 0 && $ad->end_time < time())) {
            // 广告已经失效，不更新点击数
        } else {
            $ad->updateCounters(['click' => 1]);
        }
        return $this->redirect($ad->url);
    }

    /**
     * 错误页
     * 自动读取错误内容，页面访问的错误直接显示错误页面，AJAX请求则返回格式统一的JSON
     * {
     *     "result":"failure",
     *     "message":"错误提示。",
     *     "errors":...详细错误信息，一般只用来跟踪处理，不作为对用户展示...
     * }
     * @throws ServerErrorHttpException
     * @return array|string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            if ($this->isAjax()) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return [
                    'message'=>$exception->getMessage(),
                    'errors'=>YII_ENV_DEV ? $exception->getTraceAsString() : ''
                ];
            } else {
                return $this->render('error', [
                    'exception'=>$exception
                ]);
            }
        }
        throw new ServerErrorHttpException('没有找到任何错误信息。');
    }

    /**
     * 图片处理
     * GET方式提交参数：
     * @var $uri string 图片相对与保存目录的地址
     * @var $w=1000 integer 缩放后的最大宽度
     * @var $h=1000 integer 缩放后的最大高度
     * @var $r=0 integer 旋转角度
     * @var $m=4 integer 图片处理方式
     * @see \yii\image\drivers\Kohana_Image::AUTO
     * 图片处理后使用Yii自带的Cache保存缓存
     */
    public function actionImg()
    {
        $uri = Yii::$app->request->get('uri');
        $w = Yii::$app->request->get('w', 1000);
        $h = Yii::$app->request->get('h', 1000);
        $r = Yii::$app->request->get('r', 0);
        $m = Yii::$app->request->get('m', Image::AUTO);

        Yii::$app->response->format = Response::FORMAT_RAW;
        header('Content-Type: image/png');


        $cache_hash = 'image_' . md5(Url::current());
        $cache = Yii::$app->cache->get($cache_hash);
        if (!empty($cache)) {
            echo $cache;
        } else {
            /* @var $driver \yii\image\drivers\Image */
            $driver = Yii::$app->image->load(Yii::$app->params['upload_path'] . $uri);
            $_img = $driver->resize($w, $h, $m)->rotate($r)->render();
            Yii::$app->cache->set($cache_hash, $_img, 30 * 86400);
            echo $_img;
        }
    }
}
