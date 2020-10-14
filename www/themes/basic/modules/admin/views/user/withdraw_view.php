<?php

use app\models\City;
use app\models\KeyMap;
use app\models\UserAddress;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model \app\models\UserWithdraw
 */

$this->title = '用户提现详情';
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
        <th>申请人</th>
        <td><?php echo Html::encode($model->user->nickname);?></td>
    </tr>
    <tr>
        <th>金额</th>
        <td><?php echo $model->money;?></td>
    </tr>
    <tr>
        <th>银行名称</th>
        <td><?php echo Html::encode($model->bank_name);?></td>
    </tr>
    <tr>
        <th>开户行地址</th>
        <td><?php echo Html::encode($model->bank_address);?></td>
    </tr>
    <tr>
        <th>账户名</th>
        <td><?php echo Html::encode($model->account_name);?></td>
    </tr>
    <tr>
        <th>账号</th>
        <td><?php echo Html::encode($model->account_no);?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('user_withdraw_status', $model->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
    </tr>
    <tr>
        <th>通过时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($model->apply_time);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($model->remark);?></td>
    </tr>
</table>
