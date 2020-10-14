<?php

use app\models\City;
use app\models\KeyMap;
use app\models\UserAddress;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\UserCardLevel
 */

$this->title = '用户会员卡等级详情';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $model->id;?></td>
    </tr>
    <tr>
        <th>等级名称</th>
        <td><?php echo Html::encode($model->name);?></td>
    </tr>
    <tr>
        <th>权益说明</th>
        <td><?php echo Html::encode($model->remark);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
    </tr>

</table>
