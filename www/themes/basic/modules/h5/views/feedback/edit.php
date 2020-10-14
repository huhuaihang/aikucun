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

$this->title = '意见反馈';
?>
<div class="box bg_white" id="app">
    <form>
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="javascript:void(0)" onClick="window.history.go(-1);"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-right">
            <a href="javascript:void(0)" @click="submit();" id="btn_submit">发表</a>
        </div>
    </header>
    <div class="container">
        <div class="YJ">
            <div class="textarea">
            <textarea placeholder="发表意见！" v-model="Feedback.content" onkeyup='value=value.substr(0,512);this.nextSibling.innerHTML=value.length+""; '></textarea><div class="p1">0</div><div class="p2">/ 512</div>
            </div>
        </div><!--YJ-->
    </div>
    </form>
</div><!--box-->
<script>
    var app = new Vue({
        el: '#app',
        data: {
            Feedback: {
                client: 'h5',
                version: '1.0.0'
            }
        },
        methods: {
            submit: function () {
                apiPost('<?php echo Url::to(['/api/user/feedback']);?>', app.Feedback, function (json) {
                    if (callback(json)) {
                        layer.msg('您的反馈已保存。', function () {
                            window.history.go(-1);
                        });
                    }
                });
            }
        }
    });
</script>
