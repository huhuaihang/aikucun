<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $version_list app\models\SystemVersion[]
 */

TableAsset::register($this);

$this->title = '版本管理';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
<div class="form-group">
    <a href="<?php echo Url::to(['/admin/system/version-edit']);?>" class="btn btn-success btn-sm">添加</a>
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
        <th>接口版本号</th>
        <th>IOS版本号</th>
        <th>Android版本号</th>
        <th>是否支持</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($version_list as $version) {?>
        <tr id="data_<?php echo $version->id;?>">
            <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $version->id;?>" /><span class="lbl"><?php echo $version->id;?></span></label></td>
            <td><?php echo $version->api_version;?></td>
            <td><?php echo $version->ios_version;?></td>
            <td><?php echo $version->android_version;?></td>
            <td><?php echo KeyMap::getValue('yes_no', $version->is_support);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($version->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['rbac' => 'system/version', 'icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/system/version-view', 'id' => $version->id]), 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                    ['rbac' => 'system/version', 'icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/system/version-edit', 'id' => $version->id]), 'btn_class' => 'btn btn-success btn-xs', 'tip' => '修改'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
