<?php

namespace app\modules\api\controllers;

use app\controllers\BaseController;
use app\models\User;
use app\modules\api\models\ApiException;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;
use yii\validators\Validator;
use yii\web\Response;

/**
 * 公开接口基类
 * Class BasePublicController
 * @package app\modules\api\controllers
 */
class BasePublicController extends BaseController
{
    /**
     * @var array 客户端提交的JSON数据
     */
    protected $json = [];

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function beforeAction($action)
    {
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        $allow_origin = [
            Yii::$app->params['site_host'],
            'https://omoh5.ysjjmall.com',
            'http://localhost:8081'
        ];
        if (in_array(YII_ENV, ['dev', 'test'])) {
            $allow_origin[] = $_SERVER['HTTP_HOST'];
            if (isset($_SERVER['HTTP_ORIGIN'])) {
                $allow_origin[] = $_SERVER['HTTP_ORIGIN'];
            }
        }
        if (in_array($origin, $allow_origin)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Max-Age: 86400');
            header('Access-Control-Allow-Headers: Content-Type, token');
            header('Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST, DELETE');
        }
        if (Yii::$app->request->isOptions) {
            return false;
        }
        $this->enableCsrfValidation = false;
        if ($this->id != 'page') {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }

        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        if (is_array($result) && !isset($result['error'])) {
            // 如果接口没有任何错误，添加默认值
            $result['error'] = '';
        }
        if (is_array($result) && !isset($result['error_code'])) {
            // 如果接口没有任何错误，添加默认值
            $result['error_code'] = 0;
        }
        if (is_array($result) && empty($result['error'])) {
            if ($this->get('appid') == 'ios') {
                // 接口返回的内容，统一处理null值
                array_walk_recursive($result, function (&$v) {
                    if (is_null($v)) {
                        $v = '';
                    }
                });
            }
        }
        if (is_array($result) && isset($result['error_code'])) {
            // 接口返回的内容，统一处理null值
            array_walk_recursive($result, function (&$v) {
                if (is_null($v)) {
                    $v = '';
                }
            });
        }
        if (YII_DEBUG && is_array($result)) {
            Yii::warning(json_encode($result));
        }
        return parent::afterAction($action, $result);
    }

    /**
     * 获取请求参数
     * @param string $name 参数名称，支持斜线隔开多层如：user/nickname获取{"user":{"id":1,"nickname":"Test"}}中的昵称信息
     * @param mixed $defaultValue 默认值
     * @param string|\Closure $filter 过滤器
     * @param array|null $json 源
     * @return mixed
     * @throws ApiException
     */
    protected function json($name = null, $defaultValue = null, $filter = null, $json = null)
    {
        if ($json === null) {
            if (empty($this->json)) {
                $this->checkJson();
            }
            $json = $this->json;
        }
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

    /**
     * 检查Post Json
     * @param array $rules 检查参数的规则
     * @see Model::rules()
     * @return array
     * @throws ApiException
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
        }  catch (ApiException $e) {
            //throw $e;
        } catch (Exception $e) {
            throw new ApiException('SERVER', $e->getMessage());
        }
        return $json;
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

    /**
     * 检查Post Json
     * @param array $rules 检查参数的规则
     * @see Model::rules()
     * @return boolean
     * @throws ApiException
     */
    protected function checkJson_bak($rules = [])
    {
        if (empty($this->json)) {
            $raw = Yii::$app->request->rawBody;
            if (empty($raw)) {
                throw new ApiException('PARAM', '没有找到请求数据。');
            }
            $json = json_decode($raw, true);
            if ($json === null) {
                throw new ApiException('JSON', '无法正确解析请求数据，请检查提交数据。', [], [
                    'json' => $raw,
                ]);
            }
            $this->json = $json;
            $_POST['json'] = $json;
            if (YII_DEBUG) {
                Yii::warning($raw);
            }
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
                    throw new ApiException('RULES', '验证规则错误。');
                }
                if (is_string($attributes)) {
                    $attributes = [$attributes];
                }
                $errors = [];
                foreach ($attributes as $attribute) {
                    if (!$validator->validate($this->json($attribute), $error)) {
                        $errors[$attribute] = [$error];
                    }
                }
                if (!empty($errors)) {
                    throw new ApiException('PARAM', '参数错误。', $errors);
                }
            }
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new ApiException('SERVER', $e->getMessage());
        }
    }

    /**
     * 返回登录用户
     * @return User | array
     * @throws ApiException
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
                //throw new ApiException('PARAM', '没有找到Token参数。');
            }
            try {
                $user = User::findByToken($token);
            } catch (Exception $e) {
                return [
                    'error_code' => ErrorCode::USER_TOKEN,
                    'message' => $e->getMessage(),
                ];
            }
        } else {
            $user = Yii::$app->user->identity;
        }
        return $user;
    }
}
