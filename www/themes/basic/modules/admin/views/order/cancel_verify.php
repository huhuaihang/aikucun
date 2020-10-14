<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\City;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Order[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '订单取消审核';
$this->params['breadcrumbs'][] = '订单管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl">订单号</span>
            </label>
        </th>
        <th>下单时间</th>
        <th>用户</th>
        <th>收货人</th>
        <th>总金额</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->no;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->no;?>"/>
                    <span class="lbl"><?php echo $model->no;?></span>
                </label>
            </td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo Html::encode($model->user->nickname);?></td>
            <td><?php if (!empty($model->deliver_info)) {
                    echo Html::encode($model->getDeliverInfoJson('name')), '<br />';
                    echo $model->getDeliverInfoJson('mobile'), '<br />';
                    $city = City::findByCode($model->getDeliverInfoJson('area'));
                    echo Html::encode(implode(' ', $city->address()));
                }?>
            </td>
            <td><?php echo $model->amount_money;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/order/view', 'order_no' => $model->no]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    ['icon' => 'fa fa-check', 'onclick' => 'acceptCancel(\'' . $model->no . '\')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '确认取消并退款', 'color' => 'yellow'],
                    ['icon' => 'fa fa-times', 'onclick' => 'rejectCancel(\'' . $model->no . '\')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '拒绝取消并设置为待配货', 'color' => 'yellow'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    function acceptCancel(order_no) {
        if (!confirm('确认取消申请并退款吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/order/accept-cancel']);?>', {'order_no':order_no}, function (json) {
            if (callback(json)) {
                $('#data_' + order_no).remove();
            }
        });
    }
    function rejectCancel(order_no) {
        if (!confirm('确认拒绝申请并设置为待配货吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/order/reject-cancel']);?>', {'order_no':order_no}, function (json) {
            if (callback(json)) {
                $('#data_' + order_no).remove();
            }
        });
    }
</script>
