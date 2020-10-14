<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\UtilAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $shop \app\models\Shop
 * @var $decoration \app\models\ShopDecoration
 * @var $decoration_item_list \app\models\ShopDecorationItem[]
 * @var integer $fav_count 店铺收藏数
 * @var $goods_list \app\models\Goods[]
 * @var $pagination \yii\data\Pagination
 * @var $shop_score integer 店铺平均评分
 * @var $category_count integer 店铺分类数
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
UtilAsset::register($this);

$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');
$this->title = '店铺首页';

?>
<div class="box" id="app">
    <header class="mall-headers mall-header-index">
        <div class="new_header-s">
            <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5'])?>'" class="a1"><img src="/images/new_header.png"></a>
            <a href="#" class="a2">{{ shop.name }}</a>
            <a href="#" class="a3"><img src="/images/fenx.png" class="imgs"></a>
        </div><!--new_header-->
        <div class="mall-header-title">
            <a href="<?php echo Url::to(['/h5/default/search']);?>">输入商家名称或品类</a>
        </div>
    </header>
    <div class="shop-top" ref="wrapper">
        <img :src="shop.logo">
        <p>{{ shop.name }}</p>
        <span class="hus" v-if="!is_fav">
            <img src="/images/guanzhu.png" @click="change(shop.id)" >
        </span>
        <span class="hus" v-else>
            <img src="/images/guanzhu2.png" @click="change(shop.id)" >
        </span>
    	</div>
    	<div id="wrap-s">
    		<div id="tit-s">
    			<span class="select-s">店铺首页</span>
    			<span>店铺推荐</span>
    		</div>
    		<div id="login-s">
    			<div class="login-s show-s">
                    <div class="Y_banner">
                        <div class="block_home_slider">
                            <div id="home_slider" class="flexslider">
                                <ul class="slides">
                                    <li v-for="image in image_list">
                                        <div class="slide">
                                            <a href="<?php echo Url::to(['/h5/default/about-brand'])?>"><img :src="image.url" /></a>
                                        </div>
                                    </li>
                                </ul>
                                <ol class="flex-control-nav flex-control-paging"></ol>
                            </div><!--home_slider-->
                        </div><!--block_home_slider-->
                    </div><!--Y_banner-->
    				<div id="wrap-x">
    					<div id="tit-x">
    						<span class="select-x">新品</span>
    						<span>销量</span>
    						<span>价格</span>
    					</div>
    					<div id="login-x">
    						<div class="login-x show-x">
    							<div class="list-di">
    								<ul>
    									<li v-for="goods in new_list">
    										<a href="#">
    											<img :src="goods.main_pic">
    											<p>{{ goods.title }}</p>
    											<p>{{ goods.desc }}</p>
    											<p>￥{{ goods.price }}</p>
    										</a>
    									</li>
    								</ul>
    							</div>
    						</div>
    						<div class="login-x">
    							<div class="list-di">
    								<ul>
    									<li v-for="goods in sale_list">
                                            <a href="#">
                                                <img :src="goods.main_pic">
                                                <p>{{ goods.title }}</p>
                                                <p>{{ goods.desc }}</p>
                                                <p>￥{{ goods.price }}</p>
                                            </a>
    									</li>
    								</ul>
    							</div>
    						</div>
    						<div class="login-x">
    							<div class="list-di">
    								<ul>
    									<li v-for="goods in price_list">
                                            <a href="#">
                                                <img :src="goods.main_pic">
                                                <p>{{ goods.title }}</p>
                                                <p>{{ goods.desc }}</p>
                                                <p>￥{{ goods.price }}</p>
                                            </a>
    									</li>
    								</ul>
    							</div>
    						</div>
    					</div>
    				</div>

    			</div>
    			<div class="login-s">
    				<div class="list-di">
    					<ul>
    						<li v-for="goods in recommend_list">
                                <a href="#">
                                    <img :src="goods.main_pic">
                                    <p>{{ goods.title }}</p>
                                    <p>{{ goods.desc }}</p>
                                    <p>￥{{ goods.price }}</p>
                                </a>
    						</li>
    					</ul>
    				</div>
    			</div>
    		</div>
    	</div>
    	<div class="navigation">
    		<ul>
    			<li>
    				<a :href="'/h5/shop/category?id=' + shop.id">
    					<img src="/images/fenl.png">
    					<p>商品分类</p>
    				</a>
    			</li>
    			<li>
    				<a :href="'/h5/message/chat?sid=' + shop.id">
    					<img src="/images/kef.png">
    					<p>商家客服</p>
    				</a>
    			</li>
    		</ul>
    	</div>
    	<div class="kePublic" style="display:none;" >
			<div class="gb_resLay shengji_share">
				<div class="bdsharebuttonbox">
					<ul class="gb_resItms">
						<li> <a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin"></a>微信好友 </li>
						<li> <a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq"></a>QQ好友 </li>
						<li> <a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone"></a>QQ空间 </li>
						<li> <a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina"></a>新浪微博 </li>
						<li> <a title="分享到朋友圈" href="#" class="bds_pengyou" data-cmd="sns_icon"></a>朋友圈</li>
					</ul>
				</div>
				<div class="clear"></div>
				<div class="gb_res_t"><span class="cancel">取消</span><i></i></div>
			</div>
		</div><!--kePublic-->
        </div><!--box-->
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            shop: {}, // 店铺信息
            decoration: {}, // 店铺装修
            is_fav: 0, // 是否关注
            image_list: [], // 轮播图列表
            new_list: [], // 新品排序列表
            sale_list: [], // 销量排序列表
            price_list: [], // 价格排序列表
            recommend_list: [], // 推荐列表
            SearchForm: {
                id: 0,
                sort: '',
                page: 1
            }, // 搜索表单
            page: {}, // 分页
            scroll: false // 滚动监听器
        },
        methods: {
            getNewList: function (id) {
                this.SearchForm.id = id;
                this.SearchForm.sort = 'create_time';
                apiGet('/api/shop/detail', this.SearchForm, function(json) {
                    if (callback(json)) {
                        app.shop = json['shop'];
                        app.is_fav = json['is_fav'];
                        json['image_list'].forEach(function (image) {
                            app.image_list.push(image);
                        });
                        json['goods_list'].forEach(function (goods) {
                            app.new_list.push(goods);
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
                                            app.loadNoticeList();
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
            getSaleList: function(id) {
                this.SearchForm.id = id;
                this.SearchForm.sort = 'amount';
                apiGet('/api/shop/detail', this.SearchForm, function(json) {
                    if (callback(json)) {
                        app.shop = json['shop'];
                        app.is_fav = json['is_fav'];
                        app.image_list = json['image_list'];
                        json['goods_list'].forEach(function(goods) {
                            app.sale_list.push(goods);
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
                                            app.loadNoticeList();
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
            getPriceList: function(id) {
                this.SearchForm.id = id;
                this.SearchForm.sort = 'price';
                apiGet('/api/shop/detail', this.SearchForm, function(json) {
                    if (callback(json)) {
                        app.shop = json['shop'];
                        app.is_fav = json['is_fav'];
                        app.image_list = json['image_list'];
                        json['goods_list'].forEach(function(goods) {
                            app.price_list.push(goods);
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
                                            app.loadNoticeList();
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
            getRecommendList: function(id) {
                this.SearchForm.id = id;
                apiGet('/api/shop/recommend-list', this.SearchForm, function(json) {
                    if (callback(json)) {
                        json['goods_list'].forEach(function(goods) {
                            app.recommend_list.push(goods);
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
                                            app.loadNoticeList();
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
            change: function (id) {
                if (app.is_fav === 0) {
                    apiGet('/api/user/add-fav-shop', {id: id}, function (json) {
                        if (callback(json)) {
                            app.is_fav = 1;
                        }
                    });
                } else {
                    apiGet('/api/user/delete-fav-shop', {sid: id}, function (json) {
                        if (callback(json)) {
                            app.is_fav = 0;
                        }
                    });
                }
            }
        },
        mounted: function() {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 727) + 'px';
            var id = "<?php echo Yii::$app->request->get('id');?>";
            this.getNewList(id);
            this.getSaleList(id);
            this.getPriceList(id);
            this.getRecommendList(id);
        }
    });
</script>
<script>
    $('#tit-s span').click(function() {
        var i = $(this).index();
        $(this).addClass('select-s').siblings().removeClass('select-s');
        $('.login-s').eq(i).show().siblings().hide();
    });
    $('#tit-x span').click(function() {
        var i = $(this).index();
        $(this).addClass('select-x').siblings().removeClass('select-x');
        $('.login-x').eq(i).show().siblings().hide();
    });
    function page_init() {
        window.setTimeout(function(){
            $('#home_slider').flexslider({
                animation : 'slide',
                controlNav : true,
                directionNav : false,
                animationLoop : true,
                slideshow : true,
                slideshowSpeed: 3000,
                useCSS : false
            });
            $(".mall-headers .new_header-s .imgs").click(function(){
                $(".kePublic").show();
            });
            $(".cancel").click(function(){
                $(".kePublic").hide();
            });
            $('.div3_p1 p,.div3_p1_color p').click(function(){
                $(this).addClass('change_color').siblings('p').removeClass('change_color');
            });
        },500)
        window._bd_share_config={
            "common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"16"},
            "share":{},
            "image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},
            "selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}
        };
        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src=
            'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
    }
</script>
