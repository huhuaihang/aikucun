<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\User;
use app\models\UserCardLevel;
use app\widgets\FileUploadWidget;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\User[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '用户列表';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_mobile" class="sr-only">手机号码</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号码', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_real_name" class="sr-only">真实姓名</label>
    <?php echo Html::textInput('search_real_name', Yii::$app->request->get('search_real_name'), ['id' => 'search_real_name', 'class' => 'form-control', 'placeholder' => '真实姓名', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_nickname" class="sr-only">昵称</label>
    <?php echo Html::textInput('search_nickname', Yii::$app->request->get('search_nickname'), ['id' => 'search_nickname', 'class' => 'form-control', 'placeholder' => '昵称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">上级手机号码</label>
    <?php echo Html::textInput('search_p_mobile', Yii::$app->request->get('search_p_mobile'), ['id' => 'search_p_mobile', 'class' => 'form-control', 'placeholder' => '上级手机号码', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">团队上级手机号码</label>
    <?php echo Html::textInput('search_team_p_mobile', Yii::$app->request->get('search_team_p_mobile'), ['id' => 'search_team_p_mobile', 'class' => 'form-control', 'placeholder' => '团队上级手机号码', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">上级ID</label>
    <?php echo Html::textInput('search_p_id', Yii::$app->request->get('search_p_id'), ['id' => 'search_p_id', 'class' => 'form-control', 'placeholder' => '上级ID', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">团队上级ID</label>
    <?php echo Html::textInput('search_team_p_id', Yii::$app->request->get('search_team_p_id'), ['id' => 'search_team_p_id', 'class' => 'form-control', 'placeholder' => '团队上级ID', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('user_status'), ['prompt' => '激活状态', 'class' => 'form-control']);?>
</div>
<!--<div class="form-group">-->
<!--    <label for="search_start_date" class="sr-only">激活时间</label>-->
<!--    --><?php //echo Html::textInput('search_handle_start_date', Yii::$app->request->get('search_handle_start_date'), ['id' => 'search_handle_start_date', 'placeholder' => '激活开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<!--    --->
<!--    --><?php //echo Html::textInput('search_handle_end_date', Yii::$app->request->get('search_handle_end_date'), ['id' => 'search_handle_end_date', 'placeholder' => '激活结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<!--</div>-->
<div class="form-group">
    <label for="search_start_date" class="sr-only">注册时间</label>
    <?php echo Html::textInput('search_create_start_date', Yii::$app->request->get('search_create_start_date'), ['id' => 'search_create_start_date', 'placeholder' => '注册开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_create_end_date', Yii::$app->request->get('search_create_end_date'), ['id' => 'search_create_end_date', 'placeholder' => '注册结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br>
<div class="form-group">
    <a href="<?php echo Url::to(['/admin/user/edit']);?>" class="btn btn-info btn-sm">添加</a>
<!--    <a href="--><?php //echo Url::to(['/admin/user/handle-mobile-sub']);?><!--" target="_blank" class="btn btn-info btn-sm">手动添加补贴</a>-->
<!--    <a href="--><?php //echo Url::to(['/admin/user/handle-active-sub']);?><!--" target="_blank" class="btn btn-info btn-sm">发放活动奖励补贴</a>-->
<!--    <a href="--><?php //echo Url::to(['/admin/user/send-pack-coupon']);?><!--" target="_blank" class="btn btn-info btn-sm">发放礼包兑换券</a>-->
<!--    <a href="--><?php //echo Url::to(['/admin/user/send-active-score']);?><!--" target="_blank" class="btn btn-info btn-sm">手动发放活动积分</a>-->
<!--    <button type="button" class="btn btn-info btn-sm" id="import">导入会员CSV文件</button>-->
    <?php echo FileUploadWidget::widget([
        'name' => 'import',
        'url' => Url::to(['/admin/user/import']),
        'click_node' => '#import',
        'callback' => 'importCallback',
    ]);?>
    <script>
        function importCallback(url) {
            layer.msg('导入完成。', function () {
                window.location.reload();
            });
        }
    </script>
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
        <th>手机号</th>
        <th>识别码</th>
        <th>微信号</th>
        <th>昵称</th>
        <th>真实姓名</th>
        <th>上级</th>
        <th>团队上级</th>
        <th>等级</th>
<!--        <th>状态</th>-->
        <th>注册时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php
    /** @var User $model */
    foreach ($model_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo $model->mobile;?></td>
            <td><?php echo $model->invite_code;?></td>
            <td><?php echo Html::encode($model->wx_no);?></td>
            <td><?php echo Html::encode($model->nickname);?></td>
            <td><?php echo Html::encode($model->real_name);?></td>
            <td><?php echo empty($model->parent) ? '' : Html::encode($model->parent->real_name). '<br>' . $model->parent->mobile;?></td>
            <td><?php if (!empty($model->team_pid) && !empty($model->getTeamParent($model->team_pid))) { echo $model->teamParents->real_name . "<br>" . $model->teamParents->mobile;}?></td>
            <td><?php echo ($model->account->level_money == 0 && empty($model->userLevel)) ? '待激活': Html::encode($model->userLevel->name).'<br> (比率)'.
                    $model->commission_ratio_1, '%-', $model->commission_ratio_2, '%-', $model->commission_ratio_3,   '%';;?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/user/view', 'id' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '详情','alt' => '详情'],
                    ['icon' => 'fa fa-users', 'href' => Url::to(['/admin/user/relation-view', 'id' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '团队图谱','alt' => '团队图谱'],
                    ['icon' => 'fa fa-bars', 'href' => Url::to(['/admin/user/account-list', 'uid' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '结算单列表记录'],
//                    ['icon' => 'fa fa-bars', 'href' => Url::to(['/admin/user/growth-list', 'uid' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '成长值记录'],
                    ['icon' => 'fa fa-btc', 'href' => Url::to(['/admin/user/account-log-edit', 'uid' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '增加结算单'],
//                    ['icon' => 'fa  fa-leaf', 'href' => Url::to(['/admin/user/score-list', 'uid' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '积分记录'],
//                    ['icon' => 'glyphicon glyphicon-th', 'href' => Url::to(['/admin/order/list', 'uid' => $model->id]), 'target' =>'_blank', 'btn_class' => 'btn btn-xs', 'tip' => '订单列表'],
                    //!Yii::$app->manager->can('user/recharge') ?: ['icon' => 'fa fa-cny', 'onclick' => 'recharge(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '充值'],
                    ($model->level_id == 1 && $model->status == User::STATUS_WAIT && !empty($model->parent)) ? ['icon' => 'fa fa-cny', 'btn_class'=>'btn btn-xs btn-danger', 'color'=>'red', 'tip'=>'激活', 'onclick'=>'selectActivate(' . $model->id . ')']:'',
//                    ['icon' => 'fa fa-tags', 'onclick' => 'bindCard(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '绑定会员卡'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/user/edit', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '编辑', 'color' => 'green'],
                    ['icon' => 'fa fa-trash', 'btn_class'=>'btn btn-xs btn-danger', 'color'=>'red', 'tip'=>'删除', 'onclick'=>'deleteUser(' . $model->id . ')'],

                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>

    function page_init(){
        $(document).keydown(function(event){
            switch(event.keyCode){
                case 13:return false;
            }
        });
    }

    /**
     * 设置激活
     */
//    function activate(id) {
//        if (!confirm('确定要激活会员状态吗？')) {
//            return false;
//        }
//        $.getJSON('<?php //echo Url::to(['/admin/user/activate'])?>//', {'id':id}, function(json) {
//            if (callback(json)) {
//                //window.location.reload();
//            }
//        });
//    }

    /**
     * 删除用户
     */
    function deleteUser(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/delete']);?>', {'id':id}, function(json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }


    function selectActivate(id) {
        layer.open({
            btn: ['确定', '取消'],
            title: '激活用户',
            content: '<form class="form"><div class="form-group field-money">\
            <div><div><select id="level_id"><option value="1">会员</option><option value="2">店主</option><option value="3">服务商</option></select></div>\
            </div></form>',
            yes: function (index) {
                var level_id = $('#level_id').val();
                if (level_id === '' || isNaN(level_id) || level_id === undefined) {
                    layer.msg('激活类型选择错误。', function () {});
                    return false;
                }
                $.getJSON('<?php echo Url::to(['/admin/user/activate'])?>', {'id':id, 'level_id':level_id}, function(json) {
                    if (callback(json)) {
                        layer.msg('激活成功');
                        window.location.reload();
                    }
                });
                layer.close(index);
            }
        });
    }

    /**
     * 用户充值
     */
    function recharge(id) {
        layer.open({
            title: '用户充值',
            content: '\
<form class="form">\
    <div class="form-group field-money">\
        <input type="text" id="recharge-money" class="form-control" placeholder="充值金额">\
    </div>\
    <div class="form-group field-remark">\
        <textarea id="recharge-remark" class="form-control" placeholder="备注"></textarea>\
    </div>\
</form>',
            yes: function (index) {
                var money = $('#recharge-money').val();
                var remark = $('#recharge-remark').val();
                if (money === '' || isNaN(money)) {
                    layer.msg('充值金额错误。', function () {});
                    return false;
                }
                $.getJSON('<?php echo Url::to(['/admin/user/recharge'])?>', {'id':id, 'money':money, 'remark':remark}, function(json) {
                    if (callback(json)) {
                        window.location.reload();
                    }
                });
                layer.close(index);
            }
        });
    }

</script>
