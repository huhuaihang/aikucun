<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * 管理后台默认控制器
 * Class DefaultController
 * @package app\modules\admin\controllers
 */
class DefaultController extends BaseController
{
    /**
     * 管理后台主页
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
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
        $this->layout = false;
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
}
