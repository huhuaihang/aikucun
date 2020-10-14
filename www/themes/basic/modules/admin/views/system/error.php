<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\SystemError;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this yii\web\View
 * @var $error_list app\models\SystemError[]
 * @var $pagination yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '错误日志';
$this->params['breadcrumbs'][] = '系统管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
<div class="form-group">
    <button type="submit" class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <button type="button" class="btn btn-warning btn-sm" onclick="deleteBatch()">删除</button>
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
            <th>内容</th>
            <th>代码</th>
            <th>时间</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($error_list as $error) {?>
            <tr id="data_<?php echo $error->id;?>">
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $error->id;?>" /><span class="lbl"><?php echo $error->id;?></span></label></td>
                <td><?php echo Html::encode($error->message);?></td>
                <td><?php echo Html::encode($error->code);?></td>
                <td><?php echo Yii::$app->formatter->asDatetime($error->time);?></td>
                <td><?php echo ManagerTableOp::widget(['items'=>[
                        ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/system/error-view', 'id' => $error->id]), 'btn_class' => 'btn btn-default btn-xs' . ($error->status == SystemError::STATUS_WAIT ? '  btn-danger' : ''), 'tip' => '详情'],
                    ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination'=>$pagination]);?>
<script>
    /**
     * 批量删除
     */
    function deleteBatch() {
        var ids = [];
        $('input[type=checkbox]:checked').each(function () {
            var id = $(this).val();
            if (id !== undefined && !isNaN(id)) {
                ids.push(id);
            }
        });
        $.getJSON('<?php echo Url::to(['/admin/system/delete-error']);?>', {'ids': ids}, function (json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
