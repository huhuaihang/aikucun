<?php

use app\assets\H5Asset;

/**
 * @var $this \yii\web\View
 * @var $goods \app\models\Goods
 * @var $similar_list []  相似产品列表
 */

H5Asset::register($this);
?>
<div class="box">
    <div class="product_details">
        <div class="div4_box">
            <div class="div4">
                <div class="div4_content">
                    <?php echo $goods->content;?>
                </div>
            </div>
        </div>
        <?php if(count($similar_list)>0){?>
            <div class="Y_div4 vip_div">
                <h4 class="biaoti">
                    <img src="/images/tuijian9.png">
                </h4>
                <div class="invite">

                    <?php foreach ($similar_list as $item) { ?>
                        <div class="invite_s">
                            <a href="/h5/goods/view?id=<?php echo $item['id'];?>">
                                <div class="invite_img">
                                    <img src="<?php echo $item['main_pic'];?>">
                                </div>
                                <div class="s-list_x">
                                    <dd class="dd1"><span class="span1"><?php echo $item['title'];?></span></dd>
                                    <dd class="dd3 s-list-t"> <?php echo $item['desc']; ?></dd>
                                    <dd class="dd3">
                                        <div class="dd3_s">
                                            <span class="span1">¥<?php echo $item['price']; ?></span>
                                        </div>
                                        <div class="commission">
                                            <p class="commission_p1">分佣¥<?php echo $item['share_commission']; ?></p>
                                        </div>
                                    </dd>
                                </div>
                            </a>
                        </div>
                    <?php }?>

                </div>
            </div>
        <?php };?>
    </div>
</div>
