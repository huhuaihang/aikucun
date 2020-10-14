<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\widgets\AdWidget;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');
$this->title = '商品列表';
?>
<div class="box1" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo  Url::to(['/h5']) ; ?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">{{title}}</div>
    </header>
    <div ref="wrapper">
    <div class="container">
        <!--分类菜单-->
        <div class="Y_banner" >
            <div class="block_home_slider">
                <div id="home_slider" class="flexslider">
                    <ul class="slides">
                        <?php $type = Yii::$app->request->get('type');
                            if ($type == 'best') {AdWidget::begin(['lid' => 10]);}
                            if ($type == 'score') {AdWidget::begin(['lid' => 11]);}
                            if ($type == 'socre') {AdWidget::begin(['lid' => 11]);}
                            if ($type == 'commission') {AdWidget::begin(['lid' => 12]);}
                        ?>
                        {foreach $ad_list as $ad}
                        <li>
                            <div class="slide">
                                <a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}"><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}" /></a>
                            </div>
                        </li>
                        {/foreach}
                        <?php   AdWidget::end();?>
                    </ul>
                    <ol class="flex-control-nav flex-control-paging"></ol>
                </div><!--home_slider-->
            </div><!--block_home_slider-->
        </div><!--Y_banner-->
        <!--产品列表-->

            <div v-if="goods_list.length == 0" style="text-align: center; padding-top: 20px;">没找到商品，去看看其他商品？</div>
            <ul class="search_result">
                <li v-for="goods in goods_list">
                    <a :href="'/h5/goods/view?id='+goods['id']">
                        <div class="cp_pic">
                            <img :src="goods['main_pic']+'_100x100'"/>
                        </div>
                        <div class="cp_text">
                            <h3>{{goods['title']}}</h3>
                            <dd class="dd3 s-list-t">{{goods['desc']}}</dd>
                            <p><span>¥</span><span class="price" style="font-size: .32rem;">{{goods['price']}}</span></p>
                            <?php
                            $type = Yii::$app->request->get('type');
                            if ($type == 'score') {?>
                            <p style="text-align: right; float: left; width: 28%; margin-top: .1rem; padding-right: .2rem; font-size: .22rem; color: #fff; height: .5rem; line-height: .5rem;" class="sdfwe">积分抵<span class="price-s" style="color: #fff; margin-top: -.05rem; font-size: .2rem;">{{ goods['score']/10 }}</span></p>
                            <?php } else {?>
                            <p v-if=" goods['share_commission']!=0" style="text-align: right; float: left; margin-left: .1rem; margin-top: .1rem; border-radius: 4px; width: 28%; padding-right: .2rem; height: .5rem; font-size: .22rem; color: #fff; line-height: .5rem;" class="sdfwe">分佣¥<span class="price-s" style="color: #fff; margin-top: -.05rem; font-size: .2rem;">{{ goods['share_commission'] }}</span></p>
                            <?php }?>
                            <div class="limited_jr">
                                <span v-if="goods.sale_amount > 0" style="color: #999; text-align: right; display: block; margin-top: .05rem;">已售{{goods.sale_amount}}件</span>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            title: '商品列表',
            goods_list: [], // 商品列表
            SearchForm: {
                sid: '',
                keywords: '',
                category: '',
                sort: 'sale',
                order: 'DESC',
                type: '<?php echo Yii::$app->request->get('type');?>',
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {
            /**
             * 加载商品列表
             */
            loadGoodsList: function () {
                apiGet('/api/goods/goods-list', this.SearchForm, function (json) {
                    if (callback(json)) {
                        app.title = json['title'];
                        json['goods_list'].forEach(function (goods) {
                            app.goods_list.push(goods);
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true,
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.SearchForm.page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.SearchForm.page++;
                                            app.loadGoodsList();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },
            /**
             * 根据销量排序
             */
            sortWithSale: function () {
                if (this.SearchForm.sort === 'sale') {
                    if (this.SearchForm.order === 'DESC') {
                        this.SearchForm.order = 'ASC';
                    } else {
                        this.SearchForm.order = 'DESC';
                    }
                } else {
                    this.SearchForm.sort = 'sale';
                    this.SearchForm.order = 'DESC';
                }
                this.SearchForm.page = 1;
                this.goods_list = [];
                this.loadGoodsList();
            },
            /**
             * 根据价格排序
             */
            sortWithPrice: function () {
                if (this.SearchForm.sort === 'price') {
                    if (this.SearchForm.order === 'DESC') {
                        this.SearchForm.order = 'ASC';
                    } else {
                        this.SearchForm.order = 'DESC';
                    }
                } else {
                    this.SearchForm.sort = 'price';
                    this.SearchForm.order = 'DESC';
                }
                this.SearchForm.page = 1;
                this.goods_list = [];
                this.loadGoodsList();
            },
            /**
             * 根据时间排序
             */
            sortWithTime: function () {
                if (this.SearchForm.sort === 'create_time') {
                    if (this.SearchForm.order === 'DESC') {
                        this.SearchForm.order = 'ASC';
                    } else {
                        this.SearchForm.order = 'DESC';
                    }
                } else {
                    this.SearchForm.sort = 'create_time';
                    this.SearchForm.order = 'DESC';
                }
                this.SearchForm.page = 1;
                this.goods_list = [];
                this.loadGoodsList();
            }
        },
        mounted: function () {
            // 云约中打开时去掉双标题
            if (Util.request.get('app') !== undefined) {
                $('.mall-header').hide();
                $('.container').css('margin-top','0');
            }
            var sid = Util.request.get('sid');
            if (sid !== undefined) {
                this.SearchForm.sid = sid;
            }
            var keywords = Util.request.get('keywords');
            if (keywords !== undefined) {
                this.SearchForm.keywords = keywords;
            }
            var category = Util.request.get('category');
            if (category !== undefined) {
                this.SearchForm.category = category;
            }
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 115) + 'px';
            this.loadGoodsList();
        }
    });
    function page_init() {
        $('#home_slider').flexslider({
            animation : 'slide',
            controlNav : true,
            directionNav : true,
            animationLoop : true,
            slideshow : true,
            slideshowSpeed: 3000,
            useCSS : false
        });
    }
</script>