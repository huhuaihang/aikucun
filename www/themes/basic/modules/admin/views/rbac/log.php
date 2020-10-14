<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $model_list app\models\ManagerLog[]
 * @var $pagination yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '管理日志';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['scrollTo' => 0]);?>
<?php echo Html::beginForm('?', 'get', ['class'=>'form-inline']);?>
    <?php echo Html::hiddenInput('search_mid', Yii::$app->request->get('search_mid'));?>
    <div class="form-group">
        <label for="search_username" class="sr-only">Username</label>
        <?php echo Html::textInput('search_username', Yii::$app->request->get('search_username'), ['id'=>'search_username', 'class'=>'form-control', 'placeholder'=>'用户名', 'style'=>'max-width:100px;']);?>
    </div>
    <div class="form-group">
        <label for="search_content" class="sr-only">Content</label>
        <?php echo Html::textInput('search_content', Yii::$app->request->get('search_content'), ['id'=>'search_content', 'class'=>'form-control', 'placeholder'=>'操作']);?>
    </div>
    <div class="form-group">
        <label for="search_start_date" class="sr-only">StartDate</label>
        <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id'=>'search_start_date', 'class'=>'form-control masked', 'placeholder'=>'开始日期', 'data-mask'=>'9999-99-99', 'style'=>'max-width:90px;']);?>
        -
        <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id'=>'search_end_date', 'class'=>'form-control masked', 'placeholder'=>'结束日期', 'data-mask'=>'9999-99-99', 'style'=>'max-width:90px;']);?>
    </div>
    <div class="form-group">
        <button class="btn btn-primary btn-sm">搜索</button>
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
            <th>用户名</th>
            <th>操作时间</th>
            <th>IP地址</th>
            <th>操作记录</th>
            <th>操作</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($model_list as $model) {?>
            <tr>
                <td class="center"><label class="pos-rel"><input type="checkbox" class="ace" value="<?php echo $model->id;?>" /><span class="lbl"><?php echo $model->id;?></span></label></td>
                <td><?php echo Html::encode($model->manager->username);?></td>
                <td><?php echo Yii::$app->formatter->asDatetime($model->time);?></td>
                <td><?php echo Html::encode($model->ip);?></td>
                <td><?php echo Html::encode($model->content);?></td>
                <td><?php echo ManagerTableOp::widget(['items' => [
                        ['icon' => 'fa fa-info-circle', 'onclick' => 'showData(' . $model->id . ')', 'btn_class' => 'btn btn-default btn-xs', 'tip' => '详情'],
                    ]]);?></td>
            </tr>
        <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<?php Pjax::end();?>
<script>
    function showData(id) {
        $.getJSON('<?php echo Url::to(['/admin/rbac/log-detail']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                layer.open({
                    type: 1,
                    title: json['log']['content'],
                    closeBtn: 0,
                    shadeClose: true,
                    content: '<pre>' + json['log']['data'] + '</pre>'
                });
            }
        });
    }
</script>
