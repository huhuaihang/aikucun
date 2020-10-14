<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Agent;
use app\models\City;
use app\models\KeyMap;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $agent_list \app\models\Agent[]
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '代理商入驻申请';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
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
        <th>区域</th>
        <th>状态</th>
        <th>申请时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($agent_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::a(Html::encode($model->username), '');?></td>
            <td><?php echo implode(' ', City::findByCode($model->area)->address());?></td>
            <td><span class="label labe-default"><?php echo KeyMap::getValue('agent_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    $model->status != Agent::STATUS_WAIT_CONTACT ? false : ['icon' => 'fa fa-check', 'onclick' => 'contactFinish(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '客服沟通完成', 'color' => 'yellow'],
                    $model->status != Agent::STATUS_WAIT_INITIAL_FEE ? false : ['icon' => 'fa fa-check', 'onclick' => 'checkInitialFee(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '设置加盟费已收到', 'color' => 'yellow'],
                    $model->status != Agent::STATUS_WAIT_FINANCE ? false : ['icon' => 'fa fa-check', 'onclick' => 'acceptAgentFinance(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '财务通过', 'color' => 'yellow'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/merchant/edit-agent', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    !Yii::$app->manager->can('merchant/delete-agent') ?: ['icon' => 'fa fa-trash', 'onclick' => 'deleteAgent(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script>
    /**
     * 设置客服沟通完成
     */
    function contactFinish(id) {
        if (!confirm('确定设置客服沟通完成吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/agent-contact-finish']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
    /**
     * 设置加盟费已收到
     */
    function checkInitialFee(id) {
        if (!confirm('确定设置加盟费已收到吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/check-agent-initial-fee']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
    /**
     * 通过财务财务
     */
    function acceptAgentFinance(id) {
        if (!confirm('确定要设置为通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/accept-agent-finance']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
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
</script>
