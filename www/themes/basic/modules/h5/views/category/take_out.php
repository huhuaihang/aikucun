<?php
/**
 * @var $this \yii\web\View
 */

$this->title = '外卖';
?>
<style>
    body {background-color:#FFF;}
</style>
<div class="box">
    <div class="food_head">
        <span><img src="/images/food_03.png" class="img1"></span><span class="text">香港路</span>
        <a href="#"><span class="span2"><input type="text" placeholder="输入商家名,品类或商圈"></span></a>
    </div><!--food_head-->
    <div class="clear"></div>
    <div class="food">
        <div class="food_qie">
            <div class="tab-menu12">
                <div class="food_one">
                    <ul class="ul1">
                        <li class="checked"><span>美食</span></li>
                        <li><span>酒店</span></li>
                        <li><span>玩乐</span></li>
                        <li><span>全部</span></li>
                    </ul>
                </div><!--food_one-->
            </div>
            <div class="clear"></div>
            <div class="con1">
                <div class="box_div">
                    <div class="two">
                        <ul class="ul2">
                            <li class="checked">热门</li>
                            <li>小吃快餐</li>
                            <li>面包甜点</li>
                            <li>鲁菜</li>

                            <li>川菜</li>
                            <li>东北菜</li>
                            <li>浙江菜</li>
                            <li>湘菜</li>
                        </ul>
                    </div><!--two-->
                    <div class="clear"></div>
                    <div class="con2">
                        <div class="box_div2" style="font-size:0.2rem;">
                            <a href="#">
                                <dl>
                                    <dt><img src="/images/food_07.jpg"></dt>
                                    <dd class="dd1">阿达姆牛肉拉面</dd>
                                    <dd class="dd2"><span class="span1"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy3.png"><img src="/images/starsy2.png">&nbsp;&nbsp;月售269</span> <span class="span2">30分钟|2.0km</span></dd>
                                    <dd class="dd3">起送￥20 | 配送￥5 | 人均￥23</dd>
                                    <dd class="dd4"><span>领</span>可领3元代金券</dd>
                                    <dd class="dd5"><span>返</span>实际支付30元返10元商家代金券(使用规支付30元返10元商家代金券(使用规则...)</dd>
                                </dl>
                            </a>
                            <div class="clear"></div>
                            <a href="#">
                                <dl>
                                    <dt><img src="/images/9.jpg"></dt>
                                    <dd class="dd1">经开全羊庄园</dd>
                                    <dd class="dd2"><span class="span1"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy3.png"><img src="/images/starsy2.png">&nbsp;&nbsp;月售4</span> <span class="span2">44分钟|1.2km</span></dd>
                                    <dd class="dd3">起送￥20 | 配送￥5</dd>
                                </dl>
                            </a>
                            <div class="clear"></div>
                            <a href="#">
                                <dl>
                                    <dt><img src="/images/food_10.jpg"></dt>
                                    <dd class="dd1">惟四龙虾</dd>
                                    <dd class="dd2"><span class="span1"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy1.png"><img src="/images/starsy3.png"><img src="/images/starsy2.png">&nbsp;&nbsp;月售32</span> <span class="span2">54分钟|9.0km</span></dd>
                                    <dd class="dd3">起送￥100 | 配送￥15</dd>
                                    <dd class="dd6"><span>减</span>可领3元代金券</dd>
                                    <dd class="dd7"><span>首</span>实际支付30元返10元商家代金券(使用规支付30元返10元商家代金券(使用规则...)</dd>
                                </dl>
                            </a>
                            <div class="clear"></div>
                        </div><!--box2-->
                        <div class="box_div2"  style="font-size:0.2rem;">2</div>
                        <div class="box_div2"  style="font-size:0.2rem;">我3是好人</div>
                        <div class="box_div2"  style="font-size:0.2rem;">我是4好人</div>
                        <div class="box_div2" style="font-size:0.2rem;">我5是好人</div>
                        <div class="box_div2"  style="font-size:0.2rem;">我6是好人</div>
                        <div class="box_div2"  style="font-size:0.2rem;">我7是好人</div>
                        <div class="box_div2"  style="font-size:0.2rem;">我8是好人</div>
                    </div><!--con2-->
                </div><!--box_div-->
                <div class="box_div">体育体育体育体育体育体育体育体育体育体育体育体育体育体育体育</div>
                <div class="box_div">娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐娱乐</div>
                <div class="box_div">娱乐娱乐</div>
            </div><!--con1-->
        </div><!--food_qie/-->
    </div><!--food-->
    <div class="Y_bottom">
        <a href="#">
            <dl>
                <dt><img src="/images/bottom_031.png"></dt>
                <dd>附近</dd>
            </dl>
        </a>
        <a href="Yindex.html">
            <dl>
                <dt><img src="/images/bottom_051.png"></dt>
                <dd>主页</dd>
            </dl>
        </a>
        <a href="dingdan.html">
            <dl>
                <dt><img src="/images/bottom_071.png"></dt>
                <dd>订单</dd>
            </dl>
        </a>
        <a href="#">
            <dl>
                <dt><img src="/images/bottom_091.png"></dt>
                <dd>我</dd>
            </dl>
        </a>
    </div><!--Y_bottom-->
</div><!--box-->
<script>
    function page_init() {
        //第一个
        $(".con1 .box_div:not(:first)").hide();//除了第一个p元素，其他的隐藏
        //或者$(".sub p:gt(0))").hide();
        $(".food_qie .ul1 li").click(function(){
            $(this).addClass("checked").siblings().removeClass("checked"); //点击的元素获取样式，其他兄弟元素删除样式
            index=$(this).index();//获取索引值
            $(".con1 .box_div").eq(index).show().siblings().hide();//点击的显示，其他的兄弟元素隐藏
        })

        //第一个
        $(".con2 .box_div2:not(:first)").hide();//除了第一个p元素，其他的隐藏
        //或者$(".sub p:gt(0))").hide();
        $(".food_qie .ul2 li").click(function(){
            $(this).addClass("checked").siblings().removeClass("checked"); //点击的元素获取样式，其他兄弟元素删除样式
            index=$(this).index();//获取索引值
            $(".con2 .box_div2").eq(index).show().siblings().hide();//点击的显示，其他的兄弟元素隐藏
        })
    }
</script>
