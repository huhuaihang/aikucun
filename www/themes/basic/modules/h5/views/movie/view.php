<?php

/**
 * @var $this \yii\web\View
 */

$this->registerCssFile('/style/owl.carousel.min.css');
$this->registerJsFile('/js/owl.carousel.min.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = '电影';
?>
<div class="box1">
    <div class="about_head">
        <a href="javascript:void(0)" onClick="window.history.go(-1)"><p class="p1"><img src="/images/11_1.png"></p></a>
        <p class="p2">上映影院与购票</p>
    </div><!--about_head-->
    <!--电影信息-->
    <div class="film_info clearfix">
        <a class="clearfix" href="#">
            <div class="film_img">
                <img src="/images/movie1_03.jpg" width="100%" height="100%"/>
            </div>
            <div class="film_txt">
                <h3>加勒比海盗5：死无对证</h3>
                <p>Pirates of the Caribbean: Dead Men Tell No Tales/Salazar's Revenge</p>
                <p class="mm_top">猫眼观众评分</p>
                <p class="mm_tb"><span>8.9</span>（34.0万人评）</p>
                <p>喜剧,动作,奇幻<font>IMAX 3D</font></p>
                <p>美国/129分钟</p>
                <p>2017-05-26大陆上映</p>
                <div class="arrow_r">
                    <img src="/images/logo_04.png" width="100%" height="100%"/>
                </div>
            </div>
        </a>
        <ul class="like_mark clearfix">
            <li>
                <div class="lima_img">
                    <img src="/images/heart.png" width="100%" height="100%"/>
                </div>
                <p>
                    想看
                </p>
            </li>
            <li class="flt_r">
                <a href="#">
                    <div class="lima_img">
                        <img src="/images/wuxing.png" width="100%" height="100%"/>
                    </div>
                    <p>
                        评分
                    </p>
                </a>
            </li>
        </ul>
    </div>
    <!--播放场次-->
    <div class="demo">
        <div class="container">
            <div class="row">
                <div class="col-md-12" >
                    <div id="news-slider" class="owl-carousel">
                        <div class="post-slide pslide_bto">
                            今天6月10日
                        </div>
                        <div class="post-slide">
                            明天6月11日
                        </div>
                        <div class="post-slide">
                            后天6月12日
                        </div>
                        <div class="post-slide">
                            6月13日
                        </div>
                        <div class="post-slide">
                            6月14日
                        </div>
                        <div class="post-slide">
                            6月15日
                        </div>
                    </div>
                </div>
            </div>
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
                    <a href="javascript:;"><span>全城</span><span>39</span></a>
                    <a href="javascript:;"><span>推荐商圈</span><span>1</span></a>
                    <a href="javascript:;"><span>河东区</span><span>1</span></a>
                    <a href="javascript:;"><span>兰山区</span><span>1</span></a>
                    <a href="javascript:;"><span>罗庄区</span><span>3</span></a>
                    <a href="javascript:;"><span>费县</span><span>4</span></a>
                    <a href="javascript:;"><span>郯城县</span><span>2</span></a>
                    <a href="javascript:;"><span>平阴县</span><span>2</span></a>
                    <a href="javascript:;"><span>莒南县</span><span>3</span></a>
                    <a href="javascript:;"><span>沂南县</span><span>2</span></a>
                    <a href="javascript:;"><span>沂水县</span><span>2</span></a>
                    <a href="javascript:;"><span>临沭县</span><span>2</span></a>
                    <a href="javascript:;"><span>蒙阴县</span><span>3</span></a>
                    <a href="javascript:;"><span>兰陵县</span><span>3</span></a>
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
            <li class="yy_xian">
                <a href="javascript:;"><span>特色1</span><span></span></a>
                <a href="javascript:;"><span>特色2</span><span></span></a>
                <a href="javascript:;"><span>特色3</span><span></span></a>
            </li>
        </ul>
        <div class="cinema">
            <div class="default_show show">
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
                <a href="movie2.html">
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
        $("#news-slider").owlCarousel({
            items:3,
            itemsDesktop:[1199,3],
            itemsDesktopSmall:[980,3],
            itemsMobile:[750,3],
            pagination:false,
            navigationText:false,
            autoPlay:false
        });

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
