<?php

use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var array $sub_tree_cate 所有分类
 * @var array $choicest 精选分类
 */

$this->registerJsFile('/js/swiper-3.2.5.min.js');
$this->registerJsFile('/js/ectouch.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->title = '全部分类';
?>
<style>
    body{background:#fff;}
</style>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">全部分类</div>
    </header>
    <div class="container">
        <div class="index_quanbu">
            <div class="con">
                <aside >
                    <div class="menu-left scrollbar-none" id="sidebar">
                        <ul>
                            <?php foreach ($sub_tree_cate as $first_value) {?>
                                <?php
                                if ($first_value['id'] == 0) {?>
                                    <li <?php if ($first_value['id'] == 0) {?>class="active"<?php }?>><?php echo $first_value['name'];?></li>
                                <?php }?>
                                <?php if ($first_value['id'] != 0) {?>
                                <li><?php echo $first_value['name'];?></li>
                                <?php }?>
                            <?php }?>
                        </ul>
                    </div>
                </aside>
                <section class="menu-right padding-all j-content" style="display:block">
                    <h5>精选</h5>
                    <ul>
                        <?php if (!empty($choicest)) {?>
                            <?php foreach ($choicest as $v) { ?>
                                <li class="w-3"><a href="<?php if ($v['url']) { echo Url::to($v['url']);}else{ echo Url::to(['/h5/goods/list', 'search_category' => $v['id']]);};?>"><span class="span1_img"><?php if (!empty($v['image'])) {?><img src="<?php echo Yii::$app->params['upload_url'] . $v['image'];?>_40x40"><?php }?></span><span><?php echo $v['name'];?></span></a></li>
                            <?php }?>
                        <?php }?>
                    </ul>
                </section>
                <?php foreach ($sub_tree_cate as $k => $m) {?>
                    <?php if ($k > 0) { ?>
                        <section class="menu-right padding-all j-content" style="display:none">
                            <?php foreach ($m['menu'] as $k1 => $v1) { ?>
                                <h5><a href="<?php if ($v1['url']) { echo Url::to($v1['url']);}else{ echo Url::to(['/h5/goods/list', 'search_category' => $v1['id']]);};?>"><?php echo $v1['name']?></a></h5>
                                <ul>
                                    <?php foreach ($v1['menu'] as $k2 => $v2) { ?>
                                        <li class="w-3"><a href="<?php if ($v2['url']) { echo Url::to($v2['url']);}else{ echo Url::to(['/h5/goods/list', 'search_category' => $v2['id']]);};?>"><span class="span1_img"><?php if (!empty($v2['image'])) {?><img src="<?php echo Yii::$app->params['upload_url'] . $v2['image'];?>_40x40"><?php }?></span><span><?php echo $v2['name'];?></span></a></li>
                                    <?php }?>
                                </ul>
                            <?php }?>
                        </section>
                    <?php }?>
                <?php }?>
            </div>
        </div><!--index_quanbu-->
        <?php echo $this->render('../layouts/_bottom_nav');?>
    </div>
</div><!--box-->
<script type="text/javascript">
    //设置cookie
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires;
    }
    function clearHistroy(){
        setCookie('ECS[keywords]', '', -1);
        document.getElementById("search_histroy").style.visibility = "hidden";
    }
    function page_init() {
        $('#sidebar ul li').click(function(){
            $(this).addClass('active').siblings('li').removeClass('active');
            var index = $(this).index();
            $('.j-content').eq(index).show().siblings('.j-content').hide();
        });
    }
</script>
