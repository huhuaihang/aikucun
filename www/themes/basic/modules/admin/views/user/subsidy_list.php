<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\System;
use app\models\UserSubsidy;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $list []
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '补贴记录';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>可提现补贴</th>
        <td><?php echo $user->subsidy_money;?></td>
    </tr>
    <tr>
        <th>历史补贴总额</th>
        <td><?php echo $sum;?></td>
    </tr>
</table>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
<!--        <th>客户端</th>-->
        <th>时间</th>
        <th>金额</th>
        <th>触发者</th>
        <th>接收者</th>
        <th>备注</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php
    /** @var UserSubsidy $item */
    foreach ($list as $item) {?>
        <tr id="data_<?php echo $item['id'];?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $item['id'];?>"/>
                    <span class="lbl"><?php echo $item['id'];?></span>
                </label>
            </td>
<!--            <td>--><?php //echo $item['app_id'];?><!--</td>-->
            <td><?php echo Yii::$app->formatter->asDatetime($item['create_time']);?></td>
            <td><?php echo $item['money'];?></td>
            <td><?php echo $item->fromUser->real_name . ' ' . $item->fromUser->mobile;?></td>
            <td><?php echo $item->toUser->real_name;?></td>
            <td><?php echo Html::encode($item['type']) , KeyMap::getValue('user_subsidy_type', $item['type']);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/user/subsidy-edit', 'id' => $item->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '编辑', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
