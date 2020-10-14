<?php

/**
 * @var $this \yii\web\View
 */

$this->title = '问卷调查';
?>
<?php if (empty(Yii::$app->request->get('app'))) {?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">问卷调查</div>
    </header>
    <div class="container">
        <p class="b_sys_mcont">暂无内容 ~ </p>
        <div class="b_sys_maintain1">
            <img src="/images/questionaire_03.png"/>
        </div>
    </div>
</div>
<?php } else {?>
    <div class="container">
        <p class="b_sys_mcont">暂无内容 ~ </p>
        <div class="b_sys_maintain1">
            <img src="/images/questionaire_03.png"/>
        </div>
    </div>
<?php }?>
