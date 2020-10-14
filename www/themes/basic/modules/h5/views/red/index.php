<?php

use yii\web\View;

$this->title = '现金红包雨';
$this->registerJsFile('http://yuntaobang.ysjjmall.com/jiaoben3985/js/jquery.min.js', ['postion' => View::POS_HEAD]);
?>
<html>
<head>
    <meta name="save" content="history" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="blank" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="/jiaoben3985/css/style.css" />
    <link rel="stylesheet" href="/jiaoben3985/css/rain.css" />
    <script type="text/javascript" src="/jiaoben3985/js/jquery.min.js" ></script>
    <title>现金红包雨</title>
</head>
<script>
    var a =0;
    var Timerr = setInterval(aa,200);
    var removepackage = setInterval(function(){
        for(var jj=0;jj<$('.div>div').size()/4;jj++){
            $('.div>div').eq($('.div>div').size()-jj).remove();
        }
    },1000)
    function aa(){
        for(var i=0;i<4;i++){
            var m=parseInt(Math.random()*700+100);
            var j2=parseInt(Math.random()*300+1200);
            var j=parseInt(Math.random()*1600+000);
            var j1=parseInt(Math.random()*300+300);
            var n=parseInt(Math.random()*10+(-10));
            $('.div').prepend('<div class="dd"></div>');
            $('.div').children('div').eq(0).css({'left':j,'top':n});
            $('.div').children('div').eq(0).animate({'left':j-j1,'top':$(window).height()+20},3000);
        }
    }
    $(document).on('touchstart', '.dd', function(){
        $(this).css("background-position","0 -100px");
        a++;
        if(a == 5){
            $(".chuai_box").show();
            clearInterval(Timerr,20);
            $(".div").removeClass("bg_1");
            setTimeout(function(){
                $(".div").addClass("bg_2");
            },3000);
        }
    });
</script>
<body>
<div class="page_rain">
    <div class="div bg_1"></div>
    <!--蒙层-->
    <div class="chuai_box" style="display: none;">
        <div class="chuai">
            <p>常州克里斯特尔斯甜品店红包</p>
        </div>
    </div>
</div>

<div style="text-align:center;margin:50px 0; font:normal 14px/24px 'MicroSoft YaHei';">
    <!--<p>适用浏览器：360、FireFox、Chrome、Safari、Opera、傲游、搜狗、世界之窗. 不支持IE8及以下浏览器。</p>-->
    <!--<p>来源：<a href="http://sc.chinaz.com/" target="_blank">站长素材</a></p>-->
</div>
</body>
</html>