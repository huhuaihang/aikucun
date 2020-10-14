<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 定时任务
 * Class Task
 * @package app\models
 *
 * 使用方法：
 * ```php
 * // 定义定时任务需要执行的方法
 * class User ... {
 *     // 定时任务
 *     public static function do_task($uid)
 *     {
 *         Yii::info('执行用户定时任务：' . $uid);
 *         return '定时任务执行完成：' . $uid;
 *         // 如果返回值为数组且包含字段_del等于true时，当前任务如果没有下次执行时间了，就会删掉当前任务
 *     }
 * }
 * // 生成定时任务
 * $task = new Task();
 * $task->u_type = Task::U_TYPE_USER; // 前台用户
 * $task->uid = Yii::$app->user->id; // 发起任务的用户编号
 * $task->name = '测试定时任务'; // 任务标题
 * $task->next = 0; // 如果为0，下一分钟开始执行
 * $task->cron = '* 8 * * *'; // 每天8点执行一次，CRON指令，具体规则参考app\models\Cron
 * $task->todo = json_encode(['class'=>User::className(), 'method'=>'do_task', 'params'=>123]); // 计划任务
 * $task->result = ''; // 只读，任务执行结果
 * $task->status = Task::STATUS_WAITING;
 * $task->save();
 * ```
 *
 * @property integer $id PK
 * @property integer $u_type 用户类型
 * @property integer $uid 用户编号
 * @property string $name 任务名称
 * @property integer $next 下次执行时间
 * @property string $cron CRON指令
 * @property string $todo 任务内容
 * @property string $result 上次任务执行结果
 * @property integer $status 状态
 */
class Task extends ActiveRecord
{
    const U_TYPE_MANAGER = 1;
    const U_TYPE_AGENT = 2;
    const U_TYPE_MERCHANT = 3;
    const U_TYPE_USER = 4;

    const STATUS_WAITING = 1; // 等待执行
    const STATUS_DOING = 2; // 正在执行过程中
    const STATUS_FINISHED = 3; // 执行完成，可以在$task->result得到执行结果
    const STATUS_PAUSED = 9; // 执行暂停

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 32],
            ['cron', 'string', 'max' => 128],
            ['todo', 'safe'],
        ];
    }
}
