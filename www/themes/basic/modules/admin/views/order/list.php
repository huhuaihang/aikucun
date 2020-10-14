<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\Goods;
use app\models\KeyMap;
use app\models\Order;
use app\models\Util;
use app\models\User;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $sum float
 * @var $model_list \app\models\Order[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);
$this->title = '订单列表';
if(!empty(Yii::$app->request->get('uid')))
{
  $user=User::findOne(Yii::$app->request->get('uid'));
  if (!empty($user))
  {
      $this->title = '用户【'.$user->real_name . '】订单列表';
  }

}

$this->params['breadcrumbs'][] = '订单管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_status" class="sr-only">订单状态</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('order_status'), ['prompt' => '订单状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">订单类型</label>
    <?php echo Html::dropDownList('search_order_type', Yii::$app->request->get('search_order_type'), KeyMap::getValues('order_type'), ['prompt' => '订单类型', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_no" class="sr-only">订单号</label>
    <?php echo Html::textInput('search_no', Yii::$app->request->get('search_no'), ['id' => 'search_no', 'class' => 'form-control', 'placeholder' => '订单号', 'style' => 'max-width:150px;']);?>
</div>
<div class="form-group">
    <label for="search_shop_name" class="sr-only">店铺</label>
    <?php echo Html::textInput('search_shop_name', Yii::$app->request->get('search_shop_name'), ['id' => 'search_shop_name', 'class' => 'form-control', 'placeholder' => '店铺', 'style' => 'max-width:150px;']);?>
</div>
<div class="form-group">
    <label for="search_pay_method" class="sr-only">支付方式</label>
    <?php echo Html::dropDownList('search_pay_method', Yii::$app->request->get('search_pay_method'), KeyMap::getValues('finance_log_pay_method'), ['prompt' => '支付方式', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_pay_status" class="sr-only">支付状态</label>
    <?php echo Html::dropDownList('search_pay_status', Yii::$app->request->get('search_pay_status'), KeyMap::getValues('finance_log_status'), ['prompt' => '支付状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_trade_no" class="sr-only">支付交易号</label>
    <?php echo Html::textInput('search_trade_no', Yii::$app->request->get('search_trade_no'), ['id' => 'search_trade_no', 'class' => 'form-control', 'placeholder' => '支付交易号', 'style' => 'max-width:150px;']);?>
</div>
<div class="form-group">
    <label for="search_deliver_info" class="sr-only">收货人信息</label>
    <?php echo Html::textInput('search_deliver_info', Yii::$app->request->get('search_deliver_info'), ['id' => 'search_deliver_info', 'class' => 'form-control', 'placeholder' => '收货人信息', 'style' => 'max-width:150px;']);?>
</div>
<div class="form-group">
    <label for="search_user_info" class="sr-only">下单人信息</label>
    <?php echo Html::textInput('search_user_info', Yii::$app->request->get('search_user_info'), ['id' => 'search_user_info', 'class' => 'form-control', 'placeholder' => '下单人手机号', 'style' => 'max-width:150px;']);?>
</div>
<div class="form-group">
    <label for="search_goods_name" class="sr-only">商品名称</label>
    <?php echo Html::textInput('search_goods_name', Yii::$app->request->get('search_goods_name'), ['id' => 'search_username', 'class' => 'form-control', 'placeholder' => '商品名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_start_date" class="sr-only">下单时间</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'placeholder' => '开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'placeholder' => '结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
</div>
<div class="form-group">
<?php  echo  Html::hiddenInput('uid', Yii::$app->request->get('uid')); ?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'excel']);?>" class="btn btn-info btn-sm">导出</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>订单号</th>
        <th>卖家</th>
        <th>买家</th>
        <th>产品</th>
        <th>自购省总金额</th>
        <th>总金额</th>
        <th>实收金额</th>
        <th>支付方式</th>
        <th>订单状态</th>
        <th>下单时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr class="data_<?php echo $model->no;?>">
            <td><?php echo $model->no;?></td>
            <td><?php echo Html::encode($model->shop->name);?></td>
            <td><?php echo Html::encode($model->user->nickname);?></td>
            <td>
                <table class="table">
                    <thead>
                    <tr>
                        <th>编号</th>
                        <th>图片</th>
                        <th>标题</th>
                        <th>规格</th>
                        <th>单价</th>
                        <th>数量</th>
                        <th>是否一件代发</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($model->itemList as $item) {?>
                        <tr>
                            <td><?php echo $item->gid;?></td>
                            <td><?php echo Html::img(Util::fileUrl($item->goods->main_pic, false, '_40x40'));?></td>
                            <td><?php if($item->mark_money>0){?><span style="color: #ff2222">【限时抢购】</span><?php }?>
                                <?php if($model->pack_coupon_status==1){?><span style="color: #ff2222">【卡券购买】</span><?php }?>
                                <?php if($model->pack_coupon_status==2){?><span style="color: #ff2222">【卡券兑换】</span><?php }?>
                                <?php  echo Html::encode($item->title);?></td>
                            <td><?php echo Html::encode($item->sku_key_name);?></td>
                            <td><?php echo $item->price;?></td>
                            <td><?php echo $item->amount;?></td>
                            <td><?php echo ($item->goods->sale_type == Goods::TYPE_SUPPLIER) ? $item->goods->supplier->name : '自营';?></td>
                        </tr>
                        <?php if(!empty($model->gift_id)){?>
                            <tr>
                                <td style="color: #ff2222">【赠品】</td>
                                <td><?php echo Html::img(Yii::$app->params['upload_url'] . $model->gift->thumb_pic, ['width' => 32]);?></td>
                                <td><?php echo Html::encode($model->gift->name);?></td>
                                <td></td>
                                <td><?php echo $model->gift->price;?></td>
                                <td>1</td>
                                <td></td>
                            </tr>
                            <?php }?>
                    <?php }?>
                    </tbody>
                </table>
            </td>
            <td><?php echo $model->self_buy_money;?></td>
            <td><?php echo $model->amount_money;?></td>
            <td><?php // TODO：商户实收金额，考虑优惠券 打折 积分抵扣等?>
            <?php if ($model->is_score == 1) {echo '积分兑换订单 <br> 消耗积分：' . $model->score;}?></td>
            <td><?php if (empty($model->fid)) {
                    echo '<i>没有支付信息</i>';
                } else {
                    echo KeyMap::getValue('finance_log_pay_method', $model->financeLog->pay_method), '<br />';
                    echo KeyMap::getValue('finance_log_status', $model->financeLog->status);
                }?>
            </td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('order_status', $model->status);?><?php if (!empty($model->cancel_fid) && $model->status == Order::STATUS_CANCEL) { echo " 已退款 ";}?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php
               echo ManagerTableOp::widget(['items' => [
                  $model->pack_coupon_status ==1 ? false :['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/order/view', 'order_no' => $model->no]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                ]]);

                ?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo $sum;?>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
