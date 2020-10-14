<?php

use app\assets\H5Asset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\HttpException;

/**
 * @var $this \yii\web\View
 * @var $exception \yii\web\HttpException
 */

H5Asset::register($this);

$this->title = '出现错误';

$is_404 = $exception instanceof HttpException && $exception->statusCode == 404;
?>
<div class="box">
    <!--头部-->
    <div class="b_nav1 clearfix">
        <a class="b_fanhui" href="javascript:void(0);" onClick="window.history.go(-1)"><img src="/images/11_1.png"></a>
        <h5>出错了</h5>
    </div>
    <?php if ($is_404) {?>
        <h1 class="b_sys_tit">系统发生错误</h1>
        <p class="b_sys_cont"><?php echo Html::encode($exception->getMessage());?></p>
        <div class="b_sys_e">
            <img src="/images/system_03.png"/>
        </div>
        <a class="b_sys_back" href="javascript:void(0)" onclick="window.history.go(-1)">返回重试</a>
    <?php } else {?>
        <div class="b_error_area b_magt1">
            <dl class="b_drop_down">
                <dt>
                    <img src="/images/b_xiajia_03.png" alt=""/>
                </dt>
                <dd><?php echo Html::encode($exception->getMessage());?></dd>
            </dl>
            <div class="b_sold_back">
                <a class="b_dropa1" href="<?php echo Url::to(['/']);?>">返回首页</a>
                <a class="b_dropa2" href="javascript:void(0)" onclick="window.history.go(-1)">返回上一页</a>
            </div>
        </div>
    <?php }?>
</div>
<?php if (YII_ENV_DEV) {?>
    <pre><?php print_r($exception->getTraceAsString());?></pre>
<?php }?>
