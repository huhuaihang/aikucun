<?php

use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

$this->title = '商家入驻申请';
?>
<div class="box">
    <div class="about_head">
        <a href="<?php echo Url::to(['/h5/join']);?>"><p class="p1"><img src="/images/11_1.png"></p></a>
        <p class="p2">审核中</p>
    </div><!--about_head-->
    <div class="aduit_succeed">
        <dl>
            <dt><img src="/images/aduit.png"></dt>
            <dd class="dd1">审核中</dd>
            <dd class="dd2">审核结果会以短信的方式通知您</dd>
        </dl>
        <div class="aduit_succeed_bot"><a href="<?php echo Url::to(['/h5/join']);?>">确定</a></div>
    </div>
</div>
