<?php

/**
 * @var $this \yii\web\View
 */

$this->registerCssFile('/style/owl.carousel.min.css');
$this->registerCssFile('/style/idangerous.swiper.css');
$this->registerJsFile('/js/owl.carousel.min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/idangerous.swiper.min.js');

$this->title = '影院';
?>
<div class="box1">
    <div class="about_head about_head2">
        <a href="javascript:void(0)" onClick="window.history.go(-1)"><p class="p1"><img src="/images/111.png"></p></a>
        <p class="p2">影院</p>
        <div class="collect_cinema">
            <img src="/images/wuxing.png" width="100%" height="100%"/>
        </div>
        <a class="fenxiang" href="#" onClick="toshare()" >
            <img src="/images/share_icon.png" width="100%" height="100%"/>
        </a>
    </div><!--about_head-->
    <!--影院地址-->
    <div class="cinema_address ">
        <ul class="dizhi_map clearfix">
            <li>
                <a href="#">
                    <h3>恒大影城</h3>
                    <p>河东经济开发区华夏路与香港路交汇处西北角100米恒大影城</p>
                    <div class="addres_right">
                        <img src="/images/arr12321.png" width="100%" height="100%"/>
                    </div>
                </a>
            </li>
            <li>
                <a href="#">
                    <div class="cinema_map">
                        <img src="/images/map.png" width="100%" height="100%"/>
                    </div>
                    <span>地图</span>
                </a>
            </li>
        </ul>
        <ul class="coupon clearfix">
            <li class="taocan">
                <h3>观影套餐</h3>
                <p>小吃、周边13.2元起</p>
            </li>
            <li class="manzengka">
                <h3>满赠卡</h3>
                <p>再买4张开卡</p>
            </li>
        </ul>
    </div>
    <!--影片图片-->
    <div class="device">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_03.png" width="100%" height="100%"/></a>
                </div>
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_05.png" width="100%" height="100%"/></a>
                </div>
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_07.png" width="100%" height="100%"/></a>
                </div>
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_03.png" width="100%" height="100%"/></a>
                </div>
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_05.png" width="100%" height="100%"/></a>
                </div>
                <div class="swiper-slide ">
                    <a href="#"><img src="/images/dianying_07.png" width="100%" height="100%"/></a>
                </div>
            </div>
        </div>
        <div class="pagination">
		<span class="swiper-pagination-switch  ">
			<h3>加勒比海盗5：死无对证<span>8.9分</span></h3>
			<p>129分钟 | 喜剧 | 约翰·尼德普，哈维尔·巴登</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月10日
								</div>
								<div class="post-slide">
									明天6月11日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>2:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>2:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
            <span class="swiper-pagination-switch swiper-active-switch">
			<h3>“吃吃”的爱<span>8.0分</span></h3>
			<p>129分钟 | 喜剧 | 欧豪，国子监</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider5" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月12日
								</div>
								<div class="post-slide">
									明天6月13日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>13:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>13:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>22:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>22:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
            <span class="swiper-pagination-switch ">
			<h3>神偷奶爸3<span>8.9分</span></h3>
			<p>129分钟 | 喜剧 | 史蒂夫，克里斯汀·巴登</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider6" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月14日
								</div>
								<div class="post-slide">
									明天6月15日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>18:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>19:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
            <span class="swiper-pagination-switch ">
			<h3>大护法<span>7.9分</span></h3>
			<p>129分钟 | 喜剧 | 约翰·尼德普，哈维尔·巴登</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider2" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月16日
								</div>
								<div class="post-slide">
									明天6月17日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
            <span class="swiper-pagination-switch ">
			<h3>变形剑刚5：最后的骑士<span>8.9分</span></h3>
			<p>129分钟 | 喜剧 | 约翰·尼德普，哈维尔·巴登</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider3" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月18日
								</div>
								<div class="post-slide">
									明天6月19日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
            <span class="swiper-pagination-switch ">
			<h3>闪光少女<span>8.9分</span></h3>
			<p>129分钟 | 喜剧 | 约翰·尼德普，哈维尔·巴登</p>
			<div class="demo bofangchangci">
				<div class="container show">
					<div class="row">
						<div class="col-md-12" >
							<div id="news-slider4" class="owl-carousel">
								<div class="post-slide pslide_bto">
									今天6月20日
								</div>
								<div class="post-slide">
									明天6月21日
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="buy_ticket">
					<ul class="show">
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
					<ul>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
						<li>
							<div class="start_end">
								<h3>12:05</h3>
								<p>14:14散场</p>
							</div>
							<div class="movie_type">
								<h4>英语3D</h4>
								<p>5号激光4K影厅</p>
							</div>
							<div class="mov_price">¥28</div>
							<a href="#">购票</a>
						</li>
					</ul>
				</div>
			</div>
		</span>
        </div>
    </div>
    <!--分享弹窗-->
    <div class="am-share">
        <h3 class="am-share-title">分享到</h3>
        <ul class="am-share-sns">
            <li>
                <a href="#">
                    <i class="share-icon-weibo" style="background-image: url(images/weibo.png);"></i> <span>新浪微博</span>
                </a>
            </li>
            <li>
                <a href="#"> <i class="share-icon-weibo" style="background-image: url(images/txweibo.png);"></i> <span>腾讯微博</span>
                </a>
            </li>
            <li>
                <a href="#"> <i class="share-icon-weibo" style="background-image: url(images/pyquan.png);"></i> <span>朋友圈</span>
                </a>
            </li>
            <li>
                <a href="#"> <i class="share-icon-weibo" style="background-image: url(images/QQkongjian.png);"></i> <span>QQ空间</span>
                </a>
            </li>
        </ul>
        <div class="am-share-footer"><button class="share_btn">取消</button></div>
    </div>
</div><!--box-->
<script>
    function page_init() {
        //电影图片左右滑动
        var mySwiper = new Swiper('.swiper-container',{
            pagination: '.pagination',
            paginationClickable: true,
            centeredSlides: true,
            slidesPerView: 3,
            watchActiveIndex: true
        });

        //日期左右滑动
        $("#news-slider").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });
        $("#news-slider6").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });
        $("#news-slider2").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });
        $("#news-slider3").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });
        $("#news-slider4").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });
        $("#news-slider5").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });

        /*时间场次*/
        $('.owl-wrapper .owl-item').click(function(){
            var num=$(this).index();
            $(this).find('.post-slide').addClass('pslide_bto');
            $(this).siblings('.owl-item').find('.post-slide').removeClass('pslide_bto');
            $(this).parents('span').find('.buy_ticket ul').eq(num).addClass('show').siblings('ul').removeClass('show');

        });

        $('.collect_cinema img').toggle(function(){
            $(this).attr('src','images/wuxing1.png')
        },function(){
            $(this).attr('src','images/wuxing.png')
        });
    }
    //分享按钮
    function toshare(){
        $(".am-share").addClass("am-modal-active");
        if($(".sharebg").length>0){
            $(".sharebg").addClass("sharebg-active");
        }else{
            $("body").append('<div class="sharebg"></div>');
            $(".sharebg").addClass("sharebg-active");
        }
        $(".sharebg-active,.share_btn").click(function(){
            $(".am-share").removeClass("am-modal-active");
            setTimeout(function(){
                $(".sharebg-active").removeClass("sharebg-active");
                $(".sharebg").remove();
            },300);
        })
    }
</script>
