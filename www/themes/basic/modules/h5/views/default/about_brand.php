<?php

/**
 * @var $this \yii\web\View
 */

use yii\helpers\Url;

$this->title = '品牌文化';
?>
<div class="box">
    <div class="new_header">
        <a href="<?php echo Url::to(['/h5']);?>" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">品牌文化</a>
    </div><!--new_header-->
    <div class="culture_banner"><img src="/images/new_login_banner.png"></div>
    <div class="culture_text">
        <dl>
            <dt><img src="/images/new_login_banner.png"></dt>
            <dd>东润超市</dd>
        </dl>
        <div class="div1">
            东润超市即超级市场,一般是指商品开放陈列,顾客自我服务,货款一次结算,以经营生鲜食品、日杂用品为主的商店超级市场是一种消费者自我服务、敞开式的自选售货的零售企业。它是二次大战后发展起来，最先在欧美兴起，现在在欧美十几个国家中己有超级市场20万个。
        </div>
        <div class="div1">
            超级市场一般经销食品和日用品为主，其特点主要是，薄利多销，基本上下设售货员经营中低档商品；商品采用小包装、标明分量、规格和价格;备有小车或货筐、顾客自选商品；出门一次结算付款。
        </div>
        <div class="culture_img"><img src="/images/new_login_banner.png"></div>
    </div>
</div>
<script>
    function page_init() {
        <?php if (!empty(Yii::$app->request->get('app'))) {?>
        $(".new_header").hide();
        <?php }?>
    }
</script>