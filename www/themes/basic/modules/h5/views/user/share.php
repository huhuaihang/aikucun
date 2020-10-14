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

$this->title = '分享';

?>
<div class="box" id="app">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">分享</a>
    </div><!--new_header-->
    <div class="share">
        <div class="share-top">
            <p>我的推荐码</p>
        </div>
        <div class="share-zh">
            <p>{{ user.invite_code }} </p>
            <a :href="'<?php echo Url::to(['/h5/user/recommend-qr-code']);?>?invite_code=' + user.invite_code">
                <img src="/images/erw.png">
            </a>
        </div>
        <div class="share-xia">
            <p>将推荐码分享给好友，每邀请并成功激活账号的一位好友，即可获得补贴</p>
        </div>
        <div class="recommend">
            <a :href="'<?php echo Url::to(['/h5/user/recommend-qr-code']);?>?invite_code=' + user.invite_code">
            <img src="/images/tuijian.png" class="bds-f">
            </a>
        </div>
    </div>
    <div class="kePublic" style="display:none;" >
        <div class="gb_resLay shengji_share">
            <div class="bdsharebuttonbox">
                <ul class="gb_resItms">
                    <li> <a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin"></a>微信好友 </li>
                    <li> <a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq"></a>QQ好友 </li>
                    <li> <a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone"></a>QQ空间 </li>
                    <li> <a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina"></a>新浪微博 </li>
                    <li> <a title="分享到朋友圈" href="#" class="bds_pengyou" data-cmd="sns_icon"></a>朋友圈</li>
                </ul>
            </div>
            <div class="clear"></div>
            <div class="gb_res_t"><span class="cancel">取消</span><i></i></div>
        </div>
    </div><!--kePublic-->
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            user: {}
        },
        methods: {
            loadUser: function () {
                apiGet('/api/user/detail', {}, function(json) {
                    if (callback(json)) {
                        app.user = json['user'];
                    }
                });
            }
        },
        mounted: function () {
            this.loadUser();
        }
    });
    function page_init() {
        $(".recommend .bds-f").click(function(){
           // $(".kePublic").show();
        });
        $(".cancel").click(function(){
            $(".kePublic").hide();
        });
        window._bd_share_config={
            "common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdMiniList":false,"bdPic":"","bdStyle":"0","bdSize":"16"},
            "share":{},
            "image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},
            "selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}
        };
        with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src=
            'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
    }
</script>
