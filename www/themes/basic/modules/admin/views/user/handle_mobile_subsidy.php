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

$this->title = '手动增加补贴记录';
$this->params['breadcrumbs'][] = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm();?>
<div class="form-group field-mobile_list required">
    <label class="control-label" for="from_uid">发送补贴人员手机号码</label>
    <textarea id="from_uid" class="form-control" name="from_uid" aria-required="true" style="min-height:300px;"><?php echo Html::encode(Yii::$app->request->post('mobile_list'));?></textarea>
    <div class="help-block">多个手机号码用逗号或换行隔开</div>
    <label class="control-label" for="to_uid">接收补贴人员手机号码</label>
    <input id="to_uid" class="form-control" name="to_uid" aria-required="true" value="<?php echo $mobile;?>">
    <label class="control-label" for="money">补贴金额</label>
<!--    <input id="money" class="form-control" name="money" aria-required="true">-->
    <select id="money" class="form-control" name="money" aria-required="true">
        <option value="250">250</option>
        <option value="200">200</option>
        <option value="150">150</option>
        <option value="100">100</option>
        <option value="50">50</option>
        <option value="33">33</option>
        <option value="15">15</option>
        <option value="10">10</option>
    </select>
    <label class="control-label" for="type">补贴层级</label>
    <select id="type" class="form-control" name="type" aria-required="true">
        <option value="1">直属邀请</option>
        <option value="2">直属会员新增邀请</option>
        <option value="3">直属会员邀请会员再邀请会员</option>
        <option value="4">直属团队新增会员</option>
    </select>
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
