<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\Request;

/**
 * 管理员日志
 * Class ManagerLog
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 管理员编号
 * @property integer $time 时间
 * @property string $ip IP地址
 * @property string $content 内容
 * @property string $data 数据
 *
 * @property Manager $manager 关联管理员
 */
class ManagerLog extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 512],
            [['data'], 'safe'],
        ];
    }

    /**
     * 关联管理员
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(Manager::className(), ['id' => 'mid']);
    }

    /**
     * 记录日志
     * @param integer $mid 管理员编号
     * @param string $content 事件内容
     * @param string $data 数据
     * @return boolean 是否保存成功
     */
    public static function info($mid, $content, $data = null)
    {
        $model = new ManagerLog();
        $model->mid = $mid;
        $model->time = time();
        $model->ip = Yii::$app->request instanceof Request ? Yii::$app->request->userIP : null; // 如果命令行执行则没有IP
        $model->content = $content;
        $model->data = $data;
        return $model->save();
    }
}
