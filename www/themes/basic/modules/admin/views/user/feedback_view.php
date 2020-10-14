<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\Feedback
 */

TableAsset::register($this);

$this->title = '反馈详情';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th width="100px">用户昵称</th>
        <td><?php echo Html::encode($model->user->nickname);?></td>
    </tr>
    <tr>
        <th>手机号</th>
        <td><?php echo Html::encode($model->user->user_phone);?></td>
    </tr>
    <tr>
        <th>反馈内容</th>
        <td><?php echo html::encode($model->content);?></td>
    </tr>
    <tr>
        <th>反馈状态</th>
        <td><?php echo KeyMap::getValue('feedback_status', $model->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
    </tr>
</table>
