<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Express;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Express[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '快递公司列表';
$this->params['breadcrumbs'][] = '物流快递公司管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_id" class="sr-only">编号</label>
    <?php echo Html::textInput('search_id', Yii::$app->request->get('search_id'), ['id' => 'search_id', 'class' => 'form-control', 'placeholder' => '编号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_name" class="sr-only">物流公司</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '物流公司', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_code" class="sr-only">物流编号</label>
    <?php echo Html::textInput('search_code', Yii::$app->request->get('search_code'), ['id' => 'search_code', 'class' => 'form-control', 'placeholder' => '物流编号', 'style' => 'max-width:100px;']);?>
</div>

<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>

<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/express/edit-express']);?>">添加</a>
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
        <th>物流公司</th>
        <th>物流编号</th>
        <th>状态</th>
        <th>排序</th>
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
            <td><?php echo Html::a(Html::encode($model->name), '');?></td>
            <td><?php echo $model->code;?></td>
            <td><?php echo KeyMap::getValue('express_status', $model->status);?></td>
            <td><?php echo $model->sort;?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/express/edit-express', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ($model->status != Express::STATUS_OK) ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ($model->status != Express::STATUS_PAUSE) ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-print', 'href' => Url::to(['/admin/express/print-template', 'search_eid' => $model->id]), 'btn_class' => 'btn btn-xs btn-info', 'tip' => '打印模板', 'color' => 'blue'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 设置物流快递公司状态
     */
    function toggleStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/express/status-express'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
