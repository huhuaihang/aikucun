<?php

use app\models\KeyMap;
use app\models\Manager;
use app\models\Task;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $task app\models\Task
 */

$this->title = '定时任务';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th>编号</th>
        <td><?php echo $task->id;?></td>
    </tr>
    <tr>
        <th>用户</th>
        <td><?php echo KeyMap::getValue('task_u_type', $task->u_type);?>：
            <?php switch ($task->u_type) {
                case Task::U_TYPE_MANAGER:
                    $manager = Manager::findOne($task->uid);
                    echo Html::encode($manager->nickname);
                    break;
            }?>
        </td>
    </tr>
    <tr>
        <th>任务名称</th>
        <td><?php echo Html::encode($task->name);?></td>
    </tr>
    <tr>
        <th>下次执行时间</th>
        <td><?php if ($task->next > 0) {echo Yii::$app->formatter->asDatetime($task->next);}?></td>
    </tr>
    <tr>
        <th>定时</th>
        <td><code title="分 时 日 月 周"><?php echo $task->cron;?></code></td>
    </tr>
    <tr>
        <th>任务</th>
        <td><pre style="max-width:1000px;"><?php print_r(json_decode($task->todo, true));?></pre></td>
    </tr>
    <tr>
        <th>上次执行结果</th>
        <td><pre><?php print_r(json_decode($task->result, true));?></pre></td>
    </tr>
    <tr>
        <th>任务状态</th>
        <td><?php echo KeyMap::getValue('task_status', $task->status);?></td>
    </tr>
</table>
