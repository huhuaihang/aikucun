<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Shop;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $queryList []
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '订单列表';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_shop_name" class="sr-only">类型</label>
    <?php echo Html::dropDownList('type', Yii::$app->request->get('type'), ['1' => '按日统计', '2' => '按月统计'], ['prompt' => '类型', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">提交时间</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'placeholder' => '开始日期', 'style' => 'max-width:120px;', 'class'=>'form-control']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'placeholder' => '结束日期', 'style' => 'max-width:120px;', 'class'=>'form-control']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>时间</th>
        <th>任务分组数</th>
        <th>做单数</th>
        <th>总金额</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($queryList as $order) {?>
        <tr class="data_<?php echo $order['date'];?>">
            <td><?php echo $order['date']; ?></td>
            <td><?php echo $order['group_num'];?></td>
            <td><?php echo $order['order_num'];?></td>
            <td><?php echo $order['pay_money'];?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
//                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/order/view', 'order_no' => $order->no]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script src="/js/laydate/laydate.js"></script>
<script>
    laydate.render({
        elem: '#search_start_date' //指定元素
    });
    laydate.render({
        elem: '#search_end_date' //指定元素
    });
</script>