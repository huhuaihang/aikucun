<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 系统错误
 * Class SystemError
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $time 时间
 * @property string $message 内容
 * @property string $code 代码
 * @property string $file 文件
 * @property integer $line 行号
 * @property string $trace 追踪信息
 * @property string $context 环境信息
 * @property integer $status 状态
 */
class SystemError extends ActiveRecord
{
    const STATUS_WAIT = 1;
    const STATUS_OLD = 9;
    const STATUS_DEL = 0;
}
