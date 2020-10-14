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
$this->title = '会员等级';
?>
<div class="box" id="app">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.location.href='<?php echo Url::to(['/h5/user'])?>'" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">会员等级</a>
        <a href="<?php echo Url::to(['/h5/about/agreement']);?>?name=level_description" class="a3"><img src="/images/wen.png"></a>
    </div><!--new_header-->
    <div class="grade">
        <div class="grade-sh">
            <img :src="user_info.avatar">
            <p>{{ user_info.nickname }}</p>
        </div>
        <div class="htmleaf-content">
            <h3 class="center">成长值</h3>
            <div id="progressbar2">
                <div class="progressbar">
                    <div class="proggress" style="max-width: 5.5rem;"></div>
                    <div class="percentCount"><span class="percentCount1"></span>/{{ user_info.next_level_money | numberToInt }}</div>
                </div>
            </div>
        </div>
        <!--<div class="grade-xia">-->
            <!--<a href="<?php echo Url::to(['/h5/user/recharge-values'])?>">我要进货</a>-->
        <!--</div>-->
        <div class="dengji">{{ user_info.level_name }}</div>
    </div>
    <div class="grade-zhong" >
        <img src="/images/zu1.png">
        <p style="font-size: .29rem">我的推荐人</p>
        <img src="/images/zu2.png">
    </div>
    <div class="explain" v-if="user_info.parent.real_name !==''">
        <div class="explain-zuo" style="width:300px;" v-if="user_info.id !='2576'">
            <h2>昵称：{{ user_info.parent.real_name }}</h2>
            <p>手机号：{{ user_info.parent.mobile }}</p>
        </div>
        <div  class="explain-zuo" style="width:300px;" v-else="user_info.id =='2576'">
            <p style="">你没有推荐人</p>
        </div>
<!--        <div class="explain-you">-->
<!--            <p style="line-height: .3rem"></p>-->
<!--            <p style="line-height: .3rem"></p>-->
<!--        </div>-->
    </div>
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
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            user_info: {
                id:'',
                avatar: '', // 头像
                nickname: '', // 昵称
                level_name: '', // 等级名称
                money: '', // 金额
                next_level_money: '' // 下一级所需金额
            },
            level_list: [] // 等级列表
        },
        methods: {
            loadUser: function () {
                apiGet('/api/user/level-list', {}, function(json) {
                    if (callback(json)) {
                        app.user_info = json['user_info'];
                        json['level_list'].forEach(function (level) {
                            app.level_list.push(level);
                        });
                    }
                });
            }
        },
        filters: {
            numberToInt: function (value) {
                return parseInt(value);
            }
        },
        mounted: function () {
            this.loadUser();
        }
    });
</script>
<script>
    window.setTimeout(function() {
        $('#progressbar2').LineProgressbar({
            percentage: parseInt(app.user_info.money),
            next_level_money: parseInt(app.user_info.next_level_money),
            fillBackgroundColor: '#fff'
        });
    }, 500);
</script>
