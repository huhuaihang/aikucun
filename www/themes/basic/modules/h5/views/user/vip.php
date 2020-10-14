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

$this->registerJsFile('/js/jquery.lineProgressbar.js', ['depends' => ['yii\web\JqueryAsset']]);
$this->registerJsFile('/js/clipboard.min.js');
$this->title = '会员等级';
?>
<div class="box" id="app">
    <div class="grade" v-if="user_info.status==1">
<!--        <div class="grade-sh">-->
<!--            <img :src="user_info.avatar">-->
<!--            <p>{{ user_info.nickname }}</p>-->
<!--        </div>-->
<!--        <div class="htmleaf-content">-->
<!--            <h3 class="center">成长值</h3>-->
<!--            <div id="progressbar2">-->
<!--                <div class="progressbar">-->
<!--                    <div class="proggress" style="max-width: 5.5rem;"></div>-->
<!--                    <div class="percentCount"><span class="percentCount1"></span>/{{ user_info.next_level_money | numberToInt }}</div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <!--<div class="grade-xia">-->
<!--            <!--<a href="--><?php //echo Url::to(['/h5/user/recharge-values'])?><!--">我要进货</a>-->
<!--        <!--</div>-->
<!--        <div class="dengji">{{ user_info.level_name }}</div>-->
        <div class="vip_1">
            <img :src="user_info.avatar" alt="">
        </div>
        <div class="vip_2">
            <div class="vip_t">
                <h2 style="float: left; margin-top: .1rem;">{{user_info.nickname}}</h2>
                <img :src="user_info.level_logo" alt="">
            </div>

            <p v-if="user_info.invite_code!=''">邀请码:<font id="codeNum" >{{ user_info.invite_code }}</font><span id="codeBtn" data-clipboard-target="#input">复制</span></p>

            <div class="htmleaf-content">

                <h3 class="center">成长值</h3>
                <div id="progressbar2">
                    <div class="progressbar">
                        <div class="proggress" style="max-width: 3.5rem;"></div>
                        <div class="percentCount"><span class="percentCount1"></span><span>/{{ user_info.level_money | numberToInt }}</span></div>
                    </div>
                </div>
                <p>距离成为店主还差{{user_info.gap_money}}</p>
            </div>
        </div>
        <div class="vip_3">
            <a :href="'<?php echo Url::to(['/h5/user/recommend-qr-code']);?>?invite_code=' + user_info.invite_code" > <img src="/images/vip_e.png" alt="">
            <p>生成邀请函</p>
            </a>
        </div>
    </div>
    <div class="grade" v-if="user_info.status==2">
        <div class="vip_1">
            <img :src="user_info.avatar" alt="">
        </div>
        <div class="vip_2">
            <h2>{{user_info.nickname}}</h2>
            <p>{{user_info.level_name}}</p>
            <p style="margin-bottom: .1rem;" v-if="user_info.status == 2">邀请30人或购买会员礼包升级成会员</p>
        </div>

        <div class="vip_3">
            <a :href="'<?php echo Url::to(['/h5/user/recommend-qr-code']);?>?invite_code=' + user_info.invite_code" > <img src="/images/vip_e.png" alt="">
                <p>生成邀请函</p>
            </a>
        </div>
    </div>
    <div class="vip_pic_s">
        <a @click="layer_notice()"> <img :src="user_info.vip_pic" alt=""></a>
    </div>
    <div class="activity" v-if="ground_push.switch == 1 ">
        <a :href="ground_push.url">
            <img src="/images/activity_d.gif" alt="">
            <p>{{ground_push.name}}·<a :href="ground_push.url" style="color: #f08d3d">立即参与活动</a></p>
            <img src="/images/vip_y.png" alt="">
        </a>
    </div>
    <div class="vip_dian" v-if="user_info.status==1">
        <a @click="layer_notice()">
            <div class="vip_dian1">
                <p>我的店铺</p>
                <img src="/images/vip_y.png" alt="">
            </div>
            <div class="vip_dian2">
                <div class="vip_dian_z">
                    <p>{{user_info.prepare_count}}个</p>
                    <p>(剩余礼包)</p>
                </div>
                <div class="vip_dian_y">
                    <p>{{user_info.sale_count}}个</p>
                    <p>已出售</p>
                </div>
                <div class="vip_dian_p">
                    <p>{{user_info.all_count}}个</p>
                    <p>总数量</p>
                </div>
            </div>
        </a>
    </div>
    <div class="vip_dian" v-if="user_info.status==1">
        <a href="./score">
            <div class="vip_dian1">
                <p>我的积分</p>
                <img src="/images/vip_y.png" alt="">
            </div>
            <div class="vip_dian2">
                <div class="vip_dian_z">
                    <p>{{user_info.score}}</p>
                    <p>(剩余积分)</p>
                </div>
                <div class="vip_dian_y">
                    <p>{{user_info.used_score}}</p>
                    <p>已使用</p>
                </div>
                <div class="vip_dian_p">
                    <p>{{user_info.all_score}}</p>
                    <p>累计积分</p>
                </div>
            </div>
        </a>
    </div>
    <div class="box">
        <?php echo $this->render('../layouts/_bottom_nav');?>
    </div><!--box-->
