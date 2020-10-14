<?php

namespace app\modules\api\controllers;

use app\models\ApiClient;
use app\models\SystemVersion;
use app\models\User;
use app\modules\api\models\ApiException;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;
use yii\validators\Validator;

/**
 * API控制器基类
 * Class BaseController
 * @package app\modules\api\controllers
 */
class BaseController extends BasePublicController
{

    const TIMEOUT_SEC = 0; // 接口超时时间，单位秒
    const TIMEOUT_SIGN_REPEAT = 60; // 同一个请求验证重复的限制时间

    /**
     * @var string 客户端使用的接口版本号
     */
    protected $client_api_version = '';
    /**
     * @var string 客户端使用的AppId
     */
    protected $app_id = '';

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->request->isOptions) {
            if (!$this->checkVersion(Yii::$app->request->get('version'))) {
                return false;
            }
            if ($this->client_api_version >= '1.0.3') {
                if (!$this->checkSign()) {
                    return false;
                }
            } else {
                if (YII_ENV === 'prod' || $this->get('debug') != 1) {
                    $r = $this->apiPreCheck();
                    if ($r !== true) {
                        echo json_encode($r);
                        return false;
                    }
                }
            }
        }

        return parent::beforeAction($action);
    }

    /**
     * 检查版本
     * @param string $version 客户端使用的接口版本
     * @return boolean | array
     * @throws ApiException
     */
    private function checkVersion($version)
    {
        if (empty($version)) {
            throw new ApiException('PARAM', '没有找到接口版本号参数。');
        }
        /** @var SystemVersion $systemVersion */
        $systemVersion = SystemVersion::find()->andWhere(['api_version' => $version])->one();
        if (empty($systemVersion)) {
            throw new ApiException('VERSION', '版本号错误，没有找到此版本信息。');
        }
        if ($systemVersion->is_support != 1) {
            /** @var SystemVersion $version */
            $version = SystemVersion::find()->orderBy('create_time DESC')->one();
            if (empty($version)) {
                throw new ApiException('VERSION', '系统没有定义版本号信息。');
            }
            throw new ApiException('VERSION_UPDATE', '版本不兼容，请更新客户端。', [], [
                'error_code' => 10004,
                'api_version' => $version->api_version,
                'ios_version' => $version->ios_version,
                'android_version' => $version->android_version,
                'android_download_source' => $version->android_download_source,
                'android_download_url' => $version->android_download_url,
                'update_info' => $version->update_info,
            ]);
        }
        $this->client_api_version = $version;
        return true;
    }

    /**
     * 鉴权
     * @return true | array
     * @throws ApiException
     */
    private function checkSign()
    {
        $appid = Yii::$app->request->get('appid');
        if (empty($appid)) {
            throw new ApiException('PARAM', '没有找到AppId参数。');
        }
        $apiClient = ApiClient::findByAppId($appid);
        if (empty($apiClient)) {
            throw new ApiException('APP_ID', '没有找到接口授权。');
        }
        if ($apiClient->status != ApiClient::STATUS_OK) {
            throw new ApiException('APP_ID', '接口授权异常。');
        }
        $this->app_id = $appid;
        if (YII_ENV !== 'prod' && Yii::$app->request->get('debug') == 1) {
            return true;
        }
        return $apiClient->checkSign($this->get());
    }

    /**
     * 鉴权
     * @return true|array 成功返回true，失败返回错误信息
     */
    private function apiPreCheck()
    {
        $timestamp = $this->get('timestamp');
        $nonce = $this->get('nonce');
        $sign = $this->get('sign');
        // 检查必要参数是否存在
        if (empty($timestamp)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到时间戳参数。',
            ];
        }
        if (empty($nonce)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到随机字符参数。',
            ];
        }
        if (empty($sign)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到签名参数。',
            ];
        }
        // 签名验证
        $params = $this->get();
        unset($params['sign']);
        ksort($params);
        $_tmp_str = '';
        foreach ($params as $v) {
            if ($v === '') {
                continue;
            }
            $_tmp_str .= $v;
        }
        if (isset($params['appid'])) {
            $apiClient = ApiClient::findByAppId($params['appid']);
            if (empty($apiClient)) {
                return [
                    'error_code' => ErrorCode::APP_ID,
                    'message' => '没有找到当前App授权信息。',
                ];
            }
            if ($apiClient->status != ApiClient::STATUS_OK) {
                return [
                    'error_code' => ErrorCode::APP_ID,
                    'message' => '客户端授权失效。',
                ];
            }
            $_tmp_str .= $apiClient->app_secret;
        }
        $_sign = md5($_tmp_str);
        if (strtolower($sign) !== strtolower($_sign)) {
            return [
                'error_code' => ErrorCode::AUTH,
                'message' => '签名错误1。'.$_tmp_str,
            ];
        }
        // 超时验证
        $_timestamp = time();
        if (BaseController::TIMEOUT_SEC > 0) {
            if (
                ($timestamp > $_timestamp && $timestamp > $_timestamp + BaseController::TIMEOUT_SEC)
                ||
                ($timestamp < $_timestamp && $timestamp < $_timestamp - BaseController::TIMEOUT_SEC)
            ) {
                return [
                    'error_code' => ErrorCode::TIME,
                    'message' => '时间戳超时，请检查客户端时间。',
                    'timestamp' => $_timestamp,
                ];
            }
        }
        // 调用重复验证
        if (!empty(Yii::$app->cache->get('api_sign_' . $sign))) {
            Yii::warning('重复的签名。');
            Yii::$app->cache->set('api_sign_' . $sign, time(), 86400 * 30);
            return [
                'error_code' => ErrorCode::SIGN_REPEAT,
                'message' => '签名已经使用过，请重新生成新的签名。',
            ];
        }
        Yii::$app->cache->set('api_sign_' . $sign, $_timestamp, BaseController::TIMEOUT_SIGN_REPEAT);
        return true;
    }

    /**
     * 检查Post Json
     * @param array $rules 检查参数的规则
     * @see Model::rules()
     * @return array
     */
    protected function checkJson($rules = [])
    {
        $json = Yii::$app->request->rawBody;
        if (empty($json)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '没有找到请求数据。',
            ];
        }
        $json = json_decode($json, true);
        if (empty($json)) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => '无法正确解析请求数据，请检查提交数据。',
            ];
        }
        // 规则验证
        try {
            foreach ($rules as $rule) {
                $attributes = $rule[0];
                $validator = null;
                if ($rule instanceof Validator) {
                    $validator = $rule;
                } elseif (is_array($rule) && isset($rule[0], $rule[1])) {
                    $type = $rule[1];
                    $params = array_slice($rule, 2);
                    if (isset(Validator::$builtInValidators[$type])) {
                        $type = Validator::$builtInValidators[$type];
                    }
                    if (is_array($type)) {
                        $params = array_merge($type, $params);
                    } else {
                        $params['class'] = $type;
                    }
                    $validator = Yii::createObject($params);
                } else {
                    throw new Exception('验证规则错误。');
                }
                if (is_string($attributes)) {
                    $attributes = [$attributes];
                }
                foreach ($attributes as $attribute) {
                    if (!$validator->validate($this->param($json, $attribute), $error)) {
                        throw new Exception($error);
                    }
                }
            }
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::PARAM,
                'message' => $e->getMessage(),
            ];
        }
        return $json;
    }

    /**
     * 返回登录用户
     * @return array|User
     */
    protected function loginUser()
    {
        if (Yii::$app->user->isGuest) {
            $token = Yii::$app->request->headers->get('token');
            if (empty($token)) {
                $token = $this->get('token');
            }
            if (empty($token)) {
                return [
                    'error_code' => ErrorCode::PARAM,
                    'message' => '没有找到Token参数。',
                ];
            }

            try {
                if ($this->client_api_version >= '1.0.3') {
                    $user = User::findByToken($token);
                } else {
                    $user = User::findByTokenVersion($this->client_api_version, $token);
                }
            } catch (Exception $e) {
                return [
                    //'error_code' => ErrorCode::SERVER,
                    'error_code' => ErrorCode::USER_TOKEN,
                    'message' => $e->getMessage(),
                ];
            }
        } else {
            $user = Yii::$app->user->identity;
        }
        return $user;
    }

    /**
     * 获取请求参数
     * @param array $json 请求参数
     * @param string $name 参数名称，支持斜线隔开多层如：user/nickname获取{"user":{"id":1,"nickname":"Test"}}中的昵称信息
     * @param mixed $defaultValue 默认值
     * @param string|\Closure $filter 过滤器
     * @return mixed
     */
    protected function param(&$json, $name = null, $defaultValue = null, $filter = null)
    {
        $name_path = explode('/', $name);
        if (empty($name_path)) {
            return $defaultValue;
        }
        $v = $json;
        foreach ($name_path as $name) {
            $v = isset($v[$name]) ? $v[$name] : $defaultValue;
            if (empty($v)) {
                break;
            }
        }
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
