<?php

/**
 * @var $this \yii\web\View
 * @var $agreement \app\models\System
 */


use yii\helpers\Url;

$this->title = '协议及声明';
?>

<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">协议及声明</div>
    </header>
    <div class="container">
        <div class="return">
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'merchant_join_agreement']) : Url::to(['/h5/about/agreement', 'name' => 'merchant_join_agreement', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">平台商家入驻合作协议2017</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'merchant_join_aptitude']) :  Url::to(['/h5/about/agreement', 'name' => 'merchant_join_aptitude', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">平台各店铺类型入驻资质要求</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'merchant_earnest_standard']) : Url::to(['/h5/about/agreement', 'name' => 'merchant_earnest_standard', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">平台保证金管理规范</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'goods_category_money_table']) : Url::to(['/h5/about/agreement', 'name' => 'goods_category_money_table', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">2017年度开放平台各类目资费一览表</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'merchant_operate_rules_standard']) : Url::to(['/h5/about/agreement', 'name' => 'merchant_operate_rules_standard', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">店铺运营相关规则及规范</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
            <div class="clear"></div>
            <div class="div1">
                <a href="<?php echo empty(Yii::$app->request->get('app')) ? Url::to(['/h5/about/agreement', 'name' => 'canvass_business_orders_standard']) : Url::to(['/h5/about/agreement', 'name' => 'canvass_business_orders_standard', 'app' => 1]);?>">
                    <p class="left">
                        <span class="span2">平台招商标准</span>
                    </p>
                    <p class="right">
                        <span class="span2"><img src="/images/SC_05.jpg"></span>
                    </p>
                </a>
            </div><!--div1-->
        </div><!--return-->
    </div>
</div>
<script>
    function page_init(){
        <?php if (!empty(Yii::$app->request->get('app'))) {?>
        $('.mall-header').hide();
        $('.container').css('margin-top','0');
        <?php }?>
    }
</script>
