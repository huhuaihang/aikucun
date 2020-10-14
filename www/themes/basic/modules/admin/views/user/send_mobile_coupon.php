<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\System;
use app\models\UserSubsidy;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $list []
 * @var $pagination \yii\data\Pagination
 */

TableAsset::register($this);

$this->title = '手动发放优惠券记录';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm();?>
<div class="form-group field-mobile_list required">
    <label class="control-label" for="from_uid">接收优惠券手机号码</label>
    <textarea id="from_uid" class="form-control" name="from_uid" aria-required="true" style="min-height:300px;"><?php echo Html::encode(Yii::$app->request->post('mobile_list'));?></textarea>
    <div class="help-block">多个手机号码用逗号或换行隔开</div>
<!--    <div class="help-block">多个号码用逗号或换行隔开</div>-->
</div>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>发送</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php echo Html::endForm();?>
