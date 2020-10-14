<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Agent;
use app\models\City;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Agent[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '代理商列表';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_id" class="sr-only">编号</label>
    <?php echo Html::textInput('search_id', Yii::$app->request->get('search_id'), ['id' => 'search_id', 'class' => 'form-control', 'placeholder' => '编号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_area" class="sr-only">代理区域</label>
    <?php echo Html::hiddenInput('search_area', Yii::$app->request->get('search_area'), ['id' => 'search_area']);?>
    <div id="citys">
        <select name="province" class="form-control"></select>
        <select name="city" class="form-control"></select>
        <select name="area" class="form-control"></select>
    </div>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<?php if (Yii::$app->manager->can('merchant/edit-agent')) {?>
    <div class="form-group">
        <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/merchant/edit-agent']);?>">添加</a>
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
        <th>登录邮箱</th>
        <th>手机号</th>
        <th>联系人</th>
        <th>区域</th>
        <th>代理商状态</th>
        <th>保证金</th>
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
            <td><?php echo Html::a(Html::encode($model->username), '');?></td>
            <td><?php echo $model->mobile;?></td>
            <td><?php echo Html::encode($model->contact_name);?></td>
            <td><?php echo implode(' ', City::findByCode($model->area)->address());?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('agent_status', $model->status);?></span></td>
            <td><?php if (!empty($model->earnest_money_fid)) {
                    echo $model->earnestMoneyFinanceLog->money, '<br />';
                    echo KeyMap::getValue('finance_log_pay_method', $model->earnestMoneyFinanceLog->pay_method), '<br />';
                    echo KeyMap::getValue('finance_log_status', $model->earnestMoneyFinanceLog->status);
                }?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/merchant/edit-agent', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ($model->status != Agent::STATUS_ACTIVE) ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ($model->status != Agent::STATUS_STOPED) ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteAgent(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    function page_init() {
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('#search_area').val(),
            required: false,
            placeholder: ' - 搜索区域 - ',
            onChange: function(city) {
                if(city['code'] !='0'){
                    $('#search_area').val(city['code']);
                }else{
                    $('#search_area').val('');
                }
            }
        });
    }

    /**
     * 删除代理商
     * @param id 代理商编号
     */
    function deleteAgent(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/delete-agent']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    /**
     * 设置商户状态
     */
    function toggleStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/status-agent'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }
</script>
