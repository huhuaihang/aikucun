<?php
/**
 * @var $this \yii\web\View
 */

$this->registerCssFile('/style/jiudian.css');
$this->registerCssFile('/font/iconfont.css');
$this->registerJsFile('/js/TouchSlide.1.1.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/date.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/jquery.range-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/jquery.downCount.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/jquery-labelauty.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/moment.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/bootstrap-datetimepicker.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = '酒店';
?>
<div id="slideBox" class="slideBox">
    <div class="bd">
        <ul>
            <li>
                <a class="pic" href="#"><img src="/images/ba1.png" /></a>
            </li>
            <li>
                <a class="pic" href="#"><img src="/images/ba1.png" /></a>
            </li>
            <li>
                <a class="pic" href="#"><img src="/images/ba1.png" /></a>
            </li>
        </ul>
    </div>
    <div class="hd">
        <ul></ul>
    </div>
</div>
<div id="wrap">
    <div id="tit">
        <span class="select">国内</span>
        <span>钟点房</span>
    </div>
    <div class="clear"></div>
    <div id="con">
        <div class="con show">
            <ul class="jiudian-x jiudian-x1">
                <li>
                    <div>目的地</div>
                    <div class="ct">临沂</div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>时间</div>
                    <div class="row select-time">
                        <span class="time entertime"></span>
                        <input type="text" class="input-enter none">
                        <span>入住</span>
                        <br />
                        <span class="time leavetime"></span>
                        <input type="text" class="input-leave none">
                        <span>离店</span>
                        <span class="night">共1晚</span>
                    </div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>搜索</div>
                    <div class="fujin-dian">我的附近<span class="sosuo1">酒店/地名/关键词</span></div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>星级价格</div>
                    <div class="jiagexuan">不限价格，不限星级</div>
                    <i class="iconfont icon-202"></i>
                </li>
                <a href="#" class="chazhao">查找酒店</a>
            </ul>
            <ul class="scdd scdd1">
                <li>
                    <a href="#">
                        <p><i class="iconfont icon-shoucang"></i>住过/收藏</p>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <p><i class="iconfont icon-dingdan dingdan2"></i>我的订单</p>
                    </a>
                </li>
            </ul>
            <div class="qiang">
                <div class="qiang1">
                    <span class="qiang1-1">限时抢购</span>
                    <ul class="countdown daojishi">
                        <li> <span>距离结束</span>
                        </li>
                        <li> <span class="hours">00</span>
                        </li>
                        <li class="seperator">:</li>
                        <li> <span class="minutes">00</span>
                        </li>
                        <li class="seperator">:</li>
                        <li> <span class="seconds">00</span>
                        </li>
                    </ul>
                    <img src="/images/b4.png" class="daojishi2" alt="" onclick="location='URL'">
                </div>
                <div class="qiang2">
                    <div class="qiang2-1" onclick="location='URL'">
                        <div>
                            <p>特价酒店</p>
                            <p>商家活动精选</p>
                        </div>
                        <div>
                            <img src="/images/b3.png" alt="">
                        </div>
                    </div>
                    <div class="fenge"></div>
                    <div class="qiang2-2" onclick="location='URL'">
                        <div>
                            <p>特价酒店</p>
                            <p>商家活动精选</p>
                        </div>
                        <div>
                            <img src="/images/b3.png" alt="">
                        </div>
                    </div>
                </div>
                <img src="/images/b2.png" class="hezuo" alt="" onclick="location='URL'">
                <p class="hezuo2">合作酒店</p>
            </div>
        </div>
        <div class="con">
            <ul class="jiudian-x jiudian-x1">
                <li>
                    <div>目的地</div>
                    <div class="ct">临沂</div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>时间</div>
                    <div class="iDate1 date1">
                        <input type="text">
                        <button type="button" class="addOn"></button>
                    </div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>搜索</div>
                    <div class="fujin-dian">我的附近<span class="sosuo1">酒店/地名/关键词</span></div>
                    <i class="iconfont icon-202"></i>
                </li>
                <li>
                    <div>星级价格</div>
                    <div class="jiagexuan">不限价格，不限星级</div>
                    <i class="iconfont icon-202"></i>
                </li>
                <a href="#" class="chazhao">查找酒店</a>
            </ul>
            <ul class="scdd scdd1">
                <li>
                    <a href="#">
                        <p><i class="iconfont icon-shoucang"></i>住过/收藏</p>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <p><i class="iconfont icon-dingdan dingdan2"></i>我的订单</p>
                    </a>
                </li>
            </ul>
            <div class="qiang">
                <div class="qiang1">
                    <span class="qiang1-1">限时抢购</span>
                    <ul class="countdown daojishi">
                        <li> <span>距离结束</span>
                        </li>
                        <li> <span class="hours">00</span>
                        </li>
                        <li class="seperator">:</li>
                        <li> <span class="minutes">00</span>
                        </li>
                        <li class="seperator">:</li>
                        <li> <span class="seconds">00</span>
                        </li>
                    </ul>
                    <img src="/images/b4.png" class="daojishi2" alt="">
                </div>
                <div class="qiang2">
                    <div class="qiang2-1">
                        <div>
                            <p>特价酒店</p>
                            <p>商家活动精选</p>
                        </div>
                        <div>
                            <img src="/images/b3.png" alt="">
                        </div>
                    </div>
                    <div class="fenge"></div>
                    <div class="qiang2-2">
                        <div>
                            <p>特价酒店</p>
                            <p>商家活动精选</p>
                        </div>
                        <div>
                            <img src="/images/b3.png" alt="">
                        </div>
                    </div>
                </div>
                <img src="/images/b2.png" alt="" class="hezuo">
                <p class="hezuo2">合作酒店</p>
            </div>
        </div>
    </div>
</div>
<div class="map">
    <div class="map-t">
        <i class="iconfont icon-chuyidong map-guan dtkg"></i>
        <div style="float: left;">
            <i class="iconfont icon-sousuo sousuo-ic"></i>
            <input type="text" placeholder="位置/酒店名/关键词">
        </div>
    </div>
    <div style="height:2rem;"></div>
    <div class="map-b">
        当前位置
    </div>
    <ul class="map-l">
        <li>
            <a href="#">临沂，兰山区，上海路</a>
        </li>
    </ul>
    <div class="map-b">
        最近访问
    </div>
    <ul class="map-l">
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
    </ul>
    <div class="map-b">
        热门城市
    </div>
    <ul class="map-r">
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
    </ul>
    <div class="map-b">
        A
    </div>
    <ul class="map-l">
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
    </ul>
</div>

<div class="map1">
    <div class="map-t">
        <i class="iconfont icon-chuyidong map-guan dtkg"></i>
        <div style="float: left;">
            <i class="iconfont icon-sousuo sousuo-ic"></i>
            <input type="text" placeholder="位置/酒店名/关键词">
        </div>
    </div>
    <div style="height:2rem;"></div>
    <div class="map-b">
        当前位置
    </div>
    <ul class="map-l">
        <li>
            <a href="#">临沂，兰山区，上海路</a>
        </li>
    </ul>
    <div class="map-b">
        最近访问
    </div>
    <ul class="map-l">
        <li>
            <a href="#">曼谷</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
    </ul>
    <div class="map-b">
        热门城市
    </div>
    <ul class="map-r">
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
        <li>
            <a href="#">
                北京
            </a>
        </li>
    </ul>
    <div class="map-b">
        A
    </div>
    <ul class="map-l">
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
        <li>
            <a href="#">临沂</a>
        </li>
        <li>
            <a href="#">重庆</a>
        </li>
    </ul>
</div>

<div class="fujin">
    <div class="fujin-t">
        <i class="iconfont icon-chuyidong map-guan dtkg"></i>
        <div style="float: left;">
            <i class="iconfont icon-sousuo sousuo-ic"></i>
            <input type="text" placeholder="位置/酒店名/关键词">
        </div>
    </div>
    <div style="height:2rem;"></div>
    <div class="fujin-b fujin-l-ls">
        <i class="iconfont icon-lishi lishi">历史记录</i>
        <i class="iconfont icon-shanchu jishiqing qk">清空</i>
    </div>
    <ul class="fujin-l fujin-l-ls">
        <li>
            <a href="#">云瀑洞天景区</a>
        </li>
        <li>
            <a href="#">云瀑洞天景区</a>
        </li>
        <li>
            <a href="#">云瀑洞天景区</a>
        </li>
        <li>
            <a href="#">云瀑洞天景区</a>
        </li>
        <li>
            <a href="#">智圣汤泉旅游度假村</a>
        </li>
    </ul>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-hot remen">热门</i>
            <span class="kg zk"><p style="float: left;">展开</p><i class="iconfont icon-unfold zhankai-kg"></i></span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-tesefuwu tese">特色主题</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-pinpaixuanchuan pinpai">品牌</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-hot xz">行政区/商圈</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-dabiaotijingdian jingdian">景点</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-yiyuanpaidui yiyuan">医院</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-xuexiao xuxiao">高校</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
    <div claass="fujianzhankai">
        <div class="fujin-b">
            <i class="iconfont icon-feiji jichang">机场车站</i>
            <span class="kg zk">
                <p style="float: left;">展开</p>
                <i class="iconfont icon-unfold zhankai-kg"></i>
            </span>
        </div>
        <ul class="fujin-l zhankai">
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">云瀑洞天景区</a>
            </li>
            <li>
                <a href="#">智圣汤泉旅游度假村</a>
            </li>
        </ul>
    </div>
</div>

<div class="jiage">
    <i class="iconfont icon-chuyidong jiage-guan jgg"></i>
    <div class="jiage1">
        <ul class="dowebok">
            <li><input type="checkbox" name="checkbox" data-labelauty="经济型"></li>
            <li><input type="checkbox" name="checkbox" data-labelauty="舒适/三星"></li>
            <li><input type="checkbox" name="checkbox" data-labelauty="高档/四星"></li>
            <li><input type="checkbox" name="checkbox" data-labelauty="豪华/五星"></li>
        </ul>
        <div class="huakuai">
            <input class="range-slider" type="hidden" value="0,300"/>
        </div>
        <a href="#" class="chazhao jiageguan">完成</a>
    </div>
</div>

<script>
    function page_init() {
        TouchSlide({
            slideCell:"#slideBox",
            titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
            mainCell:".bd ul",
            effect:"leftLoop",
            autoPage:true,//自动分页
            autoPlay:true //自动播放
        });

        $(".map").height($(window).height());
        $(".map1").height($(window).height());
        $(".fujin").height($(window).height());
        $(".jiage").height($(window).height());
        $('#tit span').click(function() {
            var i = $(this).index();//下标第一种写法
            $(this).addClass('select').siblings().removeClass('select');
            $('.con').eq(i).show().siblings().hide();
        });

        $(".ct").click(function() {
            $(".map").show();
        });
        $(".map-l li,.map-r li,.map-guan").click(function() {
            $(".map").hide();
        });

        $(".ct1").click(function() {
            $(".map1").show();
        });
        $(".map-l li,.map-r li,.map-guan").click(function() {
            $(".map1").hide();
        });
        $(".fujin-dian").click(function() {
            $(".fujin").show();
        });
        $(".map-guan,.fujin-l li").click(function() {
            $(".fujin").hide();
        });
        $(".jishiqing").click(function() {
            $(".fujin-l-ls").hide();
        });
        $(".jiagexuan").click(function() {
            $(".jiage").show();
        });
        $(".jiage-guan,.jiageguan").click(function() {
            $(".jiage").hide();
        });

        $(".kg").click(function() {
            var kg = $(this).parent().parent();
            if(kg.find('.zhankai-kg').hasClass('icon-unfold')){
                kg.find('.zhankai-kg').removeClass('icon-unfold').addClass('icon-packup');
                kg.find("p").html("收起");
                kg.find('ul').removeClass('zhankai');
            }else{
                kg.find('.zhankai-kg').removeClass('icon-packup').addClass('icon-unfold');
                kg.find("p").html("展开");
                kg.find('ul').addClass('zhankai');
            };
        });
        $('.select-time').hotelDate();

        //倒计时
        $('.countdown').downCount({
            date: '2018/7/25 10:03:00',//更改时间设置倒计时
            offset:8,
            milliShow:!0
        }, function () {
            console.log('倒计时结束!');
        });

        $('.range-slider').jRange({
            from: 0,
            to: 300,
            step: 50,
            scale: [0,50,100,150,200,250,300],
            format: '%s',
            width: 320,
            showLabels: false,
            showScale: true,
            snap: true
        });

        $(':input').labelauty();

        if($(".iDate1.date1").length>0){
            $(".iDate1.date1").datetimepicker({
                locale:"zh-cn",
                format:"MM-DD",
                dayViewHeaderFormat:"YYYY年 MMMM"
            });
        };
    }

    window.onresize = function(){
        $(".map").height($(window).height());
        $(".map1").height($(window).height());
        $(".fujin").height($(window).height());
        $(".jiage").height($(window).height());
    }
</script>
