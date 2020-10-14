<?php

use app\assets\InfiniteScrollAsset;
use app\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Goods[]
 * @var $pagination \yii\data\Pagination
 */

InfiniteScrollAsset::register($this);

$this->title = '搜索结果';
?>
<div class="box1">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/shop/view', 'id' => Yii::$app->request->get('sid')]);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">搜索结果</div>
    </header>
    <div class="container">
        <!--分类菜单-->
        <ul class="sort clearfix">
            <li class="
                    <?php
                        if (Yii::$app->request->get('sort') == 'sale') {
                            if (Yii::$app->request->get('order') == 'DESC') {
                                echo "on_desc";
                            } else {
                                echo "on_asc";
                            }
                        }
                    ?>
            ">
                <a data-toggle="sort-order" href="<?php echo Url::current(['sort' => 'sale', 'order' => (Yii::$app->request->get('sort') == 'sale' && Yii::$app->request->get('order') == 'DESC')? 'ASC' : 'DESC']);?>">销量<span></span></a>
            </li>
            <li class="
                <?php
                    if (Yii::$app->request->get('sort') == 'price') {
                        if (Yii::$app->request->get('order') == 'DESC') {
                            echo "on_desc";
                        } else {
                            echo "on_asc";
                        }
                    }
                ?>
            ">
                <a data-toggle="sort-order" href="<?php echo Url::current(['sort' => 'price', 'order' => (Yii::$app->request->get('sort') == 'price' && Yii::$app->request->get('order') == 'DESC')? 'ASC' : 'DESC']);?>">价格<span></span></a>
            </li>
            <li class="no_rline
                <?php
                    if (Yii::$app->request->get('sort') == 'create_time') {
                        if (Yii::$app->request->get('order') == 'DESC') {
                            echo "on_desc";
                        } else {
                            echo "on_asc";
                        }
                    }
                ?>
            ">
                <a data-toggle="sort-order" href="<?php echo Url::current(['sort' => 'create_time', 'order' => (Yii::$app->request->get('sort') == 'create_time' && Yii::$app->request->get('order') == 'DESC')? 'ASC' : 'DESC']); ?>">上架<span></span></a>
            </li>
        </ul>
        <!--产品列表-->
        <?php if (empty($model_list)) {?>
            <div style="text-align: center; padding-top: 20px;">没找到商品，去看看其他商品？</div>
        <?php } else {?>
        <ul class="search_result" id="data_list">
            <?php foreach ($model_list as $model) {?>
            <li>
                <a href="<?php echo Url::to(['/h5/goods/view', 'id' => $model->id])?>">
                    <div class="cp_pic">
                        <img src="<?php echo Yii::$app->params['upload_url'].$model->main_pic;?>"/>
                    </div>
                    <div class="cp_text">
                        <h3><?php echo Html::encode($model->title);?></h3>
                        <p>售价：<span>¥</span><span class="price"><?php echo $model->price;?></span></p>
                    </div>
                </a>
            </li>
            <?php } ?>
        </ul>
        <?php }?>
    </div>
</div><!--box-->
<div style="display:none;"><?php echo LinkPager::widget(['pagination'=>$pagination]);?></div>
<script>
    function page_init() {
        // 自动加载更多记录
        $('#data_list').infinitescroll({
            loading: {
                msgText: '正在加载更多记录。',
                finishedMsg: '没有更多记录了。'
            },
            navSelector: ".pagination",
            nextSelector: ".pagination .next a",
            itemSelector: "#data_list",
            maxPage:<?php echo $pagination->pageCount;?>
        });
    }
</script>
