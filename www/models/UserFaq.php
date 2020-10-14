<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 用户常见问题记录
 * Class UserFaq
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $uid 用户编号
 * @property integer $fid 常见问题编号
 * @property integer $result 结果
 * @property integer $create_time 创建时间
 */
class UserFaq extends ActiveRecord
{
    const RESULT_SUCCESS = 1;
    const RESULT_FAIL = 0;
}
