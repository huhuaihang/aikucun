<?php

use app\models\DiscountGoods;
use app\models\KeyMap;
use app\models\Util;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $discount \app\models\Discount
 */

$this->title = '减折价详情';
$this->params['breadcrumbs'][] = '营销管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th>编号</th>
        <td><?php echo $discount->id;?></td>
    </tr>
    <tr>
        <th>名称</th>
        <td><?php echo Html::encode($discount->name);?></td>
    </tr>
    <tr>
        <th>开始时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($discount->start_time);?></td>
    </tr>
    <tr>
        <th>结束时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($discount->end_time);?></td>
    </tr>
<!--    <tr>-->
<!--        <th>商品标志</th>-->
<!--        <td>文字：<br /><span class="label label-lg label-pink">--><?php //echo Html::encode($discount->goods_flag_txt);?><!--</span><br />-->
<!--            图标：<br />--><?php //echo Html::img(Util::fileUrl($discount->goods_flag_img));?><!--</td>-->
<!--    </tr>-->
<!--    <tr>-->
<!--        <th>限购数量</th>-->
<!--        <td>--><?php //echo $discount->buy_limit;?><!--</td>-->
<!--    </tr>-->
    <tr>
        <th>商品</th>
        <td>
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th>编号</th>
                    <th>商品名称</th>
                    <th>减折价</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($discount->discountGoodsList as $discountGoods) {?>
                    <tr>
                        <td><?php echo $discountGoods->goods->id;?></td>
                        <td><?php echo Html::a(Html::img(Util::fileUrl($discountGoods->goods->main_pic, false, '_32x32')) . Html::encode($discountGoods->goods->title), Yii::$app->params['site_host'] . '/h5/goods/view?id=' . $discountGoods->gid);?></td>
                        <td><?php echo $discountGoods->type == DiscountGoods::TYPE_PRICE ? '减价' . $discountGoods->price . '元' : '打' . $discountGoods->ratio . '折';?></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('discount_status', $discount->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($discount->create_time);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($discount->remark);?></td>
    </tr>
</table>
