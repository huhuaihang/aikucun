<?php

namespace app\commands;

use app\models\AlipayApi;
use app\models\Cron;
use app\models\Order;
use app\models\OrderRefund;
use app\models\Task;
use app\models\UserBuyPack;
use app\models\UserPackageCoupon;
use app\models\WeixinAppApi;
use app\models\WeixinMpApi;
use Yii;
use yii\console\Controller;

/**
 * 定时任务
 * Class TaskController
 * @package app\commands
 */
class TaskController extends Controller
{
    /**
     * 每分钟执行一次
     * @throws \Exception
     */
    public function actionPerMinute()
    {
        $this->_do_task();
    }

    /**
     * 查找需要执行的定时任务f
     * @throws \Exception
     */
    private function _do_task()
    {
        // TODO：检测上次任务执行情况（上次没有执行完成，超时等）
        Yii::info('Begin Task');
        while (true) {
            /* @var $task \app\models\Task */
            $task = Task::find()
                ->andWhere(['status' => Task::STATUS_WAITING])
                ->andWhere(['<=', 'next', time()])->one();
            if (empty($task)) {
                break;
            }
            Yii::info(print_r($task, true));
            $task->status = Task::STATUS_DOING;
            $task->save();
            $todo = json_decode($task->todo, true);
            $result = [];
            $result['begin_time'] = time();
            $result['result'] = (new \ReflectionClass($todo['class']))
                ->getMethod($todo['method'])
                ->invoke(null, $todo['params']);
            $result['end_time'] = time();
            $_del = false; // 是否允许执行完成后删除当前任务
            if (is_array($result['result'])
                && isset($result['result']['_del'])
                && $result['result']['_del'] === true) {
                $_del = true;
            }
            $status = Task::STATUS_FINISHED;
            $next = $task->next;
            if (!empty($task->cron)) {
                $_next = Cron::parse($task->cron, time() + 60);
                if (!empty($_next)) {
                    $status = Task::STATUS_WAITING;
                    $next = $_next;
                    $_del = false; // 循环的任务不能删除
                }
            }
            if ($_del) {
                try {
                    $task->delete();
                } catch (\Throwable $t) {
                }
            } else {
                $task->status = $status;
                $task->next = $next;
                $task->result = json_encode($result);
                $task->save();
            }
        }
        // 清理老旧数据（状态完成或暂停的下次执行时间在一年前的任务）
        foreach (Task::find()
                     ->andWhere(['status' => [Task::STATUS_FINISHED, Task::STATUS_PAUSED]])
                     ->andWhere(['<=', 'next', time() - 365 * 24 * 60 * 60])
                     ->each() as $task) {
            try {
                $task->delete();
            } catch (\Throwable $t) {
            }
            Yii::info('Clear Task:' . $task->id);
        }
        Yii::info('Finish Task');
    }

    /**
     * 初始化定时任务
     */
    public function actionInit()
    {
        $this->stdout('删除旧任务：');
        $c = Task::deleteAll();
        $this->stdout($c);
        $this->stdout(PHP_EOL);

        //$this->_newTask('支付宝对账', '0 8 * * *', AlipayApi::class, 'task_bank_reconciliation', null);
//        $this->_newTask('微信App对账', '0 6 * * *', WeixinAppApi::class, 'task_bank_reconciliation', null);
//        $this->_newTask('微信公众号对账', '0 6 * * *', WeixinMpApi::class, 'task_bank_reconciliation', null);
        $this->_newTask('订单自动取消', '*/1 * * * *', Order::class, 'task_force_cancel', null);
        $this->_newTask('强制收货', '10 0 * * *', Order::class, 'task_force_receive', null);
        $this->_newTask('强制评论', '10 0 * * *', Order::class, 'task_force_comment', null);
        $this->_newTask('生成结算单', '10 0 * * *', Order::class, 'task_create_financial_settlement', null);
        $this->_newTask('申请退款退货强制同意', '10 0 * * *', OrderRefund::class, 'task_order_refund_force_accept', null);
        $this->_newTask('申请退款退货强制取消', '10 0 * * *', OrderRefund::class, 'task_order_refund_force_delete', null);
        $this->_newTask('申请退款退货强制收货', '10 0 * * *', OrderRefund::class, 'task_order_refund_force_receive', null);
        //$this->_newTask('计算商户结算金额', '10 0 * * *', Order::class, 'task_create_financial_settlement', null);
        $this->_newTask('计算会员佣金结算金额', '10 0 1 * *', Order::class, 'task_create_month_commission_log', null);
        $this->_newTask('生成结算单是否可以结算', '10 0 * * *', Order::class, 'task_calc_financial_settlement', null);
        $this->_newTask('购买升级卡套餐卡订单自动取消', '*/1 * * * *', UserBuyPack::class, 'task_force_cancel', null);
        $this->_newTask('礼包卡券自动过期', '*/1 * * * *', UserPackageCoupon::class, 'task_coupon_expired', null);
    }

    /**
     * 创建新任务
     * @param $name string 任务名称
     * @param $cron string 定时指令
     * @param $class string 类
     * @param $method string 方法
     * @param $params mixed 参数
     */
    private function _newTask($name, $cron, $class, $method, $params)
    {
        $this->stdout($name . '：');
        $task = new Task();
        $task->u_type = Task::U_TYPE_MANAGER;
        $task->uid = 1;
        $task->name = $name;
        $task->next = 0;
        $task->cron = $cron;
        $task->todo = json_encode([
            'class' => $class,
            'method' => $method,
            'params' => $params,
        ]);
        $task->status = Task::STATUS_WAITING;
        $r = $task->save();
        $this->stdout($r);
        $this->stdout(PHP_EOL);
    }
}
