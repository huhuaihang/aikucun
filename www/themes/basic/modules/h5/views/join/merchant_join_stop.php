<?php

/**
 * @var $this \yii\web\View
 */

use yii\helpers\Url;

$this->title = '商家被停止';
?>
<div class="box">
    <div class="about_head">
        <a href="<?php echo Url::to(['/h5/join']);?>"><p class="p1"><img src="/images/11_1.png"></p></a>
        <p class="p2">停止中</p>
    </div><!--about_head-->
    <div class="aduit_succeed">
        <dl>
            <dt><img src="/images/aduit.png"></dt>
            <dd class="dd1">店铺被停止</dd>
            <dd class="dd2">请联系客服</dd>
        </dl>
        <div class="aduit_succeed_bot"><a href="<?php echo Url::to(['/h5/join']);?>">确定</a></div>
    </div>
</div>
