<?php
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $ad_list app\models\Ad[]
 */

foreach ($ad_list as $model) {
    echo Html::a(Html::encode($model->txt), ['/site/da', 'id' => $model->id]);
}
