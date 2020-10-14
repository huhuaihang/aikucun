<?php

use app\models\KeyMap;
use app\models\OrderRefund;
use yii\helpers\Url;


/**
 * @var $this \yii\web\View
 * @var $model \app\models\OrderRefund
 */

$this->title = "退款进度";
?>

<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/order/refund']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">退款进度</div>
    </header>
    <div class="container">
        <!--退款信息-->
        <ul class="b_appeal_cash b_magt1">
            <li class="ubb"><p class="p1">退款金额</p><p class="p2"><?php echo $model->money;?>元</p></li>
            <li class="ubb"><p class="p1">退回账户</p><p>原路退回</p></li>
            <li><p class="p1">退款进度</p><p class="p2"><?php echo KeyMap::getValue('order_refund_status', $model->status)?></p></li>
        </ul>
        <!--退款进度-->
        <ul class="b_refund_box b_magt1">
            <li>
                <div class="b_pro_icon">
                    <img src="/images/progress1_icon_03.png"/>
                </div>
                <p class="b_pro_status">买家申请等待卖家同意</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></p>
            </li>
            <?php if ($model->status == OrderRefund::STATUS_REJECT) {?>
            <li class="b_pro_noborder">
                <div class="b_pro_icon">
                    <img src="/images/progress1_icon_03.png"/>
                </div>
                <p class="b_pro_status">卖家拒绝</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->reject_time);?></p>
            </li>
            <?php } else { ?>
            <li>
                <div class="b_pro_icon">
                    <img src="<?php echo ($model->status >= OrderRefund::STATUS_ACCEPT)? '/images/progress1_icon_03.png' : '/images/progress_icon_03.png'; ?>"/>
                </div>
                <p class="b_pro_status">卖家同意等待买家发货</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->apply_time);?></p>
            </li>
            <li>
                <div class="b_pro_icon">
                    <img src="<?php echo ($model->status >= OrderRefund::STATUS_SEND)? '/images/progress1_icon_03.png' : '/images/progress_icon_03.png'; ?>"/>
                </div>
                <p class="b_pro_status">买家已发货等待卖家收货</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->send_time);?></p>
            </li>
            <li>
                <div class="b_pro_icon">
                    <img src="<?php echo ($model->status >= OrderRefund::STATUS_RECEIVE)? '/images/progress1_icon_03.png' : '/images/progress_icon_03.png'; ?>"/>
                </div>
                <p class="b_pro_status">卖家已收货等待退款</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->receive_time);?></p>
            </li>
            <li class="b_pro_noborder">
                <div class="b_pro_icon">
                    <img src="<?php echo ($model->status >= OrderRefund::STATUS_COMPLETE)? '/images/progress1_icon_03.png' : '/images/progress_icon_03.png'; ?>"/>
                </div>
                <p class="b_pro_status">退款成功售后完成</p>
                <p class="b_pro_time"><?php echo Yii::$app->formatter->asDatetime($model->reject_time);?></p>
            </li>
            <?php }?>
        </ul>
    </div>
</div>
