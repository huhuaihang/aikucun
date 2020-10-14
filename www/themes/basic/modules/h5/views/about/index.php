<?php

use app\models\System;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

$this->title = '关于';
?>
<?php if (empty(Yii::$app->request->get('app'))) {?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">关于<?php echo System::getConfig('site_name');?></div>
    </header>
    <div class="container">
        <div class="about_ymm">
                <div class="div1 liebiao">
                    <a href="<?php echo Url::to(['/h5/feedback/edit']);?>">
                        <span class="span1"><span><img src="/images/about_06.png"></span><b>提意见</b></span>
                        <span class="span2"><img src="/images/about_10.jpg"></span>
                    </a>
                </div><!--div1-->
                <div class="clear"></div>
            <div class="div1 liebiao">
                <a href="<?php echo Url::to(['/h5/survey']);?>">
                    <span class="span1"><span><img src="/images/about_08.png"></span><b>问卷调查</b></span>
                    <span class="span2"><img src="/images/about_10.jpg"></span>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1 liebiao">
                <a href="<?php echo Url::to(['/h5/about/agreement-list']);?>">
                    <span class="span1"><span><img src="/images/about_14.png"></span><b>协议及声明</b></span>
                    <span class="span2"><img src="/images/about_10.jpg"></span>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <!--
            <div class="div1 liebiao">
                <a href="<?php echo Url::to(['/h5/faq/list']);?>">
                    <span class="span1"><span><img src="/images/WD_13.jpg"></span><b>常见问题</b></span>
                    <span class="span2"><img src="/images/about_10.jpg"></span>
                </a>
            </div>
            <div class="clear"></div>
            -->
        </div><!--about_ymm-->
    </div>
</div>
<?php } else {?>
    <div class="about_ymm">
        <div class="div1 liebiao">
            <a href="<?php echo Url::to(['/h5/survey?app=1']);?>">
                <span class="span1"><span><img src="/images/about_08.png"></span><b>问卷调查</b></span>
                <span class="span2"><img src="/images/about_10.jpg"></span>
            </a>
        </div><!--div1-->
        <div class="clear"></div>
        <div class="div1 liebiao">
            <a href="<?php echo Url::to(['/h5/about/agreement-list?app=1']);?>">
                <span class="span1"><span><img src="/images/about_14.png"></span><b>协议及声明</b></span>
                <span class="span2"><img src="/images/about_10.jpg"></span>
            </a>
        </div><!--div1-->
<!--        <div class="clear"></div>-->
<!--        <div class="div1 liebiao">-->
<!--            <a href="--><?php //echo Url::to(['/h5/faq/list']);?><!--">-->
<!--                <span class="span1"><span><img src="/images/WD_13.jpg"></span><b>常见问题</b></span>-->
<!--                <span class="span2"><img src="/images/about_10.jpg"></span>-->
<!--            </a>-->
<!--        </div>-->
<!--        <div class="clear"></div>-->
    </div><!--about_ymm-->
<?php }?>
