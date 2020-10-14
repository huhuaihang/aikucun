<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Order;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use app\models\UserPackageCoupon;
use yii\helpers\Html;
use yii\helpers\Url;
use app\models\GoodsCouponGiftUser;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\UserPackageCoupon[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '用户礼包券列表';
$this->params['breadcrumbs'][] = '会员管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<!--<div class="form-group">-->
<!--    <label for="search_level_id" class="sr-only">会员等级</label>-->
<!--    --><?php //echo Html::dropDownList('search_level_id', Yii::$app->request->get('search_level_id'), KeyMap::getValues('user_level_id'), ['prompt' => '会员等级', 'class' => 'form-control']);?>
<!--</div>-->
<div class="form-group">
    <label for="search_id" class="sr-only">兑换券编号</label>
    <?php echo Html::textInput('search_coupon_id', Yii::$app->request->get('search_coupon_id'), ['id' => 'search_coupon_id', 'class' => 'form-control', 'placeholder' => '兑换券编号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_real_name" class="sr-only">真实姓名</label>
    <?php echo Html::textInput('search_real_name', Yii::$app->request->get('search_real_name'), ['id' => 'search_real_name', 'class' => 'form-control', 'placeholder' => '真实姓名', 'style' => 'max-width:100px;']);?>
</div>
<!--<div class="form-group">-->
<!--    <label for="search_nickname" class="sr-only">昵称</label>-->
<!--    --><?php //echo Html::textInput('search_nickname', Yii::$app->request->get('search_nickname'), ['id' => 'search_nickname', 'class' => 'form-control', 'placeholder' => '昵称', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<div class="form-group">
    <label for="search_mobile" class="sr-only">手机号</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('user_package_coupon_status'), ['prompt' => '兑换券状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">领取时间</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'placeholder' => '领取开始日期', 'style' => 'max-width:100px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'placeholder' => '领取结束日期', 'style' => 'max-width:100px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
<!--    <a href="--><?php //echo Url::current(['export' => 'excel']);?><!--" class="btn btn-info btn-sm">导出</a>-->
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>兑换券编号</th>
        <th>用户手机</th>
        <th>真实姓名</th>
        <th>领取时间</th>
        <th>来源</th>
        <th>状态</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr >
            <td><?php echo $model->id;?></td>
            <td><?php echo $model->user->mobile;?></td>
            <td><?php echo $model->user->real_name;?></td>
            <td><?php echo date('Y-m-d H:i',$model->create_time);?></td>
            <td><?php echo empty($model->oid)?'后台发放':Html::a('查看订单', '/admin/order/list?search_no='.$model->order->no);?></td>
            <td><?php echo Html::a(KeyMap::getValue('user_package_coupon_status', $model->status), 'javascript:void(0)', ['onclick'=>'activate(' . $model->id . ')', 'class'=>[UserPackageCoupon::STATUS_OK=>'label label-success', UserPackageCoupon::STATUS_HIDE=>'label label-warning',UserPackageCoupon::STATUS_USED=>'label label-info'][$model->status]]);?>
                <?php echo ManagerTableOp::widget(['items' => [
                   ($model->status == UserPackageCoupon::STATUS_USED)? ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/order/order-pack-coupon-view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '订单详情']:'',

                ]]);?>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    function  activate() {

    }
    
</script>