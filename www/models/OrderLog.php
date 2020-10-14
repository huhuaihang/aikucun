<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 订单操作日志
 * Class OrderLog
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $oid 订单编号
 * @property integer $u_type 用户类型
 * @property integer $uid 用户编号
 * @property string $content 操作内容
 * @property string $data 附加数据
 * @property integer $time 时间
 */
class OrderLog extends ActiveRecord
{
    const U_TYPE_MANAGER = 1;
    const U_TYPE_AGENT = 2;
    const U_TYPE_MERCHANT = 3;
    const U_TYPE_USER = 4;
    const U_TYPE_SUPPLIER = 5;
    const U_TYPE_SYSTEM = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['oid', 'u_type', 'uid'], 'required'],
            [['content'], 'string', 'max' => 512],
            ['data', 'safe'],
        ];
    }

    /**
     * 记录订单操作日志
     * @param $uid integer 用户编号
     * @param $type integer 用户类型
     * @param $oid integer 订单编号
     * @param $content string 操作内容
     * @param $data string 附加数据
     * @return bool
     */
    public static function info($uid, $type, $oid, $content, $data = null)
    {
        $model = new OrderLog();
        $model->oid = $oid;
        $model->u_type = $type;
        $model->uid = $uid;
        $model->content = $content;
        $model->data = $data;
        $model->time = time();
        return $model->save();
    }
}
