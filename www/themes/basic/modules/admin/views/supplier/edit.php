<?php

use app\models\House;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this \yii\web\View
 * @var $supplier \app\models\Supplier
 * @var $configForm \app\models\SupplierConfigForm
 */

$this->title = '添加/修改供货商';
$this->params['breadcrumbs'][] = '供货商管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
<?php echo Html::activeHiddenInput($supplier, 'id');?>
<div class="tabbable">
    <ul class="nav nav-tabs padding-16">
        <li class="active">
            <a data-toggle="tab" href="#edit-basic">
                <i class="green ace-icon fa fa-id-card-o bigger-125"></i>
                基本信息
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#edit-refund">
                <i class="green ace-icon fa fa-truck bigger-125"></i>
                退货信息
            </a>
        </li>
        <li>
            <a data-toggle="tab" href="#edit-finance">
                <i class="green ace-icon fa fa-dollar bigger-125"></i>
                结算信息
            </a>
        </li>
    </ul>
    <div class="tab-content profile-edit-tab-content">
        <div id="edit-basic" class="tab-pane in active">
            <div class="page-header">
                <h3>基本信息</h3>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($supplier, 'name');?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($supplier, 'mobile')->textInput(['autocomplete' => 'off']);?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($supplier, 'password')->passwordInput(['value' => '', 'autocomplete' => 'off']);?>
                </div>
            </div>
        </div>
        <div id="edit-refund" class="tab-pane in">
            <div class="page-header">
                <h3>退货信息</h3>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'refund_deliver_user'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'refund_deliver_address'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'refund_deliver_mobile'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'refund_deliver_remark')->textarea(); ?>
                </div>
            </div>
        </div>
        <div id="edit-finance" class="tab-pane in">
            <div class="page-header">
                <h3>结算信息</h3>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_bank_name'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_bank_addr'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_bank_account_name'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_bank_account'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_alipay_name'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_alipay_account'); ?>
                </div>
                <div class="col-xs-12 col-sm-8">
                    <?php echo $form->field($configForm, 'finance_weixin_account'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
