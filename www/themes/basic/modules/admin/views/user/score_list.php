<?php

use app\assets\TableAsset;
use app\widgets\LinkPager;


/**
 * @var $this \yii\web\View
 * @var $list []
 * @var $user \app\models\User
 * @var $used_score integer 已使用积分
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '积分记录';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>剩余积分</th>
        <td><?php echo $user->account->score;?></td>
    </tr>
    <tr>
        <th>已使用积分</th>
        <td><?php echo $used_score;?></td>
    </tr>
</table>
<table class="table table-striped table-bordered table-hover" >
    <thead >
    <tr >
<!--        <th>客户端</th>-->
        <th style="text-align: center!important;" >积分数量</th>
        <th style="text-align: center!important;" >积分来源</th>
        <th style="text-align: center!important;" >时间</th>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($list as $item) {?>
        <tr align="center" >
<!--            <td>--><?php //echo $item['app_id'];?><!--</td>-->

            <td><?php echo $item['score'];?></td>
            <td><?php echo  $item['remark'];?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($item['time']);?></td>

        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
