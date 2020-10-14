<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $template_list \app\models\ExpressPrintTemplate[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '打印模板列表';
$this->params['breadcrumbs'][] = '物流快递公司管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<?php if (!empty(Yii::$app->request->get('search_eid'))) {?>
    <div class="form-group">
        <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/express/edit-print-template', 'eid' => Yii::$app->request->get('search_eid')]);?>">添加</a>
    </div>
<?php }?>
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
        <th>图片</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($template_list as $template) {?>
        <tr id="data_<?php echo $template->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $template->id;?>"/>
                    <span class="lbl"><?php echo $template->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($template->express->name);?></td>
            <td><?php echo Html::img(Yii::$app->params['upload_url'] . $template->background_image, ['width' => 128]);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/express/print-template-view', 'id' => $template->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/express/edit-print-template', 'id' => $template->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
