<?php

use app\assets\ApiAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\UserAccountLog;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $list []
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
TableAsset::register($this);

$this->title = '财务记录';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<!--<div class="form-group">-->
<!--    <label for="search_mobile" class="sr-only">手机号码</label>-->
<!--    --><?php //echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号码', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<!--<div class="form-group">-->
<!--    <label for="search_real_name" class="sr-only">真实姓名</label>-->
<!--    --><?php //echo Html::textInput('search_real_name', Yii::$app->request->get('search_real_name'), ['id' => 'search_real_name', 'class' => 'form-control', 'placeholder' => '真实姓名', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<!--<div class="form-group">-->
<!--    <label for="search_nickname" class="sr-only">昵称</label>-->
<!--    --><?php //echo Html::textInput('search_nickname', Yii::$app->request->get('search_nickname'), ['id' => 'search_nickname', 'class' => 'form-control', 'placeholder' => '昵称', 'style' => 'max-width:100px;']);?>
<!--</div>-->

<div class="form-group">
    <label for="search_mobile" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('account_log_status'), ['prompt' => '激活状态', 'class' => 'form-control']);?>
</div>
<!--<div class="form-group">-->
<!--    <label for="search_start_date" class="sr-only">激活时间</label>-->
<!--    --><?php //echo Html::textInput('search_handle_start_date', Yii::$app->request->get('search_handle_start_date'), ['id' => 'search_handle_start_date', 'placeholder' => '激活开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<!--    --->
<!--    --><?php //echo Html::textInput('search_handle_end_date', Yii::$app->request->get('search_handle_end_date'), ['id' => 'search_handle_end_date', 'placeholder' => '激活结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
<!--</div>-->
<div class="form-group">
    <label for="search_start_date" class="sr-only">结算时间</label>
    <?php echo Html::textInput('search_create_start_date', Yii::$app->request->get('search_create_start_date'), ['id' => 'search_create_start_date', 'placeholder' => '结算开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_create_end_date', Yii::$app->request->get('search_create_end_date'), ['id' => 'search_create_end_date', 'placeholder' => '结算结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
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
<!--        <th>客户端</th>-->
        <th>销售员</th>
        <th>结算时间</th>
<!--        <th>金额</th>-->
        <th>总销售人数</th>
        <th>总销量</th>
        <th>个人销量</th>
        <th>销售销量</th>
        <th>总销售额</th>
        <th>直接管理业绩【学豆左下角】</th>
        <th>无限代销售管理业绩【学豆右下角】</th>
        <th>直接管理业绩【学豆收入详情】</th>
        <th>2代销售管理业绩【学豆收入详情】</th>
        <th>学豆本月收入【学豆收入详情】</th>
        <th>2代以上销售管理业绩【团队间邀2代以上销售业绩】</th>
        <th>学豆发放情况【学豆收入详情】</th>
        <th>团队直接管理业绩提成【团队直邀1代销售业绩提成】</th>
        <th>团队2代销售管理业绩提成【团队间邀2代销售业绩提成】</th>
        <th>团队2代以上销售管理业绩提成【团队间邀2代以上销售业绩提成】</th>
        <th>团队销售业绩提成总额</th>
        <th>团队私发销售业绩提成金额</th>
        <th>团队私发销售业绩提成结算状态</th>
<!--        <th>佣金</th>-->
<!--        <th>交易号</th>-->
        <th>备注</th>
<!--        <th>状态</th>-->
<!--        <th>创建时间</th>-->
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($list as $item) {?>
        <tr id="data_<?php echo $item['id'];?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $item['id'];?>"/>
                    <span class="lbl"><?php echo $item['id'];?></span>
                </label>
            </td>
<!--            <td>--><?php //echo $item['app_id'];?><!--</td>-->
            <td><?php echo $item->user->real_name;?></td>
            <td><?php echo date('Y-m-d', $item->time);//Yii::$app->formatter->asDatetime($item['time']);?></td>
<!--            <td>--><?php //echo $item['money'];?><!--</td>-->
            <td><?php echo $item['total_sale_people_count'];?></td>
            <td><?php echo $item['total_sale_count'];?></td>
            <td><?php echo $item['person_sale_count'];?></td>
            <td><?php echo $item['sale_sale_count'];?></td>
            <td><?php echo $item['total_sale_money'];?></td>
            <td><?php echo $item['direct_manager_money'];?></td>
            <td><?php echo $item['infinite_sale_manager_money'];?></td>
            <td><?php echo $item['direct_manager_detail_money'];?></td>
            <td><?php echo $item['two_sale_manager_money'];?></td>
            <td><?php echo $item['sale_bean_detail_money'];?></td>
            <td><?php echo $item['two_infinite_sale_money'];?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('bean_status', $item->bean_status);?></span></td>
            <td><?php echo $item['team_direct_manager_one_money'];?></td>
            <td><?php echo $item['team_direct_manager_two_money'];?></td>
            <td><?php echo $item['team_direct_manager_three_money'];?></td>
            <td><?php echo $item['team_direct_manager_all_money'];?></td>
            <td><?php echo $item['team_direct_manager_pay_all_money'];?></td>
<!--            <td>--><?php //echo $item['commission'];?><!--</td>-->
<!--            <td>--><?php //echo $item['trade_no'];?><!--</td>-->
            <td><span class="label label-default"><?php echo KeyMap::getValue('team_sale_status', $item->team_sale_status);?></span></td>
            <td><?php echo Html::encode($item['remark']);?></td>
<!--            <td><span class="label label-default">--><?php //echo KeyMap::getValue('account_log_status', $item->status);?><!--</span></td>-->
<!--            <td>--><?php //echo Yii::$app->formatter->asDatetime($item['create_time']);?><!--</td>-->
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-pencil', 'btn_class'=>'btn btn-xs btn-success', 'color'=>'green', 'tip'=>'修改', 'href'=>Url::to(['/admin/user/account-log-edit', 'id'=>$item->id])],
                    $item['team_sale_status'] != UserAccountLog::TEAM_STATUS_ON ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $item->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '已发', 'color' => 'yellow'],
                    $item['team_sale_status'] != UserAccountLog::TEAM_STATUS_WAIT ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $item->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '已对账', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'btn_class'=>'btn btn-xs btn-danger', 'color'=>'red', 'tip'=>'删除', 'onclick'=>'deleteData(' . $item->id . ')'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     * 设置状态
     */
    function toggleStatus(id) {
        $.getJSON('<?php echo Url::to(['/admin/user/account-log-team-status']);?>', {'id':id}, function(json) {
            if (callback(json)) {
                alert('发放成功。');
                window.location.reload();
            }
        });
    }
</script>
<script>
    /**
     * 删除结算记录
     * @param id 编号
     */
    function deleteData(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/user/delete-account-list']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>