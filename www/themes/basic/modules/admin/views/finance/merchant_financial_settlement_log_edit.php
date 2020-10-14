<?php

use app\assets\FileUploadAsset;
use app\assets\MaskedInputAsset;
use app\models\KeyMap;
use app\widgets\FileUploadWidget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\MerchantFinancialSettlementLog
 * @var $model_list app\models\MerchantFinancialSettlement[]
 */

FileUploadAsset::register($this);
MaskedInputAsset::register($this);

$this->title = '修改商户结算记录';
$this->params['breadcrumbs'][] = '财务管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($model, 'id');?>
<?php echo Html::activeTextInput($model, 'money');?>
<?php echo $form->field($model, 'bank_info')->textarea(['rows' => '7']);?>
<?php echo $form->field($model, 'proof_file')
    ->hint(!empty($model->proof_file) ? Html::img(Yii::$app->params['upload_url'] . $model->proof_file, ['width'=>100]) : ' ')
    ->widget(FileUploadWidget::className(), ['url'=>Url::to(['/admin/finance/upload', 'dir'=>'finance']), 'callback'=>'uploadCallback']);?>
<script>
    function uploadCallback(url) {
        $('[name="MerchantFinancialSettlementLog[proof_file]"]').val(url);
        $('.field-merchantfinancialsettlementlog-proof_file .hint-block').html('<img src="<?php echo Yii::$app->params['upload_url'];?>' + url + '" width="100" />');
    }
</script>
<?php echo $form->field($model, 'remark')->textarea();?>
<?php echo $form->field($model, 'status')->radioList(KeyMap::getValues('merchant_financial_settlement_log_status'));?>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button type="submit" class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th class="center">
            <label class="pos-rel">
                <input type="checkbox" class="ace" />
                <span class="lbl"></span>
            </label>
        </th>
        <th>商户</th>
        <th>订单号</th>
        <th>订单金额</th>
        <th>退款金额</th>
        <th>商户实收金额</th>
        <th>服务费</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>结算时间</th>
        <th>备注</th>
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
            <td><?php echo Html::encode($model->merchant->username);?></td>
            <td><?php echo $model->order->no;?></td>
            <td><?php echo $model->order_money;?></td>
            <td><?php echo $model->refund_money;?></td>
            <td><?php echo $model->merchant_receive_money;?></td>
            <td><?php echo $model->charge;?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('merchant_financial_settlement_status', $model->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->settle_time);?></td>
            <td><?php echo Html::encode($model->remark);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
