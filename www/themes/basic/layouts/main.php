<?php

use app\assets\BaseAsset;
use app\models\System;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $content string
 */

BaseAsset::register($this);
$this->registerJs('try{page_init();}catch(e){}');

if (empty($this->title)) {
    $this->title = System::getConfig('site_index_title');
}
if (!isset($this->metaTags['keywords']) || empty($this->metaTags['keywords'])) {
    $this->registerMetaTag(['name' => 'keywords', 'content' => System::getConfig('site_index_keywords')], 'keywords');
}
if (!isset($this->metaTags['description']) || empty($this->metaTags['description'])) {
    $this->registerMetaTag(['name' => 'description', 'content' => System::getConfig('site_index_desc')], 'description');
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=0,minimum-scale=1.0,maximum-scale=1.0"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
