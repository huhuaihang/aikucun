<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Ad;
use app\models\AdLocation;
use app\models\KeyMap;
use app\models\Util;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\Ad[]
 * @var $pagination yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '广告列表';
$this->params['breadcrumbs'][] = '广告管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('', 'get', ['class'=>'form-inline']);?>
    <div class="form-group">
        <label for="search_lid" class="sr-only">Lid</label>
        <?php echo Html::dropDownList('search_lid', Yii::$app->request->get('search_lid'), [''=>'查找广告位置'] + ArrayHelper::map(AdLocation::find()->all(), 'id', 'name'), ['class'=>'form-control']);?>
    </div>
    <div class="form-group">
        <label for="search_name" class="sr-only">Name</label>
        <input class="form-control" name="search_name" id="search_name" placeholder="名称" value="<?php echo Yii::$app->request->get('search_name');?>" />
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-sm">搜索</button>
    </div>
    <br />
    <div class="form-group">
        <a href="<?php echo Url::to(['/admin/ad/edit']);?>" class="btn btn-success btn-sm">添加</a>
    </div>
<?php echo Html::endForm();?>
<table id="simple-table" class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" />
                    <span class="lbl"></span>
                </label>
            </th>
            <th>名称</th>
            <th>位置</th>
            <th>文字</th>
            <th>图片</th>
            <th>时间</th>
            <th>状态</th>
            <th>统计</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model_list as $model) {?>
            <tr id="data_<?php echo $model->id;?>">
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $model->id;?>" /><span class="lbl"><?php echo $model->id;?></span></label></td>
                <td><?php echo Html::encode($model->name);?></td>
                <td><?php echo Html::encode($model->location->name);?></td>
                <td><?php echo Html::encode($model->txt);?></td>
                <td><?php if (!empty($model->img)) {echo Html::img(Util::fileUrl($model->img, true), ['width' => 100]);}?></td>
                <td><?php echo Yii::$app->formatter->asDatetime($model->start_time), '<br />', Yii::$app->formatter->asDatetime($model->end_time);?></td>
                <td><span class="label label-default"><?php echo KeyMap::getValue('ad_status', $model->status);?></span></td>
                <td><?php echo $model->click, '/', $model->show;?></td>
                <td><?php echo ManagerTableOp::widget(['items'=>[
                        ['icon' => 'fa fa-pencil', 'btn_class'=>'btn btn-xs btn-success', 'color'=>'green', 'tip'=>'修改', 'href'=>Url::to(['/admin/ad/edit', 'id'=>$model->id])],
                        $model->status != Ad::STATUS_STOPED ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleAdStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                        $model->status != Ad::STATUS_ACTIVE ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleAdStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                        ['icon' => 'fa fa-trash', 'btn_class'=>'btn btn-xs btn-danger', 'color'=>'red', 'tip'=>'删除', 'onclick'=>'deleteAd(' . $model->id . ')'],
                ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination'=>$pagination]);?>
<script>
/**
 * 删除广告
 */
function deleteAd(id) {
    if (!confirm('确定要删除吗？')) {
        return false;
    }
    $.getJSON('<?php echo Url::to(['/admin/ad/delete']);?>', {'id':id}, function(json) {
        if (callback(json)) {
            $('#data_' + id).remove();
        }
    });
}
/**
 * 设置广告状态
 */
function toggleAdStatus(id) {
    $.getJSON('<?php echo Url::to(['/admin/ad/status']);?>', {'id':id}, function(json) {
        if (callback(json)) {
            window.location.reload();
        }
    });
}
</script>
