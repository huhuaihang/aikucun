<?php

namespace app\controllers;

use Da\QrCode\Action\QrCodeAction;
use Yii;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * Controller基类
 * Class BaseController
 * @package app\controllers
 */
class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if ($this->isAjax()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'captcha' => [ // 显示验证码
                'class' => 'yii\captcha\CaptchaAction',
                'testLimit' => 1,
                'width' => 80,
                'height' => 30,
                'padding' => -3,
                'minLength' => 4,
                'maxLength' => 4,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'qr' => [
                'class' => QrCodeAction::className(),
                'text' => Yii::$app->params['site_host'],
                'param' => 'content',
            ]
        ];
    }

    /**
     * 判断请求类型是否为GET
     * @return boolean
     */
    public function isGet()
    {
        return Yii::$app->request->isGet;
    }

    /**
     * 判断请求类型是否为POST
     * @return boolean
     */
    public function isPost()
    {
        return Yii::$app->request->isPost;
    }

    /**
     * 判断请求类型是否为AJAX
     * @return boolean
     */
    public function isAjax()
    {
        return Yii::$app->request->isAjax || isset($_REQUEST['ajax']);
    }

    /**
     * 返回Get请求参数
     * @see Request::get()
     * @param $name string 参数名称
     * @param $defaultValue mixed 默认值
     * @param $filter string 过滤器
     * @return mixed
     */
    public function get($name = null, $defaultValue = null, $filter = null)
    {
        $v = Yii::$app->request->get($name, $defaultValue);
        if (!empty($filter)) {
            if (is_string($filter)) {
                $v = call_user_func($filter, $v);
            } elseif ($filter instanceof \Closure) {
                $v = $filter($v);
            }
        }
        return $v;
    }

    /**
     * 返回Post请求参数
     * @see Request::post()
     * @param $name string 参数名称
     * @param $defaultValue mixed 默认值
     * @param $filter string 过滤器
     * @return mixed
     */
    public function post($name = null, $defaultValue = null, $filter = null)
    {
        $v = Yii::$app->request->post($name, $defaultValue);
        if (!empty($filter)) {
            if (is_string($filter)) {
                $v = call_user_func($filter, $v);
            } elseif ($filter instanceof \Closure) {
                $v = $filter($v);
            }
        }
        return $v;
    }
}
