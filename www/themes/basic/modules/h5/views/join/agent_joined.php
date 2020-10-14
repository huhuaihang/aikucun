<?php

use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $agent \app\models\Agent
 */

$this->title = '代理商在线申请';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">代理商在线申请</div>
    </header>
    <div class="container">
        <!--设置密码-->
        <div class="b_oparea b_magt">
            <div class="b_waiting">
                <img src="/images/b_shenhe_03.png"/>
            </div>
            <p class="p1">审核已通过</p>
            <p class="p2"></p>
            <a class="b_confrim" href="<?php echo Url::to(['/agent']);?>">登录代理后台</a>
        </div>
    </div>
</div>
