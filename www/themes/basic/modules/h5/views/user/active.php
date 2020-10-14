<?php

/**
 * @var $this \yii\web\View
 */

$this->title = '激活账号';
?>
<script src="/js/jquery.js" type="text/javascript"></script>
<div class="box">
    <div class="new_header">
        <a href="javascript:void(0)" onClick="window.history.go(-1);"" class="a1"><img src="/images/new_header.png"></a>
        <a href="#" class="a2">激活账号</a>
        <a href="#" class="a3">完成</a>
    </div><!--new_header-->
    <div class="new_active">
        <div class="div1"><span>￥</span><span class="span2">20</span></div>
        <div class="div1"><span>￥</span><span class="span2">50</span></div>
        <div class="div1"><span>￥</span><span class="span2">80</span></div>
        <div class="div1"><span>￥</span><span class="span2">100</span></div>
        <div class="div1"><span>￥</span><span class="span2">200</span></div>
        <div class="div1"><span>￥</span><span class="span2">500</span></div>
    </div>
    <div class="new_active_button">
        <a href="#">确认激活</a>
    </div>
</div>
<script>
    $(function(){
        $(".new_active .div1").click(function (){
            $(this).addClass("bg").siblings().removeClass("bg");
        })
    })
</script>
