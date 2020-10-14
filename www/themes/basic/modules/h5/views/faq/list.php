<?php

use app\models\System;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $category_list \app\models\FaqCategory[]
 * @var $faq_list \app\models\Faq[]
 */

$this->title = '常见问题';
?>
<?php if (empty(Yii::$app->request->get('app'))) {?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">常见问题</div>
    </header>
    <div class="container">
        <div class="cj_pro">
            <div class="text">
                <?php foreach ($category_list as $category) {?>
                    <div class="div1">
                        <a href="<?php echo Url::to(['/h5/faq/list', 'search_cid' => $category->id]);?>">
                            <p class="left">
                                <span class="span2"><?php echo Html::encode($category->name);?></span>
                            </p>
                            <p class="right">
                                <span class="span2"><img src="/images/SC_05.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                <?php }?>
                <?php foreach ($faq_list as $faq) {?>
                    <div class="div1">
                        <a href="<?php echo Url::to(['/h5/faq/view', 'id' => $faq->id]);?>">
                            <p class="left">
                                <span class="span2"><?php echo Html::encode($faq->title);?></span>
                            </p>
                            <p class="right">
                                <span class="span2"><img src="/images/SC_05.jpg"></span>
                            </p>
                        </a>
                    </div><!--div1-->
                <?php }?>
            </div><!--text-->
        </div><!--cjpro-->
        <div class="tell"><p><a href="tel:<?php echo System::getConfig('site_service_tel');?>"><img src="/images/23.jpg">联系客服</a></p></div>
    </div>
</div><!--box-->
<?php } else {?>
    <div class="cj_pro">
        <div class="text">
            <?php foreach ($category_list as $category) {?>
                <div class="div1">
                    <a href="<?php echo Url::to(['/h5/faq/list', 'search_cid' => $category->id]);?>">
                        <p class="left">
                            <span class="span2"><?php echo Html::encode($category->name);?></span>
                        </p>
                        <p class="right">
                            <span class="span2"><img src="/images/SC_05.jpg"></span>
                        </p>
                    </a>
                </div><!--div1-->
            <?php }?>
            <?php foreach ($faq_list as $faq) {?>
                <div class="div1">
                    <a href="<?php echo Url::to(['/h5/faq/view', 'id' => $faq->id]);?>">
                        <p class="left">
                            <span class="span2"><?php echo Html::encode($faq->title);?></span>
                        </p>
                        <p class="right">
                            <span class="span2"><img src="/images/SC_05.jpg"></span>
                        </p>
                    </a>
                </div><!--div1-->
            <?php }?>
        </div><!--text-->
    </div><!--cjpro-->
<?php }?>
