<?php

use app\models\City;
use app\models\KeyMap;
use app\models\UserAddress;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\UserLevel
 */

$this->title = '销售员等级详情';
$this->params['breadcrumbs'][] = '销售员管理';
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
<!--    <tr>-->
<!--        <th>等级金额</th>-->
<!--        <td>--><?php //echo $model->money;?><!--</td>-->
<!--    </tr>-->
    <tr>
        <th>权益说明</th>
        <td><?php echo Html::encode($model->description);?></td>
    </tr>
    <tr>
        <th>一级比率</th>
        <td><?php echo Html::encode($model->commission_ratio_1);?></td>
    </tr>
    <tr>
        <th>二级比率</th>
        <td><?php echo Html::encode($model->commission_ratio_2);?></td>
    </tr>
<!--    <tr>-->
<!--        <th>三级返佣比率</th>-->
<!--        <td>--><?php //echo Html::encode($model->commission_ratio_3);?><!--</td>-->
<!--    </tr>-->
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('user_level_status', $model->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
    </tr>

</table>
