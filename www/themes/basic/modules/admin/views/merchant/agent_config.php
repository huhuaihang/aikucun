<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\City;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\AgentFee[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '代理设置';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="form-group">
    <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/merchant/edit-agent-config']);?>">添加</a>
</div>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>省</th>
        <th>市</th>
        <th>区</th>
        <th>保证金</th>
        <th>加盟费用</th>
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
            <?php $city = City::findByCode($model->area)->address();?>
            <td><?php echo $city[0];?></td>
            <td><?php echo empty($city[1])? '' : $city[1];?></td>
            <td><?php echo empty($city[2])? '' : $city[2];?></td>
            <td><?php echo Html::encode($model->earnest_money);?></td>
            <td><?php echo Html::encode($model->initial_fee);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/merchant/edit-agent-config', 'id' =>
                        $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteAgentFee(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    function deleteAgentFee(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/delete-agent-config']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
