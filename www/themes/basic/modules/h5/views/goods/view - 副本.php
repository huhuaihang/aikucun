<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use app\models\City;
use app\models\GoodsComment;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this \yii\web\View
 * @var $goods \app\models\Goods
 * @var $gav_map array [aid => \app\models\GoodsAttrValue[]]
 * @var $sku_list []
 * @var $city_data []
 * @var $gav_list \app\models\GoodsAttrValue[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);

$this->registerCssFile('/style/banner.css');
$this->registerJsFile('/js/jquery.flexslider-min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/hzw-city-picker.min.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/fs_forse.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('//res.wx.qq.com/open/js/jweixin-1.2.0.js', ['postion' => View::POS_HEAD]);

$this->title = $goods->title;
?>
<style>
    body {overflow-x:hidden}
</style>
<div class="box">
    <div class="product_details_banner">
        <div class="product_head">
            <a href="<?php echo Url::to(['/h5']);?>"><p class="p1"><img src="/images/11_1.png"></p></a>
            <span class="span1"><img src="/images/dingdan_03.png"></span>
            <!--<span id="fav_goods" class="span2"><img src="/images/shoucang_01.png"></span>-->
            <!--<span class="span3"><a href="<?php echo Url::to(['/h5/cart/index']);?>"><img src="/images/gouwuche_bai.png"></a></span>-->
        </div> <!--product_head-->
        <div class="block_home_slider">
            <div id="home_slider" class="flexslider">
                <ul class="slides">
                    <?php foreach ($goods->getDetailPicList() as $pic) {?>
                        <li>
                            <div class="slide">
                                <img src="<?php echo Yii::$app->params['upload_url'], $pic;?>" alt="" />
                            </div>
                        </li>
                    <?php }?>
                </ul>
            </div><!--home_slider-->
        </div><!--block_home_slider-->
    </div><!--product_details_banner-->
    <div class="clear"></div>
    <div class="product_details">
        <div class="div1">
            <p class="p1"><?php echo Html::encode($goods->title);?></p>
            <p class="p2"><?php echo Html::encode($goods->desc);?></p>
        </div><!--div1-->
        <div class="div2">
            <div class="div2_f">
                <p class="p1"><span class="span1 price">￥<?php echo $goods->price;?></span><span class="span2 old_price" style="display:none">原价:￥<?php echo $goods->price;?></span></p>
                <div class="commission commission_1"><p class="div2_p1">自购省¥<?php echo $goods->share_commission_value * $self_buy_ratio / 100;?></p></div>
            </div>
            <div class="div2_p2">
                <div class="a1"><?php $city = City::findByCode($goods->shop->area);echo $city->address()[0]; ?> 至</div>
                <div class="a2">
                    <input type="text" readonly="readonly" id="cityChoice" value="山东省" class="form_input">
                </div><!--a2-->
                <div class="a3">运费：
                    <select class="express"><option>无物流请联系客服</option></select>
                </div>
            </div><!--div2_p2-->
        </div><!--div2-->
        <div class="clear"></div>
        <div class="div3">
            <?php foreach ($gav_map as $aid => $goods_attr_values) {
                /** @var \app\models\GoodsAttrValue[] $goods_attr_values */
                ?>
                <div class="div3_p1_color">
                    <div class="p1" data-value="<?php echo $goods_attr_values[0]->goods_attr->name;?>"><?php echo $goods_attr_values[0]->goods_attr->name;?>:</div>
                    <?php foreach ($goods_attr_values as $key=>$val) { ?>
                        <p class="sku<?php if (count($sku_list) ==1) {?> change_color<?php }?>" data-key="<?php echo $val['id'];?>" data-name="<?php echo $val['value'];?>" data-image="<?php echo Yii::$app->params['upload_url'].$val['image'];?>">
                            <?php if (!empty($val['image'])) {?><i><img src="<?php echo Yii::$app->params['upload_url'].$val['image'];?>"></i><?php }?>&nbsp;<span><?php echo $val['value'];?> </span>&nbsp;</p>
                    <?php }?>
                </div><!--div3_p1-->
            <?php }?>
            <div class="div3_p2">
                <div class="p2_div1">数量:</div>
                <div class="p2_div2">
                    <!--演示内容开始-->
                    <div class="p_number">
                        <div class="f_l add_chose">
                            <a class="reduce" onClick="setAmount.reduce('#qty_item_1')" href="javascript:void(0)">
                                -</a>
                            <input type="text" name="qty_item_1" value="1" id="qty_item_1" onKeyUp="setAmount.modify('#qty_item_1')" class="text form_input amount_value" />
                            <a class="add" onClick="setAmount.add('#qty_item_1')" href="javascript:void(0)">
                                +</a>
                        </div>
                    </div>
                    <!--演示内容结束-->
                </div> <!--div2-->
            </div><!--div3_p2-->
            <div class="product_details_nuber">
                <span class="span1">库存:</span>
                <span class="span2 stock"><?php if (count($sku_list) == 1) { echo $sku_list[0]['stock'];}else{ echo $goods->stock;}?></span>
            </div>
        </div><!--div3-->
        <?php if (!empty($gav_list)) {?>
            <div class="product_details_table">
                <div class="specification">商品属性</div>
                <table width="100%" cellspacing="0">
                    <?php foreach ($gav_list as $gav) { ?>
                        <tr>
                            <td class="td1"><?php echo Html::encode($gav->goods_attr->name);?></td>
                            <td class="td2"><?php echo Html::encode($gav->value);?></td>
                        </tr>
                    <?php }?>
                </table>
            </div><!--product_details_table-->
        <?php }?>
        <?php $comment = GoodsComment::find()->where(['gid' => $goods->id, 'status' => GoodsComment::STATUS_SHOW])->orderBy('create_time DESC')->one();?>
        <?php if (!empty($comment)) {/** @var GoodsComment $comment */?>
            <div class="div4_box">
                <div class="p1">商品评价</div>
                <div class="div4">
                    <div class="p2">
                        <span class="left"><?php echo preg_replace('/^(\d{3})(\d{4})(\d{4})$/', '$1****$3', $comment->user->mobile);?></span>
                        <span class="right"><?php echo Yii::$app->formatter->asDate($comment->create_time);?></span>
                    </div>
                    <div class="p3">
                        <p class="left"><?php if (empty($comment->content)) {
                                echo '<i>此用户没有留下任何评价。</i>';
                            } else {
                                echo Html::encode($comment->content);
                            }?></p>
                    </div>
                    <ul class="thumbnails gallery">
                        <?php foreach ($comment->getImgList() as $img) {?>
                            <li class="span3">
                                <a href="<?php echo Yii::$app->params['upload_url'], $img;?>">
                                    <img src="<?php echo Yii::$app->params['upload_url'], $img;?>_55x55" alt="" />
                                </a>
                            </li>
                        <?php }?>
                    </ul>
                </div><!--div4-->
                <a href="<?php echo Url::to(['/h5/goods/comment', 'gid' => $goods->id]);?>"><div class="div5_more">查看更多</div></a>
                <div class="clear"></div>
            </div><!--div4_box-->
        <?php }?>
        <div class="div4_box"><div class="div4">
                <div class="div4_content"><?php echo $goods->content;?></div>
            </div></div>
        <div class="bottom form_btn">
            <!--<div class="left">-->
            <!--&lt;!&ndash;<a href="<?php echo Url::to(['/h5/shop/view', 'id' => $goods->sid]);?>">&ndash;&gt;-->
            <!--&lt;!&ndash;<dl class="dl1">&ndash;&gt;-->
            <!--&lt;!&ndash;<dt><img src="/images/talk_06.jpg"></dt>&ndash;&gt;-->
            <!--&lt;!&ndash;<dd>店铺</dd>&ndash;&gt;-->
            <!--&lt;!&ndash;</dl>&ndash;&gt;-->
            <!--&lt;!&ndash;</a>&ndash;&gt;-->
            <!--<a href="tel:18006490976">-->
            <!--&lt;!&ndash;                        href="&ndash;&gt;<?php //echo Url::to(['/h5/message/chat', 'sid' => $goods->sid, 'gid' => $goods->id]);?>&lt;!&ndash;">&ndash;&gt;-->
            <!--<dl>-->
            <!--<dt><img src="/images/det_11.png"></dt>-->
            <!--<dd>客服</dd>-->
            <!--</dl>-->
            <!--</a>-->
            <!--</div>-->
            <div class="left_d commission_fen">
                <p>分享赚</p>
                <p>¥<?php echo round($goods->share_commission_value * $share_commission / 100, 2);?></p>
            </div>
            <?php if ($goods->is_pack != 1) {?>
                <div class="right">
                    立即购买
                </div><!--right-->
            <?php } else {?>
                <div class="right buy">
                    立即购买
                </div>
            <?php }?>
            <div class="right right2 addcart">
                <input type="hidden" class="sku_key" <?php if (count($sku_list) == 1) {echo ' value="' . Html::encode($sku_list[0]['key_name']) . '"';}?>>
                加入购物车
            </div>
            <input type="hidden" class="sku_key" <?php if (count($sku_list) == 1) {echo ' value="' . Html::encode($sku_list[0]['key_name']) . '"';}?>>
        </div><!--bottom-->
        <div class="kePublic" style="display: none;">
            <div class="gb_resLay clearfix">
                <div class="bdsharebuttonbox">
                    <ul class="gb_resItms">
                        <li> <a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin"></a>微信好友 </li>
                        <!--<li> <a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq" ></a>QQ好友 </li>-->
                        <!--<li> <a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone" ></a>QQ空间 </li>-->
                        <!--<li> <a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina" ></a>新浪微博 </li>-->
                        <!--<li> <a title="分享到朋友圈" href="#" class="bds_pengyou" data-cmd="sns_icon" ></a>朋友圈</li>-->
                    </ul>
                </div>
                <div class="clear"></div>
                <div class="gb_res_t"><span>取消</span><i></i></div>
            </div>
        </div><!--kePublic-->
    </div><!--product_details-->
    <div class="b_maskss">
        <div class="state">
            <div class="state_s">
                <img src="/images/colse_btn_03.png" alt="" class="b_closebtn">
            </div>
            <div class="state_b">
                <h2>友情提示</h2>
                <p>您当前不是激活会员，成为会员最低可省下¥<span style="color: #cc1000;"><?php echo round($goods->share_commission_value * $share_commission / 100, 2);?></span></p>
            </div>
            <div class="state_l">
                <div class="state_p">
                    <a href="http://yuntaobang.ysjjmall.com/h5/goods/view?id=2">成为会员</a>
                </div>
                <div class="state_r buy">
                    直接购买
                </div>
            </div>
        </div>
    </div>
</div><!--box-->
<style>
    .layui-layer-dialog{
        font-size: 14px;
    }
</style>
<script>
    //商品规格价格对照数组
    var sku_price = <?php echo json_encode($sku_list);?>;

    function page_init() {
        if (checkToken()) {
            var ua = window.navigator.userAgent.toLowerCase();
            if (ua.toLowerCase().indexOf('micromessenger') > -1) {
                apiGet('/api/default/weixin-mp-js-config', {url:window.location.href}, function (json) {
                    if (callback(json)) {
                        var wxConfig = json['wxConfig'];
                        wxConfig['jsApiList'] = [
                            'onMenuShareAppMessage'
                        ];
                        wx.config(wxConfig);
                        wx.ready(function () {
                            apiGet('/api/user/detail', {}, function (json) {
                                if (callback(json)) {
                                    if (json['user']['status'] == 1) {
                                        wx.onMenuShareAppMessage({
                                            title: '<?php echo Html::encode($goods->title);?>', // 分享标题
                                            desc: '<?php echo Html::encode(nl2br(trim($goods->desc)));?>', // 分享描述
                                            link: '<?php echo Url::to(['/h5/goods/view', 'id' => $goods->id], true);?>&invite_code=' + json['user']['invite_code'], // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                                            imgUrl: '<?php echo Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic;?>', // 分享图标
                                            type: 'link', // 分享类型,music、video或link，不填默认为link
                                            dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                                            success: function () {
                                            },
                                            cancel: function () {
                                            },
                                            fail: function (res) {
                                            }
                                        });
                                    }
                                }
                            });
                        });
                        wx.error(function (res) {
                        });
                    }
                });
            }
            goodsFavCheck(<?php echo $goods->id;?>);
        }
        $('#home_slider').flexslider({
            animation : 'slide',
            controlNav : true,
            directionNav : true,
            animationLoop : true,
            slideshow : true,
            slideshowSpeed: 3000,
            useCSS : false
        });
        var city_data = {province:<?php echo json_encode($city_data);?>};
        var cityPicker = new HzwCityPicker({
            data: city_data,
            target: 'cityChoice',
            valType: 'k',// k-v
            hideCityInput: {
                name: 'city',
                id: 'city'
            },
            hideProvinceInput: {
                name: 'province',
                id: 'province'
            },
            callback: function(){
                getFee();
            }
        });
        cityPicker.init();

        $("input:text").click(function(){
            $(this).select();
        });

        $('.form_input').bind('focus',function(){
            $('.form_btn').css('position','static');
        }).bind('blur',function(){
            $('.form_btn').css({'position':'fixed','bottom':'0rem'});
        });

        $('.form_input').bind('focus',function(){
            $('.form_btn').css('position','static');
        }).bind('blur',function(){
            $('.form_btn').css({'position':'fixed','bottom':'0rem'});
        });

        $('.right').click(function(){
            <?php if ($goods->is_pack !=1) {?>
            $('.b_maskss').addClass('b_show');
            <?php }?>
        });
        $('.b_closebtn , .state_g').click(function(){
            $('.b_maskss').removeClass('b_show');
        });

        $('.form_input').bind('focus',function(){
            $('.form_btn').css('position','static');
        }).bind('blur',function(){
            $('.form_btn').css({'position':'fixed','bottom':'0rem'});
        });

        $(".product_head .span1").click(function(){
            $(".kePublic").show();
        });
        $(".gb_res_t span").click(function(){
            $(".kePublic").hide();
        });

        $(".commission_fen").click(function(){
            $(".kePublic").show();
        });
        $(".gb_res_t span").click(function(){
            $(".kePublic").hide();
        });

        $('.div3_p1 p').click(function(){
            $(this).addClass('change_color').siblings('p').removeClass('change_color');
        });
        $('.div3_p1_color p').click(function(){
            $(this).addClass('change_color').siblings('p').removeClass('change_color');
        });

        $('.gallery img').fsgallery();

        //加入购物车
        $('.addcart').click(function () {
            var gid = '<?php echo $goods->id;?>';
            var amount = $('.amount_value').val();
            if(amount < 1){
                amount = 1;
            }
            var check = checkSku();
            var sku_key_name = $('.sku_key').val();
            if (!check) {
                layer.msg("请选择规格。",function(){});
                return false;
            }
            //判断规格  库存数量  能否加入购物车
            var stock = 0;
            if(Object.keys(sku_price).length === 0 && $(".div3_p1_color").length ==0){
                stock = <?php echo $goods->stock;?>;
            }else{
                $.each(sku_price, function (i, value) {
                    if (value['key_name'] == sku_key_name) {
                        stock = value['stock'];
                        return stock;
                    }
                });
            }

            if (parseInt(amount) > parseInt(stock)) {
                layer.msg('该规格库存仅剩' + stock, function () {});
                return false;
            }
            //加入购物车
            saveCart(gid, sku_key_name, amount);
        });

        //立即购买
        $('.buy').click(function(){
            var check = checkSku();
            var sku_key_name = $('.sku_key').val();
            if (!check) {
                layer.msg("请选择规格。",function(){});
                return false;
            }
            var amount = $('.amount_value').val();
            if(amount < 1){
                amount = 1;
            }

            //判断规格  库存数量  能否立即购买
            var stock = 0;
            if(Object.keys(sku_price).length === 0 && $(".div3_p1_color").length ==0){
                stock = <?php echo $goods->stock;?>;
            }else{
                $.each(sku_price, function (i, value) {
                    if (value['key_name'] == sku_key_name) {
                        stock = value['stock'];
                        return stock;
                    }
                });
            }

            if (parseInt(amount) > parseInt(stock)) {
                layer.msg('该规格库存仅剩' + stock, function () {});
                return false;
            }
            var invite_code = Util.request.get('invite_code');
            window.location.href = '<?php echo Url::to(['/h5/order/confirm']);?>' + '?type=goods&gid=<?php echo $goods->id;?>&sku_key_name='+encodeURIComponent(sku_key_name)+'&amount='+amount +'&invite_code=' + invite_code;
        });

        //规格选择
        $('.sku').click(function(){
            var key = [];
            $('.sku').each(function () {
                if ($(this).hasClass('change_color')) {
                    key.push($(this).data('key'));
                }
            });
            if(Object.keys(sku_price).length === 0){

            }else{
                if(Object.keys(key).length === 0){
                    layer.msg('规格库必选。', function () {});
                    return false;
                }
                var len = sku_price.length;
                var arr = [];
                var reg = key.sort(sortNumber).join('.*');
                reg = '.*'+reg+'.*';
                var reg = new RegExp(reg);
                for(var i=0;i<len;i++){
                    //如果字符串中不包含目标字符会返回-1
                    if(sku_price[i]['key'].match(reg)){
                        arr.push(sku_price[i]);
                    }
                }
                if(arr.length===0){
                    layer.msg('该规格无库存。');
                    $(this).removeClass('change_color');
                }
            }
            var image = $(this).data('image');
            var slider=$('#home_slider').data('flexslider');
            var reg= /([^\s]+(?=\.(jpg|png))\.\2)/gi;
            if(reg.test(image)){
                slider.pause();
                $('.slides').hide();
                var img = '<img class="sku_img" src="'+image+'" width="100%">';
                $('.sku_img').remove();
                $('.flex-viewport').after(img);
            }
            key = key.sort(sortNumber).join('_');
            var old_price = <?php echo $goods->price;?>;
            var exits_key = false;
            $.each(sku_price, function (i, value) {
                if (value['key'] === key) {
                    $('.price').html('￥' + value['price']);
                    if(value['price'] != old_price){
                        $('.old_price').show();
                        $('.old_price').html('￥' + value['market_price']);
                    }else{
                        $('.old_price').hide();
                    }
                    $('.stock').html(value['stock']);
                    $('.sku_key').val(value['key_name']);
                    exits_key = true;
                    return false;
                } else {
                    $('.sku_key').val('');
                }
            });
        });

        getAddress();
    }

    //根据ip地址获取城市
    function getAddress(){
        $.getJSON('<?php echo Url::to(['/h5/goods/get-ip-address']);?>', {'ajax':1}, function (json) {
            if(json != false){
                $('#cityChoice').val(json['name'][1] ? json['name'][1]: json['name'][0]);
                $('#province').val(json['area'][0]);
                if (json['area'][1]) {
                    $('#city').val(json['area'][1]);
                }
                getFee();
            }
        });
    }

    //获取物流模板
    function getFee(){
        var amount = $('#qty_item_1').val(),
            cid = $('#city').val();
        apiGet('<?php echo Url::to(['/api/goods/get-express']);?>', {'gid':<?php echo $goods->id?>, 'area':cid, 'amount':amount}, function (json) {
            var option = '';
            if (json['express_list']) {
                $.each(json['express_list'],function(i,value){
                    if(value['fee'] ==0){
                        option += '<option>包邮</option>';
                    }else{
                        option += '<option>' + value['express_name'] + '￥'+value['fee']+'</option>';
                    }
                });
                $('.express').html(option);
            }else{
                option += '<option>无物流请联系客服</option>';
                $('.express').html(option);
            }
        });
    }

    //检查sku选择是否正确
    function checkSku(){
        var key = [];
        $('.sku').each(function () {
            if ($(this).hasClass('change_color')) {
                key.push($(this).data('key'));
            }
        });
        if(Object.keys(sku_price).length === 0){
            return true;
        }else{
            if(Object.keys(key).length === 0){
                layer.msg('规格库必选。', function () {});
                return false;
            }
        }

        key = key.sort(sortNumber).join('_');
        var exits_key = false;
        $.each(sku_price, function (i, value) {
            if (value['key'] === key) {
                $('.price').html('￥' + value['price']);
                $('.stock').html(value['stock']);
                $('.sku_key').val(value['key_name']);
                exits_key = true;
                return false;
            } else {
                $('.sku_key').val('');
            }
        });
        if(!exits_key){
            return false;
        }else{
            return true;
        }
    }

    // 用作 sort 排序用
    function sortNumber(a, b) {
        return a - b;
    }

    /**
     * 加入购物车
     * @param gid 商品编号
     * @param sku_key_name 规格
     * @param amount 数量
     */
    function saveCart(gid, sku_key_name, amount) {
        apiGet('<?php echo Url::to(['/api/cart/add']);?>', {'gid':gid, 'sku_key_name':sku_key_name, 'amount':amount}, function (json) {
            if (callback(json)) {
                layer.msg('加入成功', {
                    time: 0 ,//不自动关闭
                    btn: ['去购物车', '再逛逛'],
                    yes: function(index){
                        layer.close(index);
                        window.location.href = '<?php echo Url::to(['/h5/cart/index']);?>';
                    },
                    no: function(index){
                        layer.close(index);
                    }
                });
            }
        });
    }

    /**
     * 检查商品收藏状态
     */
    function goodsFavCheck(id) {
        apiGet('/api/user/check-fav-goods', {'id': id}, function (json) {
            if (callback(json)) {
                if (json['exist']) {
                    $('#fav_goods').unbind('click').click(function () {
                        cancelGoodsFav(id);
                    }).find('img').attr('src', '/images/shoucang_02.png');
                } else {
                    $('#fav_goods').unbind('click').click(function () {
                        goodsFav(id);
                    }).find('img').attr('src', '/images/shoucang_01.png');
                }
            }
        });
    }

    /**
     * 收藏商品
     * @param id 商品编号
     */
    function goodsFav(id) {
        apiGet('<?php echo Url::to(['/api/user/add-fav-goods']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#fav_goods').unbind('click').click(function () {
                    cancelGoodsFav(id);
                }).find('img').attr('src', '/images/shoucang_02.png');
            }
        });
    }
    /**
     * 取消收藏商品
     */
    function cancelGoodsFav(id) {
        apiGet('<?php echo Url::to(['/api/user/delete-fav-goods']);?>', {'gid':id}, function (json) {
            if (callback(json)) {
                $('#fav_goods').unbind('click').click(function () {
                    goodsFav(id);
                }).find('img').attr('src', '/images/shoucang_01.png');
            }
        });
    }

    /* 数量修改*/
    var setAmount = {
        min:1,
        max:999,
        reg:function(x) {
            return new RegExp("^[1-9]\\d*$").test(x);
        },
        amount:function(obj, mode) {
            var x = $(obj).val();
            if (this.reg(x)) {
                if (mode) {
                    x++;
                } else {
                    x--;
                }
            } else {
                layer.msg('请输入正确的数量！', {icon: 5});
                $(obj).val(1);
            }
            return x;
        },
        reduce:function(obj) {
            var x = this.amount(obj, false);
            if (x >= this.min) {
                $(obj).val(x);
            } else {
                $(obj).val(1);
            }
        },
        add:function(obj) {
            var x = this.amount(obj, true);
            if (x <= this.max) {
                $(obj).val(x);
            } else {
                layer.msg("商品数量最多为" + this.max, {icon: 5});
                $(obj).val(999);
            }
        },
        modify:function(obj) {
            var x = $(obj).val();
            if (x < this.min || x > this.max || !this.reg(x)) {
                layer.msg("请输入正确的数量！", {icon: 5});
                $(obj).val(1);
            }else{
                $(obj).val(x);

            }
        }
    };
    window._bd_share_config = {
        "common": {
            "bdText": "<?php echo Html::encode($goods->title);?>",
            "bdDesc": "<?php echo preg_replace('/\r|\n/', '', nl2br(Html::encode($goods->desc)));?>",
            "bdUrl": '<?php echo Url::current(['invite_code' => Yii::$app->user->identity['invite_code']], true);?>',
            "bdPic": "<?php echo Url::base(true), Yii::$app->params['upload_url'], $goods->main_pic;?>",
            "bdSnsKey": {},
            "bdMini": "2",
            "bdMiniList": false,
            "bdStyle": "0",
            "bdSize": "24"
        }, "share": {}
    };
    //插件的JS加载部分
    with (document) 0[(getElementsByTagName('head')[0] || body)
        .appendChild(createElement('script'))
        .src = '/static/api/js/share.js?v=89860593.js?cdnversion='
        + ~(-new Date() / 36e5)];
</script>
