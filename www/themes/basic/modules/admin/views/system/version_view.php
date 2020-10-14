<?php

use app\models\KeyMap;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $version app\models\SystemVersion
 */

$this->title = '版本详情';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th>接口版本号</th>
        <td><?php echo $version->api_version;?></td>
    </tr>
    <tr>
        <th>苹果版本号</th>
        <td><?php echo $version->ios_version;?></td>
    </tr>
    <tr>
        <th>安卓版本号</th>
        <td><?php echo $version->android_version;?></td>
    </tr>
    <tr>
        <th>是否支持</th>
        <td><?php echo KeyMap::getValue('yes_no', $version->is_support);?></td>
    </tr>
    <tr>
        <th>AES加密密钥</th>
        <td><?php echo Html::encode($version->aes_key);?></td>
    </tr>
    <tr>
        <th>AES加密初始化向量</th>
        <td><?php echo Html::encode($version->aes_iv);?></td>
    </tr>
    <tr>
        <th>安卓下载来源</th>
        <td><?php echo KeyMap::getValue('android_download_source', $version->android_download_source);?></td>
    </tr>
    <tr>
        <th>安卓下载地址</th>
        <td><?php echo Html::encode($version->android_download_url);?></td>
    </tr>
    <tr>
        <th>更新信息</th>
        <td><?php echo nl2br(Html::encode($version->update_info));?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($version->create_time);?></td>
    </tr>
</table>
