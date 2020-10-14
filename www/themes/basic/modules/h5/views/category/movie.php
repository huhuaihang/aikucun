<?php

use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

$this->registerCssFile('/style/owl.carousel.min.css');
$this->registerJsFile('/js/owl.carousel.min.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = '电影';
?>
<div class="box1">
    <div class="B_head movie_head">
        <span class="span1"><a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png"></a></span><span class="span3">主页</span>
        <a href="#"><span class="span2"><input type="text" placeholder="输入商家名,品类或商圈"></span></a>
    </div><!--B_head-->
    <!--所有电影-->
    <div class="remen_movie clearfix">
        <h4>热门影片</h4>
        <a href="#">查看更多 &gt;</a>
    </div>
    <div class="demo">
        <div class="container mar">
            <div class="row">
                <div class="col-md-12">
                    <div id="news-slider1" class="owl-carousel">
                        <div class="post-slide ">
                            <div class="post-img">
                                <a href="<?php echo Url::to(['/h5/movie/view']);?>"><img src="/images/dianying_03.png" alt=""></a>
                            </div>
                            <div class="post-content">
                                <p>加勒比海盗</p>
                            </div>
                        </div>
                        <div class="post-slide">
                            <div class="post-img">
                                <a href="movie.html"><img src="/images/dianying_05.png" alt=""></a>
                            </div>
                            <div class="post-content">
                                <p>“吃吃”的爱</p>
                            </div>
                        </div>
                        <div class="post-slide">
                            <div class="post-img">
                                <a href="movie.html"><img src="/images/dianying_07.png" alt=""></a>
                            </div>
                            <div class="post-content">
                                <p>荡寇风云</p>
                            </div>
                        </div>
                        <div class="post-slide">
                            <div class="post-img">
                                <a href="movie.html"><img src="/images/dianying_09.jpg" alt=""></a>
                            </div>
                            <div class="post-content">
                                <p>多啦A梦</p>
                            </div>
                        </div>
                        <div class="post-slide">
                            <div class="post-img">
                                <a href="movie.html"><img src="/images/dianying_03.png" alt=""></a>
                            </div>
                            <div class="post-content">
                                <p>小黄人大联盟</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--滚动广告-->
    <div class="scroll_box clearfix">
        <div class="biao_tit">xxx</div>
        <div id="scrollDiv">
            <ul class="time_name fr">
                <li>
                    <a href="#">今日实时票房444.26万，点击查看今日实时票房444.26万，点击查看今日实时票房444.26万，点击查看</a>
                </li>
                <li>
                    <a href="#">明日实时票房155万，点击查看</a>
                </li>
                <li>
                    <a href="#">变形金刚5，王者归来，点击查看</a>
                </li>
                <li>
                    <a href="#">卑鄙的我3,席卷全国，点击查看</a>
                </li>
            </ul>
        </div>
    </div>
    <!--筛选条件-->
    <div class="filter_item">
        <ul class="clearfix">
            <li>
                <div class="suoyou1">
                    <p>全城</p>
                    <div class="arro_box"><img src="/images/arrow-drop-down_03.png" width="100%" height="100%"/></div>
                </div>
            </li>
            <li>
                <div class="suoyou1">
                    <p>品牌</p>
                    <div class="arro_box"><img src="/images/arrow-drop-down_03.png" width="100%" height="100%"/></div>
                </div>
            </li>
            <li>
                <div class="suoyou">
                    <p>离我最近</p>
                    <div class="arro_box teshu"><img src="/images/arrow-drop-down_03.png" width="100%" height="100%"/></div>
                </div>
            </li>
            <li>
                <div class="suoyou1">
                    <p>特色</p>
                    <div class="arro_box"><img src="/images/arrow-drop-down_03.png" width="100%" height="100%"/></div>
                </div>
            </li>
        </ul>
    </div>


    <!--默认显示-->
    <div class="dianying_bto">
        <!--选择条件时遮罩层-->
        <ul class="condition_popup">
            <li class="yy_xian">
                <a class="diyige" href="javascript:;">全部影院30</a>
                <a href="#"><span>影院1</span><span>3</span></a>
                <a href="#"><span>影院2</span><span>1</span></a>
                <a href="#"><span>影院3</span><span>2</span></a>
                <a href="#"><span>影院4</span><span>2</span></a>
                <a href="#"><span>影院1</span><span>1</span></a>
                <a href="#"><span>影院2</span><span>1</span></a>
                <a href="#"><span>影院3</span><span>1</span></a>
                <a href="#"><span>影院4</span><span>3</span></a>
                <a href="#"><span>影院1</span><span>4</span></a>
                <a href="#"><span>影院2</span><span>2</span></a>
                <a href="#"><span>影院3</span><span>2</span></a>
                <a href="#"><span>影院4</span><span>3</span></a>
            </li>
            <li class="clearfix yy_xian">
                <div class="quancheng_zuo">
                    <a href="#"><span>全城</span><span>39</span></a>
                    <a href="#"><span>推荐商圈</span><span>1</span></a>
                    <a href="#"><span>河东区</span><span>1</span></a>
                    <a href="#"><span>兰山区</span><span>1</span></a>
                    <a href="#"><span>罗庄区</span><span>3</span></a>
                    <a href="#"><span>费县</span><span>4</span></a>
                    <a href="#"><span>郯城县</span><span>2</span></a>
                    <a href="#"><span>平阴县</span><span>2</span></a>
                    <a href="#"><span>莒南县</span><span>3</span></a>
                    <a href="#"><span>沂南县</span><span>2</span></a>
                    <a href="#"><span>沂水县</span><span>2</span></a>
                    <a href="#"><span>临沭县</span><span>2</span></a>
                    <a href="#"><span>蒙阴县</span><span>3</span></a>
                    <a href="#"><span>兰陵县</span><span>3</span></a>
                </div>
                <div class="quancheng_you">

                    <div class="quancheng_you1 show">
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>百丽广场</span><span>4</span></a>
                        <a href="#"><span>临沂一中</span><span>2</span></a>
                        <a href="#"><span>东方不夜城</span><span>2</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>临沂一中</span><span>2</span></a>
                        <a href="#"><span>东方不夜城</span><span>2</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>百丽广场</span><span>4</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>临沂一中</span><span>2</span></a>

                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>百丽广场</span><span>4</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>东方不夜城</span><span>2</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">

                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">

                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                    <div class="quancheng_you1">
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                        <a href="#"><span>启阳路</span><span>3</span></a>
                        <a href="#"><span>火车站</span><span>2</span></a>
                        <a href="#"><span>沂蒙路</span><span>1</span></a>
                        <a href="#"><span>坦然路</span><span>1</span></a>
                        <a href="#"><span>香港路</span><span>1</span></a>
                        <a href="#"><span>人民医院</span><span>3</span></a>
                    </div>
                </div>
            </li>
            <li class="yy_xian">
                <a href="javascript:;"><span>离我最近</span><span></span></a>
                <a href="javascript:;"><span>好评优先</span><span></span></a>
                <a href="javascript:;"><span>价格最低</span><span></span></a>
            </li>
            <li class="yy_xian"></li>
        </ul>


        <div class="cinema">
            <div class="default_show show">
                <a href="<?php echo Url::to(['/h5/movie/cinema']);?>">
                    <h4>保利乐尚激光影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor none">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">0.8km</span>
                </a>
                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>
                <a href="#">
                    <h4>恒大影城（临沂店）</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor none">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">2.8km</span>
                </a>

            </div>
            <div class="default_show">

                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>
                <a href="#">
                    <h4>保利乐尚激光影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor none">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">0.8km</span>
                </a>
                <a href="#">
                    <h4>恒大影城（临沂店）</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor none">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">2.8km</span>
                </a>
            </div>
            <div class="default_show">
                <a href="#">
                    <h4>恒大影城（临沂店）</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor none">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">2.8km</span>
                </a>
                <a href="#">
                    <h4>保利乐尚激光影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor none">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">0.8km</span>
                </a>
                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>

            </div>
            <div class="default_show">
                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>
                <a href="#">
                    <h4>保利乐尚激光影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor none">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">0.8km</span>
                </a>
                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>
                <a href="#">
                    <h4>恒大影城（临沂店）</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor none">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">2.8km</span>
                </a>
            </div>
            <div class="default_show">
                <a href="#">
                    <h4>恒大影城（临沂店）</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor none">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">2.8km</span>
                </a>
                <a href="#">
                    <h4>保利乐尚激光影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor none">折扣卡</span>
                        <span class="borcolor">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">0.8km</span>
                </a>
                <a href="#">
                    <h4>大华时代国际影城</h4>
                    <p class="clearfix mm_topbot">
                        <span>¥28起</span>
                        <span>座</span>
                        <span>退</span>
                        <span>改签</span>
                        <span class="borcolor ">折扣卡</span>
                        <span class="borcolor none">小吃</span>
                        <span class="borcolor">满赠卡</span>
                    </p>
                    <p>近期场次：12:05 | 14:30 | 16:55</p>
                    <span class="juliwo">5km</span>
                </a>
            </div>
        </div>
    </div>
</div><!--box-->
<script>
    function page_init() {
        //电影图片左右滑动
        $("#news-slider1").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,4],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });

        //新闻滚动
        setInterval(function (){
            $("#scrollDiv").find("ul").animate({
                marginTop:"-0.4rem"
            },500,function(){
                $(this).css({marginTop:"0"}).find("li:first").appendTo(this);
            });
        },3000);

        /*x想看*/
        var onOff=true;
        $('.like_mark li').eq(0).click(function(){
            if(onOff){
                $(this).find('.lima_img img').attr('src','images/heart1.png');
                $(this).find('p').empty().html('已想看');
                onOff=false;
            }else{
                $(this).find('.lima_img img').attr('src','images/heart.png');
                $(this).find('p').empty().html('想看');
                onOff=true;
            }

        });

        /*时间场次*/
        $('#news-slider .owl-wrapper .owl-item').click(function(){
            var num=$(this).index();
            $(this).find('.post-slide').addClass('pslide_bto');
            $(this).siblings('.owl-item').find('.post-slide').removeClass('pslide_bto');
            $('.cinema .default_show').eq(num).addClass('show').siblings('.default_show').removeClass('show');
        });
        /*筛选条件*/
        $('.filter_item ul li').click(function(){
            var num=$(this).index();
            $('.film_info').hide();
            if(num==0){
                $('.condition_popup li').eq(0).addClass('show').siblings('li').removeClass('show');
                $('.yy_xian').eq(num).find('a').click(function(){
                    var neirong1=$(this).find('span:first').html();
                    $('.filter_item ul li').eq(0).find('div p').html(neirong1);
                    $('.condition_popup li').removeClass('show');
                    $('.film_info').show();
                });
            }else if(num==2){
                $('.condition_popup li').eq(2).addClass('show').siblings('li').removeClass('show');
                $('.yy_xian').eq(num).find('a').click(function(){
                    var neirong2=$(this).find('span:first').html();
                    $('.filter_item ul li').eq(2).find('div p').html(neirong2);
                    $('.condition_popup li').removeClass('show');
                    $('.film_info').show();
                });
            }else if(num==3){
                $('.condition_popup li').eq(3).addClass('show').siblings('li').removeClass('show');
                $('.yy_xian').eq(num).find('a').click(function(){
                    var neirong3=$(this).find('span:first').html();
                    $('.filter_item ul li').eq(3).find('div p').html(neirong3);
                    $('.condition_popup li').removeClass('show');
                    $('.film_info').show();
                });
            }else{
                $('.condition_popup li').eq(1).addClass('show').siblings('li').removeClass('show');
                var a=document.documentElement.clientHeight;  //窗口高度
                var b=$('.cinema').offset().top;
                var c=a-b;
                $('.dianying_bto').find('li').css('height',c+'px');
                $('.quancheng_zuo a').click(function(){
                    var num1=$(this).index();
                    $('.quancheng_you1').eq(num1).addClass('show').siblings('.quancheng_you1').removeClass('show');
                });
                $('.quancheng_you1 a').click(function(){
                    var neirong=$(this).find('span:first').html();
                    $('.filter_item ul li').eq(1).find('p').html(neirong);
                    $('.film_info').show();
                    $('.condition_popup li').removeClass('show');
                });

            };
        });
    }
</script>
