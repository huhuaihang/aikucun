<?php

use app\models\System;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $type string
 */


$this->title = '合作协议';
?>
<div class="box">
    <?php
    if ($type == 'merchant') {
        $system_type = 'merchant_join_agreement';
    } else if ($type == 'merchant-person') {
        $system_type = 'merchant_join_agreement';
    } else if ($type == 'agent') {
        $system_type = 'agent_join_agreement';
    }
    ?>
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title"><?php $name =  System::find()->select(['show_name'])->where(['name' => $system_type])->one(); echo $name['show_name'];?></div>
    </header>
    <div class="container">
        <div class="b_gipl_cont">
            <?php echo System::getConfig($system_type);?>
        </div>
        <div class="b_getin_btn clearfix">
            <a class="b_gibtn1" href="<?php echo Url::to('/h5/join/');?>">返回</a>
            <a class="b_gibtn2" href="<?php echo Url::to('/h5/join/'.$type)?>">同意</a>
        </div>
    </div>
</div>
