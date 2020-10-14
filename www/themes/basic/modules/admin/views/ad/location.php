<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\widgets\ManagerTableOp;
use app\widgets\LinkPager;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\AdLocation[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '位置列表';
$this->params['breadcrumbs'][] = '广告管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
    <div class="form-group">
        <label for="search_name" class="sr-only">Name</label>
        <input class="form-control" name="search_name" id="search_name" placeholder="名称" value="<?php echo Yii::$app->request->get('search_name');?>" />
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-sm">搜索</button>
    </div>
<div class="form-group">
<!--    <a href="--><?php //echo Url::to(['/admin/ad/location?type=1']);?><!--"  class="btn btn-success btn-sm">一键更新商品分类广告位</a>-->
</div>
    <br />
    <div class="form-group">
        <a  href="<?php echo Url::to(['/admin/ad/edit-location']);?>"   class="btn btn-success btn-sm">添加</a>
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
            <th>广告类型</th>
            <th>位置说明</th>
            <th>最大展示数量</th>
            <th>宽度</th>
            <th>高度</th>
            <th>备注</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model_list as $model) {?>
            <tr id="data_<?php echo $model->id;?>">
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $model->id;?>" /><span class="lbl"><?php echo $model->id;?></span></label></td>
                <td><?php echo KeyMap::getValue('ad_type', $model->type);?>
                <td><?php echo Html::encode($model->name);?></td>
                <td><?php echo $model->max_count;?></td>
                <td><?php echo $model->width;?></td>
                <td><?php echo $model->height;?></td>
                <td><?php echo Html::encode($model->remark);?></td>
                <td><?php echo ManagerTableOp::widget(['items'=>[
                    ['rbac'=>'ad/location', 'icon' => 'fa fa-pencil', 'btn_class'=>'btn btn-xs btn-success', 'color'=>'green', 'tip'=>'修改', 'href'=>Url::to(['/admin/ad/edit-location', 'id'=>$model->id])],
                    ['rbac'=>'ad/location', 'icon' => 'fa fa-refresh', 'btn_class'=>'btn btn-xs btn-success', 'color'=>'green', 'tip'=>'清除Smarty缓存', 'onclick'=>'clearSmartyCache(' . $model->id . ')'],
                    ['rbac'=>'ad/edit', 'icon' => 'fa fa-plus', 'btn_class'=>'btn btn-xs btn-default', 'tip'=>'添加广告', 'href'=>Url::to(['/admin/ad/edit', 'lid'=>$model->id])],
                    ['rbac'=>'ad/list', 'icon' => 'fa fa-list', 'btn_class'=>'btn btn-xs btn-default', 'tip'=>'广告列表', 'href'=>Url::to(['/admin/ad/list', 'search_lid'=>$model->id])],
                ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination'=>$pagination]);?>
<script>
    /**
     * 清除Smarty缓存
     * @param id 位置编号
     */
    function clearSmartyCache(id) {
        $.getJSON('<?php echo Url::to(['/admin/ad/clear-smarty-cache']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                layer.msg('缓存已清除。', function() {});
            }
        });
    }
</script>