<!--    <div class="grade-zhong" >-->
<!--        <img src="/images/zu1.png">-->
<!--        <p style="font-size: .29rem">我的推荐人</p>-->
<!--        <img src="/images/zu2.png">-->
<!--    </div>-->
<!--    <div class="explain" v-if="user_info.parent.real_name !==''">-->
<!--        <div class="explain-zuo" style="width:300px;" v-if="user_info.id !='2576'">-->
<!--            <h2>昵称：{{ user_info.parent.real_name }}</h2>-->
<!--            <p>手机号：{{ user_info.parent.mobile }}</p>-->
<!--        </div>-->
<!--        <div  class="explain-zuo" style="width:300px;" v-else="user_info.id =='2576'">-->
<!--            <p style="">你没有推荐人</p>-->
<!--        </div>-->
<!--        <div class="explain-you">-->
<!--            <p style="line-height: .3rem"></p>-->
<!--            <p style="line-height: .3rem"></p>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="explain" v-else="user_info.parent.real_name ==''">-->
<!--        <div  class="explain-zuo" style="width:300px;">-->
<!--            <p style="">你没有推荐人</p>-->
<!--        </div>-->
<!--    </div>-->
<!--    <div class="grade-zhong">-->
<!--        <img src="/images/zu1.png">-->
<!--        <p>会员说明</p>-->
<!--        <img src="/images/zu2.png">-->
<!--    </div>-->
<!--    <div class="mem">-->
<!--        不同级别权益说明-->
<!--    </div>-->
<!--    <div class="explain" v-for="level in level_list">-->
<!--        <div class="explain-zuo">-->
<!--            <h2>{{ level.name }}</h2>-->
<!--            <p>{{ level.money }}元</p>-->
<!--        </div>-->
<!--        <div class="explain-you">-->
<!--            <p>一级补贴：{{ level.money_1 }}</p>-->
<!--            <p>二级补贴：{{ level.money_2 }}</p>-->
<!--            <p>三级补贴：{{ level.money_3 }}</p>-->
<!--            <p>一级佣金比：{{ level.commission_ratio_1 }}</p>-->
<!--            <p>二级佣金比：{{ level.commission_ratio_2 }}</p>-->
<!--            <p>三级佣金比：{{ level.commission_ratio_3 }}</p>-->
<!--        </div>-->
<!--    </div>-->
    <div style="height: 1.4rem;"></div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            user_info:[],
            level_list: [], // 等级列表
            ground_push:{},//活动
        },
        methods: {
            loadUser: function () {

                    apiGet('/api/user/vip-detail', {}, function (json) {
                        if (callback(json)) {
                            app.user_info = json['user'];
                            app.ground_push = json['ground_push'];

                        }
                        console.log(app.ground_push)
                    });

            },

        layer_notice:function(){
            //询问框
            layer.open({
                content: '请在云淘帮APP中查看',
                btn: ['下载APP', '我知道了'],
                btnAlign: 'c',
                yes: function(index){
                    location.href='https://sj.qq.com/myapp/detail.htm?apkName=com.yunshang.yuntaob';
                    layer.close(index);
                }
            });
         },
        },
        filters: {
            numberToInt: function (value) {
                return parseInt(value);
            }
        },
        mounted: function () {
            this.loadUser();
        },
        updated:function () {
            document.getElementById('codeBtn').addEventListener('click', copyArticle, false);

        }
    });
</script>
<script>
    window.setTimeout(function() {
        $('#progressbar2').LineProgressbar({
            percentage: parseInt(app.user_info.growth_money),
            next_level_money: parseInt(app.user_info.level_money),
            fillBackgroundColor: '#fff'
        });
    }, 500);

    function copyArticle(event) {
        const range = document.createRange();
        range.selectNode(document.getElementById('codeNum'));

        const selection = window.getSelection();
        if(selection.rangeCount > 0) selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('copy');
        alert("复制成功！");
    }


</script>
