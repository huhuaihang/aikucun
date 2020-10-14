<?php

use app\models\KeyMap;
use app\models\Manager;
use app\models\Task;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $error app\models\SystemError
 */

$this->title = '错误详情';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th>编号</th>
        <td><?php echo $error->id;?></td>
    </tr>
    <tr>
        <th>时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($error->time);?></td>
    </tr>
    <tr>
        <th>内容</th>
        <td><?php echo Html::encode($error->message);?></td>
    </tr>
    <tr>
        <th>代码</th>
        <td><?php echo Html::encode($error->code);?></td>
    </tr>
    <tr>
        <th>文件</th>
        <td><?php echo $error->file;?></td>
    </tr>
    <tr>
        <th>行号</th>
        <td><?php echo $error->line;?></td>
    </tr>
    <tr>
        <th>追踪信息</th>
        <td><pre><?php echo Html::encode($error->trace);?></pre></td>
    </tr>
    <tr>
        <th>环境信息</th>
        <td><pre><?php print_r(json_decode($error->context, true));?></pre></td>
    </tr>
</table>
