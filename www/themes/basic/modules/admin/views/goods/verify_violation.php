<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\ViolationType;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\GoodsViolation[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '违规商品列表';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_name" class="sr-only">店铺名称</label>
    <?php echo Html::textInput('search_shop', Yii::$app->request->get('search_shop'),
        ['id' => 'search_shop', 'class' => 'form-control', 'placeholder' => '店铺名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_vid" class="sr-only">违规类型</label>
    <?php echo Html::dropDownList('search_vid', Yii::$app->request->get('search_vid'), ViolationType::find()
        ->select(['name','id'])
        ->indexBy('id')
        ->column(), ['prompt'=>'请选择违规类型'], ['id' => 'search_vid', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">商品名称</label>
    <?php echo Html::textInput('search_title', Yii::$app->request->get('search_title'), ['id' => 'search_title', 'class' => 'form-control', 'placeholder' => '商品名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>
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
        <th>商品名称</th>
        <th>商户名称</th>
        <th>商品违规类型</th>
        <th>状态</th>
        <th>添加时间</th>
        <th>操作</th>
    </tr>
    </thead>
    <?php foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($model->goods->title);?></td>
            <td><?php echo Html::encode($model->goods->shop->name);?></td>
            <td><?php echo empty($model->vid) ? "" : Html::encode($model->violationType->name);?></td>
            <td><?php echo KeyMap::getValue('goods_violation_status', $model->status)?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-check', 'onclick'=> 'accept('.$model->id.')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '通过', 'color' => 'yellow'],
                    ['icon' => 'fa fa-close', 'onclick'=> 'refuse('.$model->id.')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '拒绝', 'color' => 'yellow'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>

    function accept(id){
        if (!confirm('确定要审核通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/goods/status-violation'])?>', {'id':id, 'status': 'accept'}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    function refuse(id){
        if (!confirm('确定要拒绝吗？')) {
            return false;
        }
        layer.prompt({title: '输入拒绝备注', formType: 2}, function(text, index){
            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/goods/status-violation'])?>', {'id':id, 'status': 'refuse', 'remark':text}, function(json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }

</script>