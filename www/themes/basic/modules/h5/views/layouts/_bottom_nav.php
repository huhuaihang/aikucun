<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
?>
<div class="Y_bottom">
    <a href="<?php echo Url::to(['/h5']);?>">
        <dl>
            <?php if (Yii::$app->controller->id == 'default') {?>
                <dt><img src="/images/bottom_05.png"></dt>
                <dd class="color">主页</dd>
            <?php } else {?>
                <dt><img src="/images/bottom_051.png"></dt>
                <dd>主页</dd>
            <?php }?>
        </dl>
    </a>
    <a href="<?php echo Url::to(['/h5/cart']);?>">
        <dl>
            <?php if (Yii::$app->controller->id == 'cart') {?>
                <dt><img src="/images/bottom_07.png"></dt>
                <dd class="color">购物车</dd>
            <?php } else {?>
                <dt><img src="/images/bottom_071.png"></dt>
                <dd>购物车</dd>
            <?php }?>
        </dl>
    </a>
    <a href="<?php echo Url::to(['/h5/user/vip']);?>">
        <dl>
            <?php if (Yii::$app->controller->action->id == 'vip') {?>
                <dt><img src="/images/vip.png"></dt>
                <dd class="color">VIP</dd>
            <?php } else {?>
                <dt><img src="/images/vip1.png"></dt>
                <dd>VIP</dd>
            <?php }?>
        </dl>
    </a>
    <a href="<?php echo Url::to(['/h5/user']);?>">
        <dl class="dl4">
            <?php if (Yii::$app->controller->id == 'user' && Yii::$app->controller->action->id!='vip') {?>
                <dt>
                    <span id="new_msg_info" style="display: none;"></span>
                    <img src="/images/bottom_09.png">
                </dt>
                <dd class="color">我的</dd>
            <?php } else {?>
                <dt>
                    <span id="new_msg_info" style="display: none;"></span>
                    <img src="/images/bottom_091.png">
                </dt>
                <dd>我</dd>
            <?php }?>
        </dl>
    </a>
</div><!--Y_bottom-->
<?php if (!Yii::$app->user->isGuest) {?>
    <script>
        <?php $this->registerJs('check_new_msg();');?>
        function check_new_msg() {
            apiGet('<?php echo Url::to(['/api/user/check-new-message']);?>', {}, function (json) {
                if (callback(json)) {
                    if (json['have_new_msg']) {
                        $('#new_msg_info').show();
                    }
                    window.setTimeout(function () {check_new_msg();}, 60000);
                }
            });
        }
    </script>
<?php }?>
