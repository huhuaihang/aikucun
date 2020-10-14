<?php

use app\models\System;

/**
 * @var $this \yii\web\View
 */

$this->title = '客服中心';
?>
<style>
    body{ background-color: #f4f4f4;}
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">客服中心</div>
    </header>
    <div class="container">
        <!--客服中心-->
        <div class="">
            <?php echo System::getConfig('site_contact');?>
        </div>
    </div>
</div>
