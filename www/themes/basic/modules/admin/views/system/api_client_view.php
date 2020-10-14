<?php

use app\models\KeyMap;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $client app\models\ApiClient
 */

$this->title = '接口客户端详情';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th>名称</th>
        <td><?php echo Html::encode($client->name);?></td>
    </tr>
    <tr>
        <th>AppId</th>
        <td><?php echo $client->app_id;?></td>
    </tr>
    <tr>
        <th>AppSecret</th>
        <td><?php echo $client->app_secret;?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('api_client_status', $client->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($client->create_time);?></td>
    </tr>
</table>
