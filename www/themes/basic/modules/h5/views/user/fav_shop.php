<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\VueAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

ApiAsset::register($this);
LayerAsset::register($this);
VueAsset::register($this);
$this->registerJsFile('/js/NativeShare.js');

$this->title = '我的收藏';
?>
<div class="box" id="app">
    <div class="coll_head head_fixed_top">
        <p class="p1">
            <a href="<?php echo Url::to(['/h5/user']);?>"><span class="span1"><img src="/images/10.png"></span></a>
            <a href="javascript:void(0)"><span class="span2"><b class="b1" @click="showEdit" v-show="!on_edit">编辑</b><b class="b2" id="b2" @click="closeEdit" v-show="on_edit">完成</b></span></a>
        </p>
        <p class="p2"><span class="span1"><a href="<?php echo Url::to(['/h5/user/fav-goods']);?>">商品</a></span><span class="span2 collect_color"><a href="<?php echo Url::to(['/h5/user/fav-shop']);?>">店铺</a></span></p>
    </div><!--coll_head-->
    <div class="collect" ref="wrapper">
        <div class="collect_dianpu">
            <div class="div1" v-for="(fav, index) in fav_list">
                <div class="z_collect_left">
                    <input type="checkbox" class="filled-in" v-model="checked_ids" :value="fav.id" :id="'filled-in-box_'+index" style="display: none;">
                    <label :for="'filled-in-box_'+index"></label>
                </div>
                <span class="label_dl">
                    <dl>
                        <dt><a class="a1" :href="'<?php echo Url::to(['/h5/shop/view']);?>?id=' + fav.shop.id"><img :src="fav.shop.logo"></a></dt>
                        <dd class="dd1"><a class="a1" :href="'<?php echo Url::to(['/h5/shop/view']);?>?id=' + fav.shop.id"><span class="span1">{{fav.shop.name}}</span><span class="span2">{{fav.shop.type}}</span></a></dd>
                        <dd class="dd2"><a class="a1" :href="'<?php echo Url::to(['/h5/shop/view']);?>?id=' + fav.shop.id">主营：{{fav.shop.major_business.join(' ')}}</a></dd>
                        <dd class="dd3">
                            <span class="span1"><img v-for="i in fav.shop.score" src="/images/heart1.png"></span>
                            <span class="span2" @click="showShare(index)"><img src="/images/share_03.png"></span>
                        </dd>
                    </dl>
                </span>
            </div><!--div1-->
            <div class="clear"></div>
        </div><!--collect_dianpu-->
        <div class="clear"></div>
        <div class="gb_resLay clearfix" v-show="show_share">
            <div class="bdsharebuttonbox">
                <ul class="gb_resItms">
                    <li @click="show_share_qr=true"><a title="分享到微信" href="javascript:void(0)" class="bds_weixin"></a>微信好友</li>
                    <li v-show="!in_weixin"><a title="分享到QQ好友" href="javascript:void(0)" class="bds_sqq" data-cmd="sqq" ></a>QQ好友
                        <button @click="nativeShare('qqFriend')"></button>
                    </li>
                    <li v-show="!in_weixin"><a title="分享到QQ空间" href="javascript:void(0)" class="bds_qzone" data-cmd="qzone" ></a>QQ空间
                        <button @click="nativeShare('qZone')"></button>
                    </li>
                    <li v-show="!in_weixin"><a title="分享到新浪微博" href="javascript:void(0)" class="bds_tsina" data-cmd="tsina" ></a>新浪微博
                        <button @click="nativeShare('weibo')"></button>
                    </li>
                    <li v-show="!in_weixin"><a title="分享到朋友圈" href="javascript:void(0)" class="bds_pengyou" data-cmd="sns_icon" ></a>朋友圈
                        <button @click="nativeShare('wechatTimeline')"></button>
                    </li>
                </ul>
            </div>
            <div class="clear"></div>
            <div class="gb_res_t"><span @click="show_share=false">取消</span><i></i></div>
        </div><!--kePublic-->
    </div><!--collect-->
    <div class="collect_weixin_img" v-show="show_share_qr">
        <div>
            <p class="p1" @click="show_share_qr=false"><img src="/images/27.png"></p>
            <p class="p2"><img id="wx_qr" :src="share_qr" alt="微信二维码"></p>
        </div>
    </div>
    <div class="collect_delete" @click="deleteFav" v-show="on_edit">删除</div>
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            current_page: 1, // 当前页码
            fav_list: [], // 收藏列表
            show_share: false, // 显示分享层
            in_weixin: false, // 是否在微信中打开
            share_json: {title: '', url: '', image: ''}, // 分享信息
            share_qr: '', // 微信分享二维码地址
            show_share_qr: false, // 是否显示二维码
            on_edit: false, // 是否在编辑状态
            checked_ids: [], // 选中的编号
            scroll: false, // 滚动监听器
            user: {} //登录用户信息
        },
        methods: {
            loadMore: function () {
                this.closeEdit();
                apiGet('<?php echo Url::to(['/api/user/fav-shop-list']);?>', {page: this.current_page}, function (json) {
                    if (callback(json)) {
                        json['fav_list'].forEach(function (fav) {
                            app.fav_list.push(fav);
                        });
                        app.$nextTick(function () {
                            if (!app.scroll) {
                                app.scroll = new BScroll(this.$refs.wrapper, {
                                    click: true, //
                                    probeType: 1 // 非实时派发滚动事件
                                });
                                app.scroll.on('scrollEnd', function (pos) {
                                    if (pos.y < this.maxScrollY + 30) {
                                        if (app.current_page >= json['page']['pageCount']) {
                                            layer.msg('没有更多数据了。');
                                        } else {
                                            app.current_page++;
                                            app.loadMore();
                                        }
                                    }
                                });
                            } else {
                                app.scroll.refresh();
                            }
                        });
                    }
                });
            },
            showShare: function (index) {
                this.show_share = true;
                this.in_weixin = /MicroMessenger/i.test(window.navigator.userAgent.toLowerCase());
                this.share_json.title = this.fav_list[index].shop.name;
                this.share_json.url = '<?php echo Url::to(['/h5/shop/view'], true);?>?id=' + this.fav_list[index].shop.id;
                if (app.user.invite_code) {
                    this.share_json.url += '&invite_code='+ app.user.invite_code;
                }
                this.share_json.image = this.fav_list[index].shop.logo;
                this.share_qr = '<?php echo Url::to(['/site/qr']);?>?content=' + this.share_json.url;
            },
            showEdit: function () {
                this.on_edit = true;
                // TODO：使用VUE的过渡效果代替JQuery
                $(".box .collect .div1").animate({'left':'0%','transition':'0s'});
            },
            closeEdit: function () {
                this.on_edit = false;
                $(".box .collect .div1").animate({'left':'-11%','transition':'0s'});
            },
            deleteFav: function () {
                apiGet('<?php echo Url::to(['/api/user/delete-fav-shop']);?>', {ids: this.checked_ids.join(',')}, function (json) {
                    if (callback(json)) {
                        app.checked_ids.forEach(function (id) {
                            app.fav_list.forEach(function (fav, index) {
                                if (id === fav.id) {
                                    app.fav_list.splice(index, 1);
                                }
                            });
                        });
                        app.$nextTick(function () {
                            app.scroll.refresh();
                        });
                    }
                });
            },
            nativeShare: function (command) {
                var nativeShare = new NativeShare(),
                    shareData = {
                        title: this.share_json.title,
                        desc: this.share_json.title,
                        link: this.share_json.url,
                        icon: this.share_json.url,
                        success: function() {
                            // alert('success')
                        },
                        fail: function() {
                            // alert('fail')
                        }
                    };
                nativeShare.setShareData(shareData);
                try {
                    nativeShare.call(command)
                } catch (err) {
                    // 如果不支持，你可以在这里做降级处理
                    //alert(err.message)
                    alert('当前浏览器不支持');
                }
            }
        },
        mounted: function () {
            this.$refs.wrapper.style.height = (document.documentElement.clientHeight - 65) + 'px';
            this.loadMore();
            apiGet('<?php echo Url::to(['/api/user/detail']);?>', {}, function (json) {
                if (callback(json)) {
                    app.user = json['user'];
                }
            });
        }
    });
</script>
