<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\UserLevel[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '销售员等级列表';
$this->params['breadcrumbs'][] = '销售员管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_name" class="sr-only">等级名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '等级名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/user/level-edit']);?>">添加</a>
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
        <th>等级名称</th>
        <th>等级LOGO</th>
<!--        <th>等级金额</th>-->
<!--        <th>一级/二级/三级 补贴</th>-->
        <th>直接/2代/无限代 比例</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo  Html::encode($model->name);?></td>
            <td><img src="<?php echo Yii::$app->params['upload_url'] . $model->logo;?>_80x40"></td>
<!--            <td>--><?php //echo$model->money;?><!--</td>-->
<!--            <td>--><?php //echo $model->money_1, '-', $model->money_2, '-', $model->money_3, '';?><!--</td>-->
            <td><?php echo $model->commission_ratio_1, '%-', $model->commission_ratio_2, '%-', $model->commission_ratio_3,   '%';?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/user/level-view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/user/level-edit', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '编辑', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteLevel(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 删除用户等级
     * @param id 等级编号
     */
    function deleteLevel(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/delete-level']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
