<?php

use app\assets\MaskedInputAsset;
use app\assets\TableAsset;
use app\models\KeyMap;
use app\models\Order;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\User[]
 * @var $pagination \yii\data\Pagination
 */

MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '团队统计列表';
$this->params['breadcrumbs'][] = '会员管理';
$this->params['breadcrumbs'][] = $this->title;
echo $c;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_level_id" class="sr-only">会员等级</label>
    <?php echo Html::dropDownList('search_level_id', Yii::$app->request->get('search_level_id'), KeyMap::getValues('user_level_id'), ['prompt' => '会员等级', 'class' => 'form-control']);?>
</div>
<!--<div class="form-group">-->
<!--    <label for="search_real_name" class="sr-only">真实姓名</label>-->
<!--    --><?php //echo Html::textInput('search_real_name', Yii::$app->request->get('search_real_name'), ['id' => 'search_real_name', 'class' => 'form-control', 'placeholder' => '真实姓名', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<!--<div class="form-group">-->
<!--    <label for="search_nickname" class="sr-only">昵称</label>-->
<!--    --><?php //echo Html::textInput('search_nickname', Yii::$app->request->get('search_nickname'), ['id' => 'search_nickname', 'class' => 'form-control', 'placeholder' => '昵称', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<!--<div class="form-group">-->
<!--    <label for="search_mobile" class="sr-only">手机号</label>-->
<!--    --><?php //echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号', 'style' => 'max-width:100px;']);?>
<!--</div>-->
<div class="form-group">
    <label for="search_start_date" class="sr-only">下单时间</label>
    <?php echo Html::textInput('search_start_date', Yii::$app->request->get('search_start_date'), ['id' => 'search_start_date', 'placeholder' => '开始日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
    -
    <?php echo Html::textInput('search_end_date', Yii::$app->request->get('search_end_date'), ['id' => 'search_end_date', 'placeholder' => '结束日期', 'style' => 'max-width:90px;', 'class'=>'form-control masked', 'data-mask'=>'9999-99-99']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
<!--    <a href="--><?php //echo Url::current(['export' => 'excel']);?><!--" class="btn btn-info btn-sm">导出</a>-->
</div>
<?php echo Html::endForm();?>
<table class="table table-striped table-bordered table-hover">
    <thead>
    <tr>
        <th>用户编号</th>
        <th>用户编号</th>
        <th>真实姓名</th>
        <th>昵称</th>
        <th>手机号</th>
        <th>数量</th>
        <th>6月之后激活数量</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($model_list as $model) {?>
        <tr class="data_<?php echo $model['id'];?>">
            <td><?php echo $model['c'];?></td>
            <td><?php echo $model['id'];?></td>
            <td><?php echo Html::encode($model['real_name']);?></td>
            <td><?php echo Html::encode($model['nickname']);?></td>

            <td><?php echo $model['mobile'];?></td>
            <td>
                <?php echo $model['count'];?>
            </td>
            <td>
                <?php echo $model['handle_count'];?>
            </td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php //echo LinkPager::widget(['pagination' => $pagination]);?>
