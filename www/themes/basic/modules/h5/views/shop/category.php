<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $shop \app\models\Shop
 * @var $category_list \app\models\ShopGoodsCategory[]
 */

$this->title = '店铺商品分类';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">店铺分类</div>
    </header>
    <div class="container">
        <a class="b_all_goods" href="<?php echo Url::to(['/h5/shop/search', 'do_search' => 1, 'sid' => $shop->id])?>">全部宝贝</a>
        <div class="b_baobei">
            <div class="b_bb_box ">
                <ul class="clearfix">
                    <?php if (!empty($category_list)) { foreach ($category_list as $category) {?>
                    <li>
                        <a href="<?php echo Url::to(['/h5/shop/search', 'do_search' => 1, 'scid' => $category->id, 'sid' => $shop->id])?>"><?php echo Html::encode($category->name);?></a>
                    </li>
                    <?php }}?>
                </ul>
            </div>
        </div>
    </div>
</div>
<!--box-->
