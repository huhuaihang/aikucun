<?php

use app\widgets\Redirect;
use cornernote\ace\widgets\Alert;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * @var $this \yii\web\View
 * @var $content string
 */
?>
<div class="main-content">
    <?php if (isset($this->params['breadcrumbs'])) { ?>
        <div class="breadcrumbs breadcrumbs-fixed">
            <?= Breadcrumbs::widget([
                'homeLink' => ['label' => '管理后台', 'url' => ['/admin']],
                'links' => $this->params['breadcrumbs'],
            ]) ?>
            <div class="nav-search" id="nav-search">
                <form class="form-search" method="get" action="<?php echo Url::to(['/admin/default/search']);?>">
                    <span class="input-icon">
                        <input placeholder="Search ..." class="nav-search-input" autocomplete="off"
                               name="keyword" value="<?php echo Yii::$app->request->get('keyword'); ?>"/>
                        <i class="ace-icon fa fa-search nav-search-icon"></i>
                    </span>
                </form>
            </div><!-- /.nav-search -->
        </div>
    <?php } ?>

    <div class="page-content">

        <div class="page-header">
            <h1>
                <?= $this->title ?>
            </h1>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <?= Alert::widget() ?>
                <?= Redirect::widget() ?>
                <?= $content ?>
            </div>
        </div>

    </div>
</div>
