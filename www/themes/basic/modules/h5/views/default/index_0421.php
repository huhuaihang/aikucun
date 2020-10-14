<?php

use app\models\System;
use app\widgets\AdWidget;
use yii\helpers\Url;

/**

 * @var $this \yii\web\View
 * @var $goods_list \app\models\Goods[]
 * @var $notice_list \app\models\Notice[]
 */

$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerCssFile('/style/banner.css');


$this->title = System::getConfig('site_index_title', '主页');
?>
<div class="box">
    <header class="mall-header mall-header-index">

        <div class="mall-header-title">
            <a href="<?php echo Url::to(['/h5/default/search']);?>">输入商家名称或品类</a>
            <p>云淘帮</p>
        </div>
    </header>
    <div class="container">
        <div class="Y_banner">
            <div class="block_home_slider">
                <div id="home_slider" class="flexslider">
                    <ul class="slides">
                        <?php AdWidget::begin(['lid' => 1]);?>
                        {foreach $ad_list as $ad}
                        <li>
                            <div class="slide">
                                <a href="<?php echo Url::to(['/site/da']);?>?id={$ad['id']}"><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}" /></a>
                            </div>
                        </li>
                        {/foreach}
                        <?php AdWidget::end();?>
                    </ul>
                    <ol class="flex-control-nav flex-control-paging"></ol>
                </div><!--home_slider-->
            </div><!--block_home_slider-->
        </div><!--Y_banner-->
        <div class="clear"></div>
        <div class="Yindex" >
            <p class="vip">- 会员大礼包 -</p>
            <div class="Y_div2">
                <?php AdWidget::begin(['lid' => 2]);?>
                {foreach $ad_list as $ad}
                <a href="{$ad['url']}">
                    <dl>
                        <dt><img src="<?php echo Yii::$app->params['upload_url'];?>{$ad['img']}"></dt>
                        <dd>{$ad['txt']}</dd>
                    </dl>
                </a>
                {/foreach}
                <?php AdWidget::end();?>
                <div id="score_closed">
                    <div class="Yindex_layer_box">
                        <div class="Yindex_layer">
                            <div class="div1">功能暂时未开放</div>
                            <div class="div2">确定</div>
                        </div>
                    </div><!--Yindex_layer-->
                    <div class="huiyuan_zhezhao"></div>
                </div>
                <style>
                    #score_closed {display:none;}
                    .Yindex_layer_box{max-width: 750px;width:100%; position: fixed;top:4.5rem;z-index:999;}
                    .Yindex_layer{width:85%;height: 3rem;margin:0 auto; background: #fff; text-align: center;border-radius: 4px;}
                    .Yindex_layer .div1{font-size:0.36rem ;color: #212121;padding: 0.72rem 0;border-bottom: 1px solid #F0F0F0;}
                    .Yindex_layer .div2{margin-top:0.33rem;font-size: 0.32rem;color: #FF6951;}
                </style>
            </div><!--Y_div2-->
            <!--<div id="broadcast" class="bar" name="giftactive">-->
            <!--<div style="float:left"><img style=" width:.4rem" src="/images/gonggao.png"></div>-->
            <!--<div id="demo" style="float:left;overflow:hidden;height:.8rem;line-height:.8rem; width:70%">-->
            <!--<ul class="mingdan" id="holder">-->
            <!--<?php foreach ($notice_list as $notice) {?>-->
            <!--<li><a href="<?php echo Url::to(['/h5/notice/list']);?>"><?php echo $notice['title'];?></a></li>-->
            <!--<?php }?>-->
            <!--</ul>-->
            <!--</div>-->
            <!--<div style="float:right"><a href="<?php echo Url::to(['/h5/notice/list']);?>"><img style=" width:1.2rem" src="/images/chakangengduo.png"><a/></div>-->
            <!--</div>-->

            <div class="clear"></div>
            <div class="Y_div4 vip_div">
                <h4 class="biaoti">
                    <img src="/images/jingpinbiaoti_1.png">
                </h4>
                <?php foreach ($goods_list as $goods) {?>
                    <dl>
                        <a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods['id']]);?>">
                            <dt><img src="<?php echo Yii::$app->params['upload_url'] . $goods['main_pic'];?>"></dt>
                            <div class="s-list">
                                <dd class="dd1"><span class="span1"><?php echo $goods['title'];?></span></dd>
                                <dd class="dd3 s-list-t">精品甄选 <?php echo $goods['desc']?></dd>
                                <dd class="dd3">

                                    <div class="dd3_s">
                                        <span class="span1"><?php echo $goods['price'];?></span>
                                    </div>
                                    <?php if ($goods['is_pack'] != 1) {?>
                                        <div class="commission">
                                            <!--<p>分享赚¥<?php echo $goods['share_commission'];?><span class="commission_fen">分享</span></p>-->
                                            <?php if ($goods['is_pack'] != 1) {?>
                                                <p>自购省¥<?php echo $goods['self_money'];?></p>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                </dd>
                            </div>
                        </a>
                        <!--<div class="s-list-r">-->
                        <!--<a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods['id']]);?>"><img src="/images/goumaianniu.png" alt=""></a>-->
                        <!--</div>-->
                    </dl>
                <?php }?>
            </div>
            <!--<div class="Y_div4 vip_div">-->
            <!--<h4 class="biaoti">-->
            <!--<img src="/images/jingpinbiaoti_1.png">-->
            <!--</h4>-->
            <!--<?php foreach ($goods_list as $goods) {?>-->
            <!--<dl>-->
            <!--<a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods['id']]);?>">-->
            <!--<dt><img src="<?php echo Yii::$app->params['upload_url'] . $goods['main_pic'];?>"></dt>-->
            <!--<div class="s-list">-->
            <!--<dd class="dd1"><span class="span1"><?php echo $goods['title'];?></span></dd>-->
            <!--<dd class="dd3 s-list-t">精品甄选 来自牛蒡之乡</dd>-->
            <!--<dd class="dd3">-->
            <!--<span class="span1"><?php echo $goods['price'];?></span>-->
            <!--<?php if ($goods['is_pack'] != 1) {?>-->
            <!--<div class="commission"><p>分享赚¥<?php echo $goods['share_commission'];?><span class="commission_fen">分享</span></p></div>-->
            <!--<?php }?>-->
            <!--</dd>-->
            <!--</div>-->
            <!--</a>-->
            <!--&lt;!&ndash;<div class="s-list-r">&ndash;&gt;-->
            <!--&lt;!&ndash;<a href="<?php echo Url::to(['/h5/goods/view', 'id' => $goods['id']]);?>"><img src="/images/goumaianniu.png" alt=""></a>&ndash;&gt;-->
            <!--&lt;!&ndash;</div>&ndash;&gt;-->
            <!--</dl>-->
            <!--<?php }?>-->
            <!--</div>-->
        </div>
        <?php echo $this->render('../layouts/_bottom_nav');?>
    </div>
    <!--<div class="kePublic" style="display: none;">-->
    <!--<div class="gb_resLay clearfix">-->
    <!--<div class="bdsharebuttonbox">-->
    <!--<ul class="gb_resItms">-->
    <!--<li> <a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin"></a>微信好友 </li>-->
    <!--<li> <a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq" ></a>QQ好友 </li>-->
    <!--<li> <a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone" ></a>QQ空间 </li>-->
    <!--<li> <a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina" ></a>新浪微博 </li>-->
    <!--<li> <a title="分享到朋友圈" href="#" class="bds_pengyou" data-cmd="sns_icon" ></a>朋友圈</li>-->
    <!--</ul>-->
    <!--</div>-->
    <!--<div class="clear"></div>-->
    <!--<div class="gb_res_t"><span>取消</span><i></i></div>-->
    <!--</div>-->
    <!--</div>-->
    <!--kePublic-->
</div><!--box-->

<script>

    function AutoScroll(obj) {
        $(obj).find("ul:first").animate({
                marginTop: "-22px"
            },
            500,
            function() {
                $(this).css({
                    marginTop: "0px"
                }).find("li:first").appendTo(this);
            });
    }
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
        $('.Yindex .Y_div2 a:contains("积分专区")').attr('src', 'javascript:void(0)').click(function () {
            $('#score_closed').show();
            $(".huiyuan_zhezhao").css('height', $(window).height() + 'px').show();
            $(".Yindex_layer_box").show();
            $(".Yindex_layer .div2").click(function(){
                $(".huiyuan_zhezhao").hide();
                $(".Yindex_layer_box").hide();
                $('#score_closed').hide();
            });
            return false;
        });
        $(document).ready(function() {
            setInterval('AutoScroll("#demo")', 2500)
        });
//        $(".commission_fen").click(function(){
//            $(".kePublic").show();
//        });
//        $(".gb_res_t span").click(function(){
//            $(".kePublic").hide();
//        });

    }

</script>
