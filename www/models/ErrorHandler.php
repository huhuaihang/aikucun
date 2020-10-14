<?php

namespace app\models;

/**
 * 自定义异常处理
 * Class ErrorHandler
 * @package app\models
 */
class ErrorHandler extends \yii\web\ErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function renderException($exception)
    {
        parent::renderException($exception);
        $error = new SystemError();
        $error->message = $exception->getMessage();
        $error->code = $exception->getCode();
        $error->file = $exception->getFile();
        $error->line = $exception->getLine();
        $error->trace = $exception->getTraceAsString();
        $error->context = json_encode([
            'session' => isset($_SESSION) ? $_SESSION : null,
            'get' => $_GET,
            'post' => $_POST,
            'raw' => file_get_contents('php://input'),
            'cookie' => $_COOKIE,
            'server' => $_SERVER,
        ]);
        $error->time = time();
        $error->status = SystemError::STATUS_WAIT;
        $error->save();
    }
}
