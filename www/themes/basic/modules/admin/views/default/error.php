<?php

use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $exception \yii\web\HttpException
 */

$this->title = '系统发生错误';
?>
<p>系统发生错误</p>
<p><?= nl2br(Html::encode($exception->getMessage())) ?></p>
<a href="<?php echo Url::to(['/admin']);?>">返回主页</a>
<a href="javascript:void(0)" onclick="window.history.go(-1)">返回上一页</a>

<?php if (YII_ENV_DEV) {?>
    <pre><?php print_r($exception->getTraceAsString());?></pre>
<?php }?>
