<?php

use app\assets\LayerAsset;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 */

LayerAsset::register($this);

$this->title = '我要合作';
?>
<div class="box">
    <header class="mall-header">
        <div class="mall-header-left">
            <a href="<?php echo Url::to(['/h5/user']);?>"><img src="/images/11_1.png" alt="返回"></a>
        </div>
        <div class="mall-header-title">我要合作</div>
    </header>
    <div class="container">
        <div class="wyhz">
            <div class="sh">
                <ul>
                    <li>
                        <div class="tu">
                            <img src="/images/cooperation_03.png">
                        </div>
                        <div class="tu1">
                            <h2>商家入驻</h2>
                            <p>诚邀优质商家加盟合作</p>
                        </div>
                        <div class="tu2">
                            <img src="/images/you.png">
                        </div>
                    </li>
                    <li>
                        <a href="javascript:void(0);" onclick="agentTip()">
                            <div class="tu">
                                <img src="/images/cooperation_06.png">
                            </div>
                            <div class="tu1">
                                <h2>代理商加盟</h2>
                                <p>欢迎加盟代理合作共赢</p>
                            </div>
                            <div class="tu2">
                                <img src="/images/you.png">
                            </div>
                        </a>
                    </li>
                </ul>
            </div><!--sh-->
        </div><!--wyhz-->
        <div class="cooperation_popover">
            <div class="popover_div1">
                <div class="a1">
                    <p class="span1">开店类型</p>
                    <span><img src="/images/popover_03.png"></span>
                </div>
                <dl>
                    <dt><a href="<?php echo Url::to(['/h5/join/merchant']);?>"><img src="/images/popover_07.png"></a></dt>
                    <dd><a href="<?php echo Url::to(['/h5/join/agreement', 'type'=>'merchant-person']);?>">个人商家入驻</a></dd>
                </dl>
                <dl>
                    <dt><a href="<?php echo Url::to(['/h5/join/merchant']);?>"><img src="/images/popover_10.png"></a></dt>
                    <dd><a href="<?php echo Url::to(['/h5/join/agreement', 'type'=>'merchant']);?>">企业商家入驻</a></dd>
                </dl>
            </div>
        </div>
    </div>
</div><!--box-->
<script>
    function page_init() {
        $("ul li:first").click(function(){
            $(".cooperation_popover").show()
        });
        $(".cooperation_popover .a1 img").click(function(){
            $(".cooperation_popover").hide()
        });
    }

    function agentTip(){
        layer.msg('暂未开通 敬请期待。');
        return false;
    }
</script>
