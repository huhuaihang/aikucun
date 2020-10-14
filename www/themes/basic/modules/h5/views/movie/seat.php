<?php
/**
 * @var $this \yii\web\View
 */

$this->registerCssFile('/style/jq22.css');
$this->registerJsFile('/js/jquery.seat-charts.min.js', ['depends' => ['yii\web\JqueryAsset']]);

$this->title = '选择座位';
?>
<style>
    body {overflow-x: hidden;}
</style>
<div class="box1">
    <div class="about_head about_head2">
        <a href="javascript:void(0)" onClick="window.history.go(-1)"><p class="p1"><img src="/images/111.png"></p></a>
        <p class="p2">保利乐尚激光影城</p>
        <a class="fenxiang" href="#" onClick="toshare()" >
            <img src="/images/share_icon.png" width="100%" height="100%"/>
        </a>
    </div><!--about_head-->
    <div class="container">
        <div class="demo clearfix">
            <!--选座信息-->
            <div class="booking_area">
                <p>电影：<span>天将雄师</span></p>
                <p class="pp">今天：<span>03月20日 22:15</span>  英语3D</p>
            </div>
            <!--左边座位列表-->
            <div id="seat_area">
                <div class="big_ting">5号激光4K影厅银幕</div>
                <div class="front">屏幕</div>
            </div>
        </div>
    </div>
    <!--分享弹窗-->
    <div class="am-share">
        <h3 class="am-share-title">分享到</h3>
        <ul class="am-share-sns">
            <li><a href="#"> <i class="share-icon-weibo" style="background-image: url(/images/weibo.png);"></i> <span>新浪微博</span> </a> </li>
            <li><a href="#"> <i class="share-icon-weibo" style="background-image: url(/images/txweibo.png);"></i> <span>腾讯微博</span> </a> </li>
            <li><a href="#"> <i class="share-icon-weibo" style="background-image: url(/images/pyquan.png);"></i> <span>朋友圈</span> </a> </li>
            <li><a href="#"> <i class="share-icon-weibo" style="background-image: url(/images/QQkongjian.png);"></i> <span>QQ空间</span> </a> </li>
        </ul>
        <div class="am-share-footer"><button class="share_btn">取消</button></div>
    </div>
</div><!--box-->
<div class="goupiao">
    <p>已选座位：</p>
    <ul id="seats_chose"></ul>
    <div class="danjia">
        <p>票数：<span id="tickects_num">0</span></p>
        <p class="zongjiage">总价：￥<span id="total_price">0</span></p>
    </div>
    <input type="button" class="btn" value="确定购买"/>
    <div id="legend1"></div>
</div>
<script type="text/javascript">
    var price = 28; //电影票价
    function page_init() {
        var $cart = $('#seats_chose'), //座位区
            $tickects_num = $('#tickects_num'), //票数
            $total_price = $('#total_price'); //票价总额
        var sc = $('#seat_area').seatCharts({
            map: [//座位结构图 a 代表座位; 下划线 "_" 代表过道
                'cccccccccc',
                'cccccccccc',
                'cccccccccc',
                '__cccccccc',
                '__cccccccc',
                '__cccccccc',
                '__cccccccc',
                'cc_ccccccc',
                'cc_ccccccc',
                '_cccccccc_'
            ],
            naming: {//设置行列等信息
                top: false, //不显示顶部横坐标（行）
                getLabel: function(character, row, column) { //返回座位信息
                    return column;
                }
            },
            legend: {//定义图例
                node: $('#legend'),
                items: [
                    ['c', 'available', '可选座'],
                    ['c', 'unavailable', '已售出']
                ]
            },
            click: function() {
                if (this.status() == 'available') { //若为可选座状态，添加座位
                    if($('#seats_chose li').length>=5){
                        alert('一次最多选择5个座位');
                        $tickects_num.text(sc.find('selected').length ); //统计选票数量
                        $total_price.text(getTotalPrice(sc));//计算票价总金额
                        return false;
                    }
                    $('<li>' + (this.settings.row + 1) + '排' + this.settings.label + '座</li>')
                        .attr('id', 'cart-item-' + this.settings.id)
                        .data('seatId', this.settings.id)
                        .appendTo($cart);
                    $tickects_num.text(sc.find('selected').length + 1); //统计选票数量
                    $total_price.text(getTotalPrice(sc) + price);//计算票价总金额
                    return 'selected';
                } else if (this.status() == 'selected') { //若为选中状态
                    $tickects_num.text(sc.find('selected').length - 1);//更新票数量
                    $total_price.text(getTotalPrice(sc) - price);//更新票价总金额
                    $('#cart-item-' + this.settings.id).remove();//删除已预订座位
                    return 'available';
                } else if (this.status() == 'unavailable') { //若为已售出状态
                    return 'unavailable';
                } else {
                    return this.style();
                }
            }
        });
        //设置已售出的座位
        //sc.get(['1_3', '1_4', '4_4', '4_5', '4_6', '4_7', '4_8']).status('unavailable');
        sc.get(['', '', '', '', '', '', '']).status('unavailable');
    }
    function getTotalPrice(sc) { //计算票价总额
        var total = 0;
        sc.find('selected').each(function() {
            total += price;
        });
        return total;
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
        });
    }
</script>

