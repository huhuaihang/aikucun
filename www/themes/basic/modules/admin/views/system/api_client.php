<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $client_list app\models\ApiClient[]
 */

TableAsset::register($this);

$this->title = '接口客户端管理';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
<div class="form-group">
    <a href="<?php echo Url::to(['/admin/system/api-client-edit']);?>" class="btn btn-success btn-sm">添加</a>
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>名称</th>
        <td>AppId</td>
        <td>AppSecret</td>
        <td>状态</td>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($client_list as $client) {?>
        <tr id="data_<?php echo $client->id;?>">
            <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $client->id;?>" /><span class="lbl"><?php echo $client->id;?></span></label></td>
            <td><?php echo Html::encode($client->name);?></td>
            <td><?php echo $client->app_id;?></td>
            <td><?php echo $client->app_secret;?></td>
            <td><?php echo KeyMap::getValue('api_client_status', $client->status);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($client->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['rbac' => 'system/api-client', 'icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/system/api-client-view', 'id' => $client->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                    ['rbac' => 'system/api-client', 'icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/system/api-client-edit', 'id' => $client->id]), 'btn_class' => 'btn btn-success btn-xs', 'tip' => '修改'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
