<?php

use app\assets\ApiAsset;
use app\assets\CitySelectAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\City;
use app\models\GoodsCategory;
use app\models\KeyMap;
use app\models\Merchant;
use app\models\ShopConfig;
use app\widgets\ManagerTableOp;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $merchant_list \app\models\Merchant[]
 */

ApiAsset::register($this);
CitySelectAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '商户入驻申请';
$this->params['breadcrumbs'][] = '商户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_username" class="sr-only">登录账号</label>
    <?php echo Html::textInput('search_username', Yii::$app->request->get('search_username'), ['id' => 'search_username', 'class' => 'form-control', 'placeholder' => '登录账号', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_name" class="sr-only">店铺名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '店铺名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <label for="search_area" class="sr-only">区域</label>
    <?php echo Html::hiddenInput('search_area', Yii::$app->request->get('search_area'), ['id' => 'search_area']);?>
    <div id="citys">
        <select name="province" class="form-control"></select>
        <select name="city" class="form-control"></select>
        <select name="area" class="form-control"></select>
    </div>
</div>
<div class="form-group">
    <label for="search_status" class="sr-only">状态</label>
    <?php echo Html::dropDownList('search_status', Yii::$app->request->get('search_status'), KeyMap::getValues('merchant_status'), ['prompt' => '状态', 'class' => 'form-control']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
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
        <th>店铺名称</th>
        <th>联系手机号</th>
        <th>区域</th>
        <th>申请类目</th>
        <th>状态</th>
        <th>类型</th>
        <th>申请时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($merchant_list as $model) {?>
        <tr id="data_<?php echo $model->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $model->id;?>"/>
                    <span class="lbl"><?php echo $model->id;?></span>
                </label>
            </td>
            <td><?php echo Html::a(Html::encode($model->username), '');?></td>
            <td><?php echo !empty($model->shop) ? $model->shop->name : '未设置'?></td>
            <td><?php echo $model->mobile;?></td>
            <td><?php echo empty($model->shop->area) ? "未设置" : implode(' ', City::findByCode($model->shop->area)->address());?></td>
            <td><?php $cid_list = json_decode(ShopConfig::getConfig($model->shop->id, 'cid_list'), true);
                if (is_array($cid_list)) {
                    foreach ($cid_list as $cid) {
                        if(!empty($cid)){
                            echo GoodsCategory::findOne($cid)->name . chr(10);
                        }
                    }
                }?>
            </td>
            <td><span class="label labe-default"><?php echo KeyMap::getValue('merchant_status', $model->status);?></span></td>
            <td><span class="label labe-default"><?php echo empty($model->is_person) ?  '企业' :  '个人';?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($model->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/merchant/view', 'id' => $model->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    $model->status != Merchant::STATUS_WAIT_DATA1 ?: ['icon' => 'fa fa-check', 'onclick' => 'acceptMerchantData1(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '数据1通过', 'color' => 'yellow'],
                    $model->status != Merchant::STATUS_WAIT_DATA1 ?: ['icon' => 'fa fa-times', 'onclick' => 'rejectMerchantData1(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '数据1拒绝', 'color' => 'yellow'],
                    $model->status != Merchant::STATUS_WAIT_DATA2 ?: ['icon' => 'fa fa-check', 'href' => Url::to(['/admin/merchant/get-merchant-fee', 'id' => $model->id]), 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '数据2通过', 'color' => 'yellow'],
                    $model->status != Merchant::STATUS_WAIT_DATA2 ?: ['icon' => 'fa fa-times', 'onclick' => 'rejectMerchantData2(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '数据2拒绝', 'color' => 'yellow'],
                    !Yii::$app->manager->can('merchant/delete') ?: ['icon' => 'fa fa-trash', 'onclick' => 'deleteMerchant(' . $model->id . ')', 'btn_class' => 'btn btn-xs btn-danger', 'tip' => '删除', 'color' => 'red'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<script>
    function page_init() {
        $('#citys').citys({
            dataUrl: makeApiUrl('<?php echo Url::to(['/api/default/city', 'format' => 'flat', 'level' => 3]);?>'),
            code: $('#search_area').val(),
            required: false,
            placeholder: ' - 搜索区域 - ',
            onChange: function(city) {
                if(city['code'] !='0'){
                    $('#search_area').val(city['code']);
                }else{
                    $('#search_area').val('');
                }
            }
        });
    }

    /**
     * 通过商户数据
     */
    function acceptMerchantData1(id) {
        if (!confirm('确定要设置为通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/accept-data1']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                layer.msg('设置成功。');
                window.location.reload();
            }
        });
    }

    /**
     * 拒绝商户数据
     */
    function rejectMerchantData1(id) {
        layer.prompt({
            formType: 2,
            value: '',
            title: '请填写拒绝原因：'
        }, function(info, index){
            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/merchant/reject-data1']);?>', {'id':id, 'info':info}, function(json) {
                if (callback(json)) {
                }
            });
        });
    }

    /**
     * 通过商户数据
     */
    function acceptMerchantData2(id) {
        if (!confirm('确定要设置为通过吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/accept-data2']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }

    /**
     * 拒绝商户数据
     */
    function rejectMerchantData2(id) {
        layer.prompt({
            formType: 2,
            value: '',
            title: '请填写拒绝原因：'
        }, function(info, index){
            layer.close(index);
            $.getJSON('<?php echo Url::to(['/admin/merchant/reject-data2']);?>', {'id':id, 'info':info}, function(json) {
                if (callback(json)) {
                    window.location.reload();
                }
            });
        });
    }

    /**
     * 删除商户
     */
    function deleteMerchant(id) {
        if (!confirm('确定要删除吗？')) {
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/merchant/delete']);?>', {'id':id}, function (json) {
            if (callback(json)) {
                $('#data_' + id).remove();
            }
        });
    }
</script>
