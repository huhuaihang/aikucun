<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\System;
use app\models\UserWithdraw;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\UserWithdraw[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '用户提现列表';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_account_name" class="sr-only">账号名称</label>
    <?php echo Html::textInput('search_account_name', Yii::$app->request->get('search_account_name'), ['id' => 'search_account_name', 'class' => 'form-control', 'placeholder' => '账户名', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_account_name" class="sr-only">账号名称</label>
    <?php echo Html::textInput('search_account_no', Yii::$app->request->get('search_account_no'), ['id' => 'search_account_no', 'class' => 'form-control', 'placeholder' => '账号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('user_withdraw_status'), ['prompt' => '状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br>
<div class="form-group">
<!--    <a class="btn btn-success btn-sm" href="--><?php //echo Url::to(['/admin/user/level-edit']);?><!--">添加</a>-->
</div>
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'excel']);?>" class="btn btn-info btn-sm">导出</a>
</div>
<div class="form-group">
    <a href="<?php echo Url::current(['export' => 'search_excel']);?>" class="btn btn-info btn-sm">搜索导出</a>
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
        <th>用户</th>
        <th>提现金额</th>
        <th>提现服务费</th>
        <th>合伙费</th>
        <th>扣除总费</th>
        <th>实际到账</th>
        <th>银行名</th>
        <th>开户行所在地</th>
        <th>账户名</th>
        <th>账号</th>
        <th>创建时间</th>
        <th>通过时间</th>
        <th>完毕时间</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php
    /** @var UserWithdraw $model */
    foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo  Html::encode($model->user->nickname);?></td>
            <td><?php echo  Html::encode($model->money);?></td>
            <td><?php echo  Html::encode($model->money * (1/100));?></td>
            <td><?php echo  Html::encode($model->money * (5/100));?></td>
            <td><?php echo  Html::encode($model->money * (System::getConfig('subsidy_withdraw_point')/100));?></td>
            <td><?php echo  Html::encode($model->money - ($model->money * ( System::getConfig('subsidy_withdraw_point')/100)));?></td>
            <td><?php echo $model->bank_name;?></td>
            <td><?php echo $model->bank_address;?></td>
            <td><?php echo $model->account_name;?></td>
            <td><?php echo $model->account_no;?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->apply_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->finish_time);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('user_withdraw_status', $model->status)?></span></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/user/withdraw-view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    $model->status != UserWithdraw::STATUS_WAIT ? '' : ['icon' => 'fa fa-check', 'onclick'=> 'accept('.$model->id.')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '通过', 'color' => 'yellow'],
                    $model->status != UserWithdraw::STATUS_OK ? '' : ['icon' => 'fa fa-check', 'onclick'=> 'finish('.$model->id.')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '完毕', 'color' => 'red'],
                    ($model->status == UserWithdraw::STATUS_FINISH || $model->status == UserWithdraw::STATUS_REFUSE) ? '' : ['icon' => 'fa fa-close', 'onclick'=> 'refuse('.$model->id.')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '拒绝', 'color' => 'yellow'],
//                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteWithdraw(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
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
    function deleteWithdraw(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/delete-withdraw']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    /**
     * 审核通过提现
     * @param id
     * @returns {boolean}
     */
    function accept(id){
        if (!confirm('确定要审核通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/accept-withdraw'])?>', {'id':id, 'status': 'accept'}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    /**
     * 完毕提现
     * @param id
     * @returns {boolean}
     */
    function finish(id){
        if (!confirm('确定要完毕吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/finish-withdraw'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    /**
     * 审核拒绝提现
     * @param id
     * @returns {boolean}
     */
    function refuse(id){
        if (!confirm('确定要拒绝吗？')) {
            return false;
        }
        layer.prompt({title: '输入拒绝备注', formType: 2}, function(text, index){
            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/user/reject-withdraw'])?>', {'id':id, 'remark':text}, function(json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }
</script>
