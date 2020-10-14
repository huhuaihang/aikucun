<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\City;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\models\Merchant;
use app\models\MerchantConfig;
use app\models\ShopConfig;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $model_list \app\models\Merchant[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '商户列表';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_id" class="sr-only">编号</label>
    <?php echo Html::textInput('search_id', Yii::$app->request->get('search_id'), ['id' => 'search_id', 'class' => 'form-control', 'placeholder' => '编号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_username" class="sr-only">登录邮箱</label>
    <?php echo Html::textInput('search_username', Yii::$app->request->get('search_username'), ['id' => 'search_username', 'class' => 'form-control', 'placeholder' => '登录邮箱', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_name" class="sr-only">店铺名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '店铺名字', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_agent" class="sr-only">代理商登录邮箱</label>
    <?php echo Html::textInput('search_agent', Yii::$app->request->get('search_agent'), ['id' => 'search_agent', 'class' => 'form-control', 'placeholder' => '代理商登录邮箱', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">Status</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('merchant_status'), ['prompt' => '商户状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <label for="search_mobile" class="sr-only">手机号码</label>
    <?php echo Html::textInput('search_mobile', Yii::$app->request->get('search_mobile'), ['id' => 'search_mobile', 'class' => 'form-control', 'placeholder' => '手机号码', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_area" class="sr-only">代理区域</label>
    <?php echo Html::hiddenInput('search_area', Yii::$app->request->get('search_area'), ['id' => 'search_area']);?>
</div>

<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br/>
<?php if (Yii::$app->manager->can('merchant/edit')) {?>
    <div class="form-group">
        <a class="btn btn-success btn-sm" href="<?php echo Url::to(['/admin/merchant/add']);?>">添加</a>
    </div>
<?php }?>
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
        <th>登录邮箱</th>
        <th>手机号</th>
        <th>店铺类型</th>
        <th>商户类型</th>
        <th>店铺名称</th>
        <th>店铺管理员状态</th>
        <th>店铺状态</th>
        <th>代理商</th>
        <th>区域</th>
        <th>经营类目</th>
        <th>结算比率</th>
        <th>创建时间</th>
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
            <td><?php echo Html::a(Html::encode($model->username), '');?></td>
            <td><?php echo $model->mobile;?></td>
            <td><?php echo KeyMap::getValue('merchant_type', empty($model->type) ? 1 : $model->type);?></td>
            <td><span class="label labe-default"><?php echo empty($model->is_person) ?  '企业' :  '个人';?></span></td>
            <td><?php echo !empty($model->shop) ? $model->shop->name: '未设置';?></td>
            <td><span class="label label-default"><?php echo KeyMap::getValue('merchant_status', $model->status);?></span></td>
            <td><?php echo KeyMap::getValue('shop_status', empty($model->shop->status) ? 1 : $model->shop->status);?></td>
            <td><?php echo Html::encode(empty($model->agent->username) ? '未设置代理商' : $model->agent->username);?></td>
            <td><?php echo empty($model->shop->area) ? '未设置':  implode(' ', City::findByCode($model->shop->area)->address());?></td>
            <td><?php $cid_list = json_decode(ShopConfig::getConfig($model->shop->id, 'cid_list'), true);
                if (is_array($cid_list)) {
                    foreach ($cid_list as $cid) {
                        if (!empty(GoodsCategory::findOne($cid))){
                            echo GoodsCategory::findOne($cid)->name . chr(10);
                        }
                    }
                }?>
            </td>
            <td><?php echo MerchantConfig::getConfig($model->id, 'merchant_charge_ratio');?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/merchant/view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/merchant/edit', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '修改', 'color' => 'green'],
                    ['icon' => 'fa fa-rub', 'onclick'=> 'chargeRatio('.$model->id.', \'' . MerchantConfig::getConfig($model->id, 'merchant_charge_ratio') . '\')', 'btn_class' => 'btn btn-xs btn-success', 'tip' => '设置结算比率', 'color' => 'green'],
                    ($model->status != Merchant::STATUS_COMPLETE) ? false : ['icon' => 'fa fa-times', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '停用', 'color' => 'yellow'],
                    ($model->status != Merchant::STATUS_STOPED) ? false : ['icon' => 'fa fa-check', 'onclick' => 'toggleStatus(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启用', 'color' => 'yellow'],
                    ['icon' => 'fa fa-trash', 'onclick' => 'deleteShop(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],

                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<script>
    /**
     *初始化搜索区域
     */
    function page_init() {
        $('#search_area').after(
            '<div id="citys">\n' +
            '    <select name="province" class="form-control"></select>\n' +
            '    <select name="city" class="form-control"></select>\n' +
            '    <select name="area" class="form-control"></select>\n' +
            '</div>');
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('#search_area').val(),
            required: false,
            placeholder: ' - 搜索区域 - ',
            onChange: function(city) {
                $('#search_area').val(city['code']);
            }
        });
    }

    /**
     * 设置商户状态
     */
    function toggleStatus(id) {
        if (!confirm('确定要切换状态吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/status'])?>', {'id':id}, function(json) {
            if (callback(json)) {
                window.location.reload();
            }
        });
    }

    /**
     * 删除商户
     * @param id 商户编号
     */
    function deleteShop(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/delete']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    function chargeRatio(id, old) {
        layer.prompt({title: '输入结算比率', formType: 3, value:old}, function(text, index){
            if(!/^-?\d+\.?\d{0,4}$/.test(text)){
                layer.msg('只能输入数字，小数点后只能保留四位');
                return false;
            }

            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/merchant/merchant-charge-ratio'])?>', {'id':id, 'merchant_charge_ratio':text}, function(json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }
</script>
