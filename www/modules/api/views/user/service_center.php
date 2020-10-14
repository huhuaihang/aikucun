<?php

use app\assets\H5Asset;
use app\models\System;

/**
 * @var $this \yii\web\View
 */

H5Asset::register($this);
$this->title = '客服中心';
?>
<div class="">
    <!--客服中心-->
    <div class="">
        <?php echo System::getConfig('site_contact');?>
    </div>
</div>
