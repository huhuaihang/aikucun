<?php

/**
 * @var $this \yii\web\View
 */

use yii\helpers\Url;

$this->title = '我的代理';
?>
<div class="box">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user']);?>'" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">我的代理</a>
    </div><!--new_header-->
    <div class="agent">
        <ul>
            <li>
                <a href="<?php echo Url::to(['/h5/user/recommend-list']);?>">
                    <p>我的推荐</p>
                    <img src="/images/tixian5.png">
                </a>
            </li>
            <li>
                <a href="<?php echo Url::to(['/h5/user/commission-list']);?>">
                    <p>我的收益</p>
                    <img src="/images/tixian5.png">
                </a>
            </li>
            <li>
                <a href="<?php echo Url::to(['/h5/user/recharge-values']);?>">
                    <p>我要进货</p>
                    <img src="/images/tixian5.png">
                </a>
            </li>
        </ul>
    </div>
</div>
