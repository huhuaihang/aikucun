<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->title = '商品列表';
?>
<div class="box1" id="app">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo empty(Yii::$app->request->get('sid')) ? Url::to(['/h5/default/search']) : Url::to(['/h5/shop/view', 'id' => Yii::$app->request->get('sid')])?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">{{title}}</div>
    </header>
    <div class="container">
        <!--分类菜单-->
        <ul class="sort clearfix" style="z-index: 999;position: relative">
            <li :class="{on_asc: SearchForm.sort === 'sale' && SearchForm.order === 'ASC', on_desc: SearchForm.sort === 'sale' && SearchForm.order === 'DESC'}">
                <a href="javascript:void(0)" @click="sortWithSale()">销量<span></span></a>
            </li>
            <li :class="{on_asc: SearchForm.sort === 'price' && SearchForm.order === 'ASC', on_desc: SearchForm.sort === 'price' && SearchForm.order === 'DESC'}">
                <a href="javascript:void(0)" @click="sortWithPrice()">价格<span></span></a>
            </li>
            <li class="no_rline" :class="{on_asc: SearchForm.sort === 'create_time' && SearchForm.order === 'ASC', on_desc: SearchForm.sort === 'create_time' && SearchForm.order === 'DESC'}">
                <a href="javascript:void(0)" @click="sortWithTime()">上架<span></span></a>
            </li>
        </ul>
        <!--产品列表-->
        <div ref="wrapper">
            <div v-if="goods_list.length == 0" style="text-align: center; padding-top: 20px;">没找到商品，去看看其他商品？</div>
            <ul class="search_result">
                <li v-for="goods in goods_list">
                    <a :href="'/h5/goods/view?id='+goods['id']">
                        <div class="cp_pic">
                            <img :src="goods['main_pic']+'_100x100'"/>
                        </div>
                        <div class="cp_text">
                            <h3>{{goods['title']}}</h3>
                            <p><span>¥</span><span class="price" style="font-size: .32rem;">{{goods['price']}}</span></p>
                            <div class="limited_jr">
                                <span style="color: #999; text-align: right; display: block; margin-top: .05rem;">已售{{goods.sale_amount}}件</span>
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
                apiGet('/api/goods/list', this.SearchForm, function (json) {
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
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 95) + 'px';
            this.loadGoodsList();
        }
    });
</script>