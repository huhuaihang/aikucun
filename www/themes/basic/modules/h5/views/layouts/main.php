<?php

use app\assets\H5Asset;
use app\models\System;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

H5Asset::register($this);
$this->registerJsFile('https://cdn.bootcss.com/babel-polyfill/6.23.0/polyfill.min.js', ['position' => 1]);

$this->beginContent('@app/themes/basic/layouts/main.php');?>
<div id="loading" style="max-width:750px;width: 100%; position: fixed; top:0; height: 100%; background-color: rgb(255, 255, 255); z-index: 9999;">
    <p style="width:25%; margin:55% auto 0;"><img src="/images/loading.gif" style="width:width:100%;"></p>
</div>
<?php $this->registerJs('$("#loading").remove();');?>
<?php echo $content;?>
<?php $site_statistics = System::getConfig('site_statistics');
if (!empty($site_statistics)) {
    echo $site_statistics;
}?>
<?php $this->endContent();
