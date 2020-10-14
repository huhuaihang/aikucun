<?php

/**
 * @var $this \yii\web\View
 */

$this->registerJsFile('/js/zepto.js');
$this->registerJsFile('/js/touch.js');
$this->registerJsFile('/js/zepto.extend.js');
$this->registerJsFile('/js/zepto.ui.js');
$this->registerJsFile('/js/slider.js');

$this->title = '旅游';
?>
<style>
    body {overflow-x:hidden;}
</style>
<div class="box1">
    <div class="travel_head">
        <a class="go_back" href="javascript:void(0)" onClick="window.history.go(-1)"><img src="/images/backg.png"></a>
        <div class="search_bar">
            <div class="search_icon">
                <img src="/images/search.png" width="100%" height="100%" alt="search icon"/>
            </div>
            <input class="search" type="text" placeholder="目的地/景点/关键词" />
        </div>
    </div><!--about_head-->
    <!--第一部分-->
    <div class="tuijian clearfix">
        <div class="zuo">
            <div class="every_dayimg">
                <img src="/images/tr_11.jpg" width="100%" height="100%"/>
                <div class="evd_title">
                    <img src="/images/haohuo_03.jpg" width="100%" height="100%"/>
                </div>
            </div>
            <h3>观唐温泉国际度假村节假日门票成人票</h3>
            <p class="evd_tui">热卖推荐</p>
            <p class="evd_price"><span>¥</span>168</p>
        </div>
        <div class="you">
            <div class="dazhe_top">
                <img src="/images/tr_12.png" width="100%" height="100%"/>
                <div class="dz_hbimg">
                    <img src="/images/5zhe_03.png" width="100%" height="100%"/>
                </div>
            </div>
            <div class="hongbao_bto">
                <img src="/images/tr_13.jpg" width="100%" height="100%"/>
                <div class="dz_hbimg">
                    <img src="/images/6zhe_03.png" width="100%" height="100%"/>
                </div>
            </div>
        </div>
    </div>
    <!--第二部分-旅行研究所 -->
    <div style="margin-top: 0.14rem;">
        <div class="tuijian">
            <div class="sec_title">
                <h4>旅行研究所</h4>
                <a class="more_artical" href="#">全部文章 &gt;</a>
            </div>
        </div>
        <div class="advertise">
            广告位招租
            <!--<a href="#">
                <img src="#"/>
            </a>-->
        </div>
        <ul class="tuijian yanjiusuo">
            <li>
                <div class="yjs_txt">
                    <a href="#">啤酒、蛤蜊、玩海……盛夏来青岛是一种戒不掉的瘾！</a>
                    <p>境内游</p>
                    <span><font>1068</font>人看过</span>
                </div>
                <div class="yjs_img">
                    <a href="#"><img src="/images/lvyou_26.jpg" width="100%" height="100%"/></a>
                </div>
            </li>
            <li>
                <div class="yjs_txt">
                    <a href="#">假期防坑防骗指南</a>
                    <p>境内游</p>
                    <span><font>3606</font>人看过</span>
                </div>
                <div class="yjs_img">
                    <a href="#"><img src="/images/lvyou_29.jpg" width="100%" height="100%"/></a>
                </div>
            </li>
        </ul>

        <div class="tuijian lvyoutuan">
            <div id="slider"></div>
        </div>
    </div>
    <!--海外精选-->
    <div style="margin-top: 0.14rem;">
        <div class="tuijian">
            <div class="sec_title sec_title1">
                <h4>海外精选</h4>
                <p>端午特价</p>
            </div>
        </div>

        <div class="tuijian ">
            <div class="padd_bto">
                <div id="slider1"></div>
            </div>
            <div class="padd_bto1">
                <div id="slider2"></div>
            </div>
        </div>
    </div>
    <div>
        <div class="tuijian">
            <div class="sec_title sec_title2">
                <h4>猜你喜欢</h4>
            </div>
        </div>
        <ul class="lv_fenlei">
            <li class="lvfl_secect">全部</li>
            <li>周边游</li>
            <li>出境游</li>
        </ul>
        <div class="lvfl_detail">
            <ul class="lvfl_ddlist show">

                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_49.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂动植物园（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li >返劵10元</li>
                                <li class="none">今日可定</li>
                                <li class="none">快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_52.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>爱莱星空错觉艺术馆</h3>
                            <p class="pingfen">很好，4.7分<span> | 沂蒙路</span></p>
                            <ul class="yinxiang clearfix">
                                <li >返劵5元</li>
                                <li >今日可定</li>
                                <li >快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">8.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_54.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>沂蒙山银座天蒙旅游区</h3>
                            <p class="pingfen">很好，4.5分<span> | 费县</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li class="show">今日可定</li>
                                <li class="none">快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>43<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_56.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>马泉休闲园（3A）</h3>
                            <p class="pingfen">很好，4.3分<span> | 沂南 </span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li class="none">今日可定</li>
                                <li class="none">快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>20<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_60.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂广播电视塔</h3>
                            <p class="pingfen">很好，4.4分<span> | 兰山区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_58.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>竹泉村旅游度假村（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>70<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
            </ul>
            <ul class="lvfl_ddlist">
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_54.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>沂蒙山银座天蒙旅游区</h3>
                            <p class="pingfen">很好，4.5分<span> | 费县</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li class="show">今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>43<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_49.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂动植物园（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_52.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>爱莱星空错觉艺术馆</h3>
                            <p class="pingfen">很好，4.7分<span> | 沂蒙路</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵5元</li>
                                <li class="show">今日可定</li>
                                <li class="show">快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">8.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_54.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>沂蒙山银座天蒙旅游区</h3>
                            <p class="pingfen">很好，4.5分<span> | 费县</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li class="show">今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>43<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_56.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>马泉休闲园（3A）</h3>
                            <p class="pingfen">很好，4.3分<span> | 沂南 </span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>20<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_60.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂广播电视塔</h3>
                            <p class="pingfen">很好，4.4分<span> | 兰山区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_58.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>竹泉村旅游度假村（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>70<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
            </ul>
            <ul class="lvfl_ddlist">
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_58.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>竹泉村旅游度假村（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>70<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_49.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂动植物园（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_52.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>爱莱星空错觉艺术馆</h3>
                            <p class="pingfen">很好，4.7分<span> | 沂蒙路</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵5元</li>
                                <li class="show">今日可定</li>
                                <li class="show">快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">8.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_54.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>沂蒙山银座天蒙旅游区</h3>
                            <p class="pingfen">很好，4.5分<span> | 费县</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li class="show">今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>43<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_56.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>马泉休闲园（3A）</h3>
                            <p class="pingfen">很好，4.3分<span> | 沂南 </span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>20<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_60.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>临沂广播电视塔</h3>
                            <p class="pingfen">很好，4.4分<span> | 兰山区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>23<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <div class="lvfldd_img">
                            <img src="/images/lvyou_58.jpg" width="100%" height="100%"/>
                        </div>
                        <div class="lvfldd_txt">
                            <h3>竹泉村旅游度假村（4A）</h3>
                            <p class="pingfen">很好，4.7分<span> | 河东区</span></p>
                            <ul class="yinxiang clearfix">
                                <li class="show">返劵10元</li>
                                <li>今日可定</li>
                                <li>快速入园</li>
                            </ul>
                            <p class="jiage"><span class="renminbi">¥</span>70<span class="price_qi">起</span></p>
                            <span class="distance">13.3km</span>
                            <p class="consume_people"><span>13万</span>人消费</p>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div><!--box-->
