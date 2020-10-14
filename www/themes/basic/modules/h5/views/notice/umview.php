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

$this->title = '通知详情';
?>
<div class="box" id="app">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.history.go(-1);" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">通知详情</a>
    </div><!--new_header-->
<!--    <div class="detail">-->
<!--        <h1>{{notice.title}}</h1>-->
<!--        <p><span>{{notice.time | timeFormat}}</span></p>-->
<!--        <div v-html="notice.content"></div>-->
<!--<!--        <img src="/images/xiangq.png">-->
<!--    </div>-->
    <div class="news_s">
        <div class="news_y">
            <h2>激活会员成功</h2>
            <p>{{notice.create_time| timeFormat}}</p>
        </div>
        <div class="news_f">
<!--            <img src="/images/news-1.png" alt="">-->
            <p><span v-html="notice.content"></span></p>
        </div>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            notice: {
                id: 0,
                title: '',
                url: '',
                content: '',
                create_time: ''
            }
        },
        methods: {
            //获取用户信息
            loadNotice: function (id) {
                apiGet('/api/user/user-message-detail', {id:id}, function (json) {
                    if (callback(json)) {
                        app.notice = json['detail'];
                    }
                });
            }
        },
        filters: {
            timeFormat: function (value) {
                var date = new Date(value * 1000);
                var y = date.getFullYear();
                var M = date.getMonth() + 1;
                var d = date.getDate();
                var h = date.getHours();
                var m = date.getMinutes();
                var s = date.getSeconds();
                if (M < 10) {
                    M = '0' + M;
                }
                if (d < 10) {
                    d = '0' + d;
                }
                if (h < 10) {
                    h = '0' + h;
                }
                if (m < 10) {
                    m = '0' + m;
                }
                if (s < 10) {
                    s = '0' + s;
                }
                return y + '-' + M + '-' + d + ' ' + h + ':' + m + ':' + s;
            }
        },
        mounted: function () {
            var id = '<?php echo Yii::$app->request->get('id')?>';
            this.loadNotice(id);
        }
    });
</script>
<script>
    function page_init(){
        <?php if (!empty(Yii::$app->request->get('app'))) {?>
         $('.new_header').hide();
        $('.detail').css('margin-top','0');
        <?php }?>
    }
</script>
