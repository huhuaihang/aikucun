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
        <p class="p2">审核通过</p>
    </div><!--about_head-->
    <div class="aduit_succeed">
        <dl>
            <dt><img src="/images/aduit_succeed1.png"></dt>
            <dd class="dd1">审核通过</dd>
            <dd class="dd2">请前往<?php echo Url::to(['/merchant'], true);?>登录查看</dd>
        </dl>
        <div class="aduit_succeed_bot"><a href="#">确定</a></div>
    </div>
</div>
