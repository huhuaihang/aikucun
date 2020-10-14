<?php
/**
 * @var $this \yii\web\View
 */

$this->title = '丽人';
?>
<style>
    body {background:#fff;}
</style>
<div class="box">
    <div class="B_head">
        <span class="span1"><a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/10.jpg"></a></span>
        <a href="#"><span class="span2"><input type="text" placeholder="输入商家名,品类或商圈"></span></a>
    </div><!--B_head-->
    <div class="beauty">
        <div class="div1">
            <span><a href="#"><img src="/images/liren_07.jpg"></a></span>
            <span><a href="#"><img src="/images/liren_09.jpg"></a></span>
            <span><a href="#"><img src="/images/liren_11.jpg"></a></span>
        </div><!--div1-->
        <div class="clear"></div>
        <div class="div2">
            <div class="div2_top">
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_17.jpg"></dt>
                        <dd>韩式定妆</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_19.jpg"></dt>
                        <dd>瑜伽舞蹈</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_21.jpg"></dt>
                        <dd>瘦身仟体</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_23.jpg"></dt>
                        <dd>祛痘</dd>
                    </dl>
                </a>
            </div><!--top-->
            <div class="clear"></div>
            <div class="div2_top" style="border:none;">
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_29.jpg"></dt>
                        <dd>纹身</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_30.jpg"></dt>
                        <dd>整形</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_31.jpg"></dt>
                        <dd>化妆品</dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_32.jpg"></dt>
                        <dd>全部</dd>
                    </dl>
                </a>
            </div><!--top-->
        </div><!--div2-->
        <div class="clear"></div>
        <div class="bor"></div>
        <div class="div3">
            <div class="left">
                <img src="/images/liren_40.jpg">
            </div><!--left-->
            <div class="right">
                <div id="ad">
                    <ul id="adul" >
                        <li>
                            <a href="#">
                                <img src="/images/liren_38.jpg">
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <img src="/images/liren_38.jpg">
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <img src="/images/liren_38.jpg">
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <img src="/images/liren_38.jpg">
                            </a>
                        </li>
                        <li>
                            <a href="#">
                                <img src="/images/liren_38.jpg">
                            </a>
                        </li>
                    </ul>
                </div>
            </div><!--right-->
        </div><!--div3-->
        <div class="clear"></div>
        <div class="div4">
            <div class="p1">
                <a href="#">
                    <dl class="dl1">
                        <dt><img src="/images/liren_44.jpg"></dt>
                        <dd>&nbsp;&nbsp;快去看看为您定制得美丽套餐</dd>
                    </dl>
                    <dl class="dl2">
                        <dt><img src="/images/12.jpg"></dt>
                        <dd>&nbsp;&nbsp;附近美发2.2折起</dd>
                    </dl>
                </a>
            </div><!--p1-->
            <div class="clear"></div>
            <div class="p2">
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_47.jpg"></dt>
                        <dd class="dd1">[剪发]担任总担任总担任总监</dd>
                        <dd class="dd2"><span>￥24.8</span><span class="span2">品牌新用户减</span></dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_49.jpg"></dt>
                        <dd class="dd1">[染烫]烫发加染发</dd>
                        <dd class="dd2"><span>￥158</span><span class="span2">低至2.3折</span></dd>
                    </dl>
                </a>
                <a href="#">
                    <dl>
                        <dt><img src="/images/liren_51.jpg"></dt>
                        <dd class="dd1">[染烫]华仔美业</dd>
                        <dd class="dd2"><span>￥24.8</span><span class="span2">低至3.9折</span></dd>
                    </dl>
                </a>
            </div>
        </div><!--div4-->
        <div class="clear"></div>
        <div class="div5">
            <a href="#">
                <dl class="left">
                    <dt><img src="/images/liren_59.jpg"></dt>
                    <dd class="dd1">天天特价</dd>
                    <dd class="dd2">造型5折起</dd>
                </dl>
            </a>
            <a href="#">
                <dl class="right">
                    <dt><img src="/images/liren_63.jpg"></dt>
                    <dd class="dd1">免费试用</dd>
                    <dd class="dd2">奥迪真我香水</dd>
                </dl>
            </a>
        </div><!--div5-->
    </div><!--beauty-->
</div><!--box-->
<script language="javascript">
    var ad = {
        o:null,      // 存放滚动的UL
        cloneImg:null,  //克隆UL的第一个图片
        adY:0, //滚动值
        distan:0, //每个图片的高度
        ///
        init:function(obj){
            if(!obj.style.top){
                obj.style.top = '0px';
            }
            this.cloneImg = obj.firstChild.cloneNode(true); //克隆第一个节点
            if(this.cloneImg.nodeType == 3) this.cloneImg = obj.firstChild.nextSibling.cloneNode(true); //除IE外第一个节点为文本节点，让克隆节点还是指向第一个元素
            obj.appendChild(this.cloneImg); //让克隆的节点放入最后
            this.adY = parseInt(obj.style.top);
            this.o = obj;
            this.distan = this.cloneImg.offsetHeight;
            this.moveCtrl();
        },
        moveCtrl:function(){
            if(Math.abs(this.adY) == this.o.offsetHeight - this.distan) this.adY = 0;//到达底部滚动跳回最上面
            if(Math.abs(this.adY)%this.distan==0){
                setTimeout('ad.moveCtrl()',2000);//图片停留延迟
            } else {
                setTimeout('ad.moveCtrl()',10);//运动循环
            }
            --this.adY;
            ad.o.style.top = this.adY + 'px';
        }
    };
    window.onload = function(){
        var obj = document.getElementById('adul');
        ad.init(obj);
    }
</script>
