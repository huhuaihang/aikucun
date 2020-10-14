<?php

use app\models\KeyMap;
use app\models\Manager;
use app\models\ManagerRole;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model app\models\Manager
 */

$this->title = '添加/修改管理员';
$this->params['breadcrumbs'][] = '权限管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin();?>
    <?php echo Html::activeHiddenInput($model, 'id');?>
    <?php echo $form->field($model, 'nickname');?>
    <?php echo $form->field($model, 'username');?>
    <?php echo $form->field($model, 'password')->passwordInput(['value'=>'']);?>
    <?php echo $form->field($model, 'mobile');?>
    <?php echo $form->field($model, 'email');?>
    <?php echo $form->field($model, 'rid')->dropDownList(ArrayHelper::map(ManagerRole::find()->andWhere(['>', 'id', 1])->andWhere(['status'=>ManagerRole::STATUS_OK])->asArray()->all(), 'id', 'name'));?>
    <?php echo $form->field($model, 'status')->radioList([Manager::STATUS_ACTIVE=>KeyMap::getValue('manager_status', Manager::STATUS_ACTIVE), Manager::STATUS_STOPED=>KeyMap::getValue('manager_status', Manager::STATUS_STOPED)]);?>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
            <button class="btn btn-primary"><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
            <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
        </div>
    </div>
<?php $form->end();?>
