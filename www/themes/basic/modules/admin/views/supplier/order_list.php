<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Util;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $orderList \app\models\Order[]
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '订单列表';
$this->params['breadcrumbs'][] = '供货商管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>订单号</th>
        <th>卖家</th>
        <th>买家</th>
        <th>产品</th>
        <th>下单时间</th>
        <th>订单状态</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($orderList as $order) {?>
        <tr class="data_<?php echo $order->no;?>">
            <td><?php echo $order->no;
//                if (!empty($order->pid)) {
//                    echo '<br />上级订单号：<br />', $order->parent->no;
//                }
                ?>
            </td>
            <td><?php echo empty($order->sid) ? '<i>组合订单</i>' : Html::encode($order->shop->name);?></td>
            <td><?php echo Html::encode($order->user->nickname);?></td>
            <td>
<!--                --><?php //if ($order->is_combine == 1) {
//                    foreach ($order->childList as $childOrder) {?>
<!--                        <table class="table">-->
<!--                            <thead>-->
<!--                            <tr>-->
<!--                                <th colspan="7">-->
<!--                                    --><?php //echo Html::a($childOrder->no, ['/admin/order/view', 'order_no' => $childOrder->no]);?>
<!--                                    <span class="label label-default arrowed-in-right arrowed">--><?php //echo KeyMap::getValue('order_status', $childOrder->status);?><!--</span>-->
<!--                                </th>-->
<!--                            </tr>-->
<!--                            <tr>-->
<!--                                <th>编号</th>-->
<!--                                <th>图片</th>-->
<!--                                <th>标题</th>-->
<!--                                <th>规格</th>-->
<!--                                <th>供货商</th>-->
<!--                                <th>单价</th>-->
<!--                                <th>优惠价</th>-->
<!--                                <th>供货价</th>-->
<!--                                <th>数量</th>-->
<!--                            </tr>-->
<!--                            </thead>-->
<!--                            <tbody>-->
<!--                            --><?php //foreach ($childOrder->itemList as $item) {
//                                if (empty($item->goods->supplier_id)) {continue;}?>
<!--                                <tr>-->
<!--                                    <td>--><?php //echo $item->gid;?><!--</td>-->
<!--                                    <td>--><?php //echo Html::img(Util::fileUrl($item->goods->main_pic, false, '_32x32'));?><!--</td>-->
<!--                                    <td>--><?php //echo Html::encode($item->title);?><!--</td>-->
<!--                                    <td>--><?php //echo Html::encode($item->sku_key_name);?><!--</td>-->
<!--                                    <td>--><?php //echo Html::encode($item->goods->supplier->name);?><!--</td>-->
<!--                                    <td>--><?php //echo $item->price;?><!--</td>-->
<!--                                    <td>--><?php //echo $item->d_price;?><!--</td>-->
<!--                                    <td>--><?php //echo $item->goodsSku->supplier_price;?><!--</td>-->
<!--                                    <td>--><?php //echo $item->amount;?><!--</td>-->
<!--                                </tr>-->
<!--                            --><?php //}?>
<!--                            </tbody>-->
<!--                        </table>-->
<!--                    --><?php //}
//                } else {?>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>编号</th>
                            <th>图片</th>
                            <th>标题</th>
                            <th>规格</th>
                            <th>供货商</th>
                            <th>单价</th>
<!--                            <th>优惠价</th>-->
                            <th>供货价</th>
                            <th>数量</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($order->itemList as $item) {
                            if (empty($item->goods->supplier_id)) {continue;}?>
                            <tr>
                                <td><?php echo $item->gid;?></td>
                                <td><?php echo Html::img(Util::fileUrl($item->goods->main_pic, false, '_32x32'));?></td>
                                <td><?php echo Html::encode($item->title);?></td>
                                <td><?php echo Html::encode($item->sku_key_name);?></td>
                                <td><?php echo Html::encode($item->goods->supplier->name);?></td>
                                <td><?php echo $item->price;?></td>
<!--                                <td>--><?php //echo $item->d_price;?><!--</td>-->
                                <td><?php echo empty($item->goodsSku) ? '' : $item->goodsSku->supplier_price;?></td>
                                <td><?php echo $item->amount;?></td>
                            </tr>
                        <?php }?>
                        </tbody>
                    </table>
<!--                --><?php //}?>
            </td>
            <td><?php echo Yii::$app->formatter->asDatetime($order->create_time);?></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo KeyMap::getValue('order_status', $order->status);?></span></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/order/view', 'order_no' => $order->no]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