<script>
    function page_init() {
        //创建slider组件
        var slider = $.ui.slider('#slider', {
            autoPlay:false,
            showArr:false,
            viewNum:2,
            content:[
                {
                    href: "#",
                    pic: "/images/lvyou_32.jpg",
                    title:"张家界核心景区张家界张家界",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_34.jpg",
                    title: "大理古城、丽江古城、无为寺",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_38.jpg",
                    title: "巴厘岛人间天堂巴厘岛人间天堂",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_39.jpg",
                    title: "九寨沟神农架风景名胜古迹",
                    price:"1588",
                    text:"起"
                }
            ]
        });

        var slider = $.ui.slider('#slider1', {
            autoPlay:false,
            showArr:false,
            viewNum:2,
            content:[
                {
                    href: "#",
                    pic: "/images/lvyou_38.jpg",
                    title:"张家界核心景区张家界张家界",
                    descr:"马来西亚升级国际四星交底书倒计时的撒几点睡",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_39.jpg",
                    title: "大理古城、丽江古城、无为寺",
                    descr:"赠送往返大巴全程机票倒计时的撒几点睡时的撒几点睡时的撒几点睡时的撒几点睡",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_34.jpg",
                    title: "巴厘岛人间天堂巴厘岛人间天堂",
                    descr:"马来西亚升级国际四星交底书倒计时的撒几点睡",
                    price:"1588",
                    text:"起"
                }, {
                    href: "#",
                    pic: "/images/lvyou_32.jpg",
                    title: "九寨沟神农架风景名胜古迹",
                    descr:"马来西亚升级国际四星交底书倒计时的撒几点睡",
                    price:"1588",
                    text:"起"
                }
            ]
        });

        var slider = $.ui.slider('#slider2', {
            autoPlay:false,
            showArr:false,
            viewNum:2,
            content:[
                {
                    href: "#",
                    pic: "/images/lvyou_42.jpg",
                }, {
                    href: "#",
                    pic: "/images/lvyou_44.jpg",
                }, {
                    href: "#",
                    pic: "/images/lvyou_42.jpg",
                }, {
                    href: "#",
                    pic: "/images/lvyou_44.jpg"
                }
            ]
        });

        var img_height = $('.ui-slider-item').children().children('img').height();
        $('#slider').css('height',img_height + 'px');

        //底部tab切换
        $('.lv_fenlei li').click(function(){
            var num=$(this).index();

            $(this).addClass('lvfl_secect').siblings('li').removeClass('lvfl_secect');
            $('.lvfl_detail .lvfl_ddlist').eq(num).addClass('show').siblings('ul').removeClass('show');
        });
    }
</script>
