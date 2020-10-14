<?php

namespace app\modules\api\models;

use Throwable;
use yii\base\Exception;

/**
 * 接口异常
 * Class ApiException
 * @package app\modules\api\models
 */
class ApiException extends Exception
{
    /**
     * @var string 错误码，英文大写字符串，下划线隔开，如保存用户失败可以用：USER_SAVE
     */
    protected $error;
    /**
     * @var string 错误提示信息，可用户客户端提示
     */
    protected $message;
    /**
     * @var array 详细字段错误信息，可作为调试
     */
    protected $errors;
    /**
     * @var array 附加返回值
     */
    protected $extra;

    /**
     * ApiException constructor.
     * @param $error string 错误码
     * @param $message string 错误提示信息
     * @param array $errors 详细字段错误
     * @param array $extra 附加返回值
     * @param int $code 系统错误码
     * @param Throwable|null $previous
     */
    public function __construct($error, $message, $errors = [], $extra = [], $code = 0, Throwable $previous = null)
    {
        $this->error = $error;
        $this->message = $message;
        $this->errors = $errors;
        $this->extra = $extra;
        parent::__construct($message, $code, $previous);
    }

    /**
     * 返回错误详细信息
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 返回错误数组，可作为接口返回值
     * @return array
     */
    public function getErrorAsJson()
    {
        $code = ['SERVER' => 10006, 'PARAM' => 10000, 'SIGN' => 10006, 'RULES' => 10000,
            'TIME' => 10000, 'SIGN_REPEAT' => 10003, 'APP_ID' => 10007, 'VERSION' => 10006][$this->error];
        return array_merge([
            'error_code' => ($code > 0) ? $code : 0,
            'error' => $this->error,
            'message' => $this->message,
            'errors' => empty($this->errors) ? new \stdClass() : $this->errors,
        ], $this->extra);
    }
}
