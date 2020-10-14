<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\City;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Shop[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '店铺列表';
$this->params['breadcrumbs'][] = '店铺管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_id" class="sr-only">编号</label>
    <?php echo Html::textInput('search_id', Yii::$app->request->get('search_id'), ['id' => 'search_id', 'class' => 'form-control', 'placeholder' => '编号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_username" class="sr-only">登录账号</label>
    <?php echo Html::textInput('search_username', Yii::$app->request->get('search_username'), ['id' => 'search_username', 'class' => 'form-control', 'placeholder' => '登录账号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_name" class="sr-only">店铺名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '店铺名字', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">手机号码</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号码', 'style' => 'max-width:100px;']);?>
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
        <th>店铺名称</th>
        <th>联系电话</th>
        <th>店铺类型</th>
        <th>店铺管理员状态</th>
        <th>店铺状态</th>
        <th>区域</th>
        <th>操作</th>
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
            <td><?php echo Html::encode($model->name);?></td>
            <td><?php echo $model->merchant->mobile;?></td>
            <td><?php echo KeyMap::getValue('merchant_type', empty($model->merchant->type) ? 1 : $model->merchant->type);?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('merchant_status', $model->merchant->status);?></span></td>
            <td><?php echo KeyMap::getValue('shop_status', $model->status);?></td>
            <td><?php echo empty($model->area) ? '未设置':  implode(' ', City::findByCode($model->area)->address());?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/merchant/shop-view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
