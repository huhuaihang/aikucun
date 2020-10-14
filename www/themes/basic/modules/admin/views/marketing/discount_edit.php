<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Coupon;
use app\models\DiscountGoods;
use app\models\FullCut;
use app\models\Goods;
use app\models\GoodsType;
use app\models\Discount;
use app\models\Util;
use app\widgets\FileUploadWidget;
use app\widgets\LinkPager;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $discount app\models\Discount
 */

ApiAsset::register($this);
LayerAsset::register($this);
TableAsset::register($this);

$this->title = '添加/修改折扣活动';
$this->params['breadcrumbs'][] = '营销管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $form = ActiveForm::begin(['options' => ['onsubmit' => 'return checkGoods()']]);?>
<?php echo Html::activeHiddenInput($discount, 'id');?>
<?php if (empty($discount->id)) {?>
    <?php echo $form->field($discount, 'name');?>
    <?php echo $form->field($discount, 'start_time')->widget(MaskedInput::class, ['mask' => '9999-99-99 99:99:99', 'options' => ['class' => 'form-control', 'value' => Yii::$app->formatter->asDatetime($discount->start_time)]]);?>
    <?php echo $form->field($discount, 'end_time')->widget(MaskedInput::class, ['mask' => '9999-99-99 99:99:99', 'options' => ['class' => 'form-control', 'value' => Yii::$app->formatter->asDatetime($discount->end_time)]]);?>
<?php } else {?>
    <?php echo $form->field($discount, 'name')->textInput(['disabled' => 'disabled']);?>
<?php }?>
<?php //echo $form->field($discount, 'goods_flag_txt');?>
<?php //echo $form->field($discount, 'goods_flag_img')
//    ->hint(!empty($discount->goods_flag_img) ? Html::img(Util::fileUrl($discount->goods_flag_img, false, '_100x100')) : ' ')
//    ->widget(FileUploadWidget::class, ['url' => Url::to(['/admin/marketing/upload', 'dir' => 'marketing']), 'callback' => 'uploadCallback']);?>
<!--<script>-->
<!--    function uploadCallback(url) {-->
<!--        $('#discount-goods_flag_img').val(url);-->
<!--        $('.field-discount-goods_flag_img .hint-block').html('<img src="/uploads/' + url + '" width="100" />');-->
<!--    }-->
<!--</script>-->
<?php //echo $form->field($discount, 'buy_limit')->hint('0表示不限购');?>
<?php //echo $form->field($discount, 'amount')->hint('0表示不限购');?>
<?php echo $form->field($discount, 'remark')->textarea();?>
<div class="form-group field-gid_list required">
    <label class="control-label" for="discount_goods_list">商品</label>
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-sm btn-info" onclick="chooseValidGoods()">添加</button>
            <p  style="font-size: 18px;color: #ff2222;" id="ts"></p>
        </div>
        <div class="col-md-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                <tr>
                    <th>商品</th>
                    <th>价格</th>
                    <th>结算价</th>
                    <th>分佣金额</th>
                    <th>打折</th>
                    <th>减价</th>
                    <th>优惠价格</th>
                    <th>限制数量</th>
                    <th>已售数量</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody id="discount_goods_box">
                <?php foreach ($discount->discountGoodsList as $discountGoods) {?>
                    <tr id="data_<?php echo $discountGoods->gid;?>">
                        <td><?php echo Html::a(Html::img(Util::fileUrl($discountGoods->goods->main_pic, false, '_32x32')) . Html::encode($discountGoods->goods->title), Yii::$app->params['site_host'] . '/h5/goods/view?id=' . $discountGoods->gid);?></td>
                        <td><?php echo $discountGoods->goods->price;?></td>
                        <td><?php echo $discountGoods->goods->supplier_price;?></td>
                        <td><?php echo $discountGoods->goods->share_commission_value;?></td>
                        <td><label>
                                <?php echo Html::radio('DiscountGoods[' . $discountGoods->gid . '][type]', $discountGoods->type == DiscountGoods::TYPE_RATIO, ['class' => 'ace', 'value' => DiscountGoods::TYPE_RATIO]);?>
                                <span class="lbl"> <?php echo Html::textInput('DiscountGoods[' . $discountGoods->gid . '][ratio]', $discountGoods->ratio, ['class' => 'form-control inline', 'style' => 'max-width:60px;']);?></span>
                            </label></td>
                        <td><label>
                                <?php echo Html::radio('DiscountGoods[' . $discountGoods->gid . '][type]', $discountGoods->type == DiscountGoods::TYPE_PRICE, ['class' => 'ace', 'value' => DiscountGoods::TYPE_PRICE]);?>
                                <span class="lbl"> <?php echo Html::textInput('DiscountGoods[' . $discountGoods->gid . '][price]', $discountGoods->price, ['class' => 'form-control inline', 'style' => 'max-width:60px;']);?></span>
                            </label></td>
                        <td><?php if ($discountGoods->type == DiscountGoods::TYPE_PRICE) {
                                echo Util::money($discountGoods->goods->price - $discountGoods->price);
                            } else {
                                echo Util::money($discountGoods->goods->price * $discountGoods->ratio / 10);
                            }?></td>
                        <td><label>

                                <span class="lbl"> <?php echo Html::textInput('DiscountGoods[' . $discountGoods->gid . '][amount]', $discountGoods->amount, ['class' => 'form-control inline', 'style' => 'max-width:60px;']);?></span>
                            </label></td>
                        <td><label>
                                <?php echo Html::textInput('DiscountGoods[' . $discountGoods->gid . '][sale_amount]', $discountGoods->getSaleAmount(), ['class' => 'form-control inline', 'style' => 'max-width:60px;' , 'readonly' =>'readonly']);?>

                            </label></td>
                        <td><button type="button" class="btn btn-xs btn-default" onclick="delete_goods(this,<?php echo $discountGoods->gid;?>)">删除</button></td>
                    </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="help-block"></div>
</div>
<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <button type="button" class="btn btn-default" onclick="window.history.go(-1);"><i class="ace-icon fa fa-arrow-left bigger-110"></i>返回</button>
        <button class="btn btn-primary" ><i class="ace-icon fa fa-check bigger-110"></i>保存</button>
        <button type="reset" class="btn btn-warning"><i class="ace-icon fa fa-undo bigger-110"></i>重置</button>
    </div>
</div>
<?php $form->end();?>
<div id="choose_goods_box" style="display:none; max-width:1000px;" class="container">
    <?php Pjax::begin(['enablePushState' => false]);?>
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline" data-pjax="true">
                <div class="form-group">
                    <label for="search_goods_id" class="sr-only">商品编号</label>
                    <?php echo Html::textInput('search_goods_id', Yii::$app->request->get('search_goods_id'), ['id' => 'search_goods_id', 'class' => 'form-control', 'placeholder' => '商品编号']);?>
                </div>
<!--                <div class="form-group">-->
<!--                    <label for="search_goods_id" class="sr-only">商品思迅编号</label>-->
<!--                    --><?php //echo Html::textInput('search_goods_siss_code', Yii::$app->request->get('search_goods_siss_code'), ['id' => 'search_goods_siss_code', 'class' => 'form-control', 'placeholder' => '商品思迅编号']);?>
<!--                </div>-->
                <div class="form-group">
                    <label for="search_goods_name" class="sr-only">商品名</label>
                    <?php echo Html::textInput('search_goods_name', Yii::$app->request->get('search_goods_name'), ['id' => 'search_goods_name', 'class' => 'form-control', 'placeholder' => '商品名']);?>
                </div>
<!--                <div class="form-group">-->
<!--                    <label for="search_shop_name" class="sr-only">商铺名</label>-->
<!--                    --><?php //echo Html::textInput('search_shop_name', Yii::$app->request->get('search_shop_name'), ['id' => 'search_shop_name', 'class' => 'form-control', 'placeholder' => '店铺名']);?>
<!--                </div>-->
                <div class="form-group">
                    <label for="search_goods_type" class="sr-only">商品类别</label>
                    <?php echo Html::dropDownList('search_goods_type', Yii::$app->request->get('search_goods_type'), ArrayHelper::map(GoodsType::find()->all(), 'id', 'name'), ['prompt' => '搜索商品类型', 'id' => 'search_goods_type', 'class' => 'form-control']);?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-sm btn-primary">搜索</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    $ids=[];
    $discount_goods_list_ids=[];
     if(!empty($discount))
     {
         $discount_goods_list=$discount->goodsList;
         foreach ($discount_goods_list as $goods)
         {
        $discount_goods_list_ids[]= $goods->id;//记录已添加的商品id 防止重复出现
         }
     $ids=$discount_goods_list_ids;
     }
     $discount_r=Discount::find()
         ->andWhere(['status' => Discount::STATUS_RUNNING])
         ->andWhere(['<=', 'start_time', time()])
         ->andWhere(['>=', 'end_time', time()])
         ->one();
     /** @var Discount $discount_r*/
     if(!empty($discount_r))
     {
     $did=$discount_r->id;
     //正在进行的抢购活动商品id数组
     $discountGoods_ids = DiscountGoods::find()
        ->select('gid')
        ->andWhere(['did' => $did])
        ->asArray()
        ->all();
     $ids=array_column($discountGoods_ids,'gid');
         if(!empty($discount_goods_list_ids))
         {
             $ids=array_unique(array_merge($discount_goods_list_ids,array_column($discountGoods_ids,'gid')));
         }
     }


    $query = Goods::find()->joinWith('shop');
     if(!empty($ids))
     {
    $query->andWhere(['not in','{{%goods}}.id',$ids]);
     }
    $query->andWhere(['{{%goods}}.status' => Goods::STATUS_ON]);
    $query->andWhere(['{{%goods}}.is_pack' => Goods::NO]);
    $query->andWhere(['{{%goods}}.is_score' => Goods::NO]);
    $query->andWhere(['{{%goods}}.is_today' => Goods::NO]);
    $query->andWhere(['{{%goods}}.is_index_best' => Goods::NO]);
    $query->andWhere(['{{%goods}}.is_coupon' => Goods::NO]);
    $query->andFilterWhere(['{{%goods}}.id' => Yii::$app->request->get('search_goods_id')]);
    $query->andFilterWhere(['like', '{{%goods}}.siss_code', Yii::$app->request->get('search_goods_siss_code')]);
    $query->andFilterWhere(['like', '{{%goods}}.title', Yii::$app->request->get('search_goods_name')]);
    $query->andFilterWhere(['like', '{{%shop}}.name', Yii::$app->request->get('search_shop_name')]);
    $query->andFilterWhere(['{{%goods}}.tid' => Yii::$app->request->get('search_goods_type')]);
    $pagination = new Pagination(['totalCount' => $query->count(), 'defaultPageSize' => 10]);
    $query->orderBy('id DESC')->offset($pagination->offset)->limit($pagination->limit);?>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped table-bordered" id="goods_list">
                <thead>
                <tr>
                    <th class="center">
                        <label class="pos-rel">
                            <input type="checkbox" class="ace" />
                            <span class="lbl"></span>
                        </label>
                    </th>
                    <th>商品名称</th>
                    <th>价格</th>
                    <th>结算价格</th>
                    <th>佣金设置</th>
                    <th>库存</th>
<!--                    <th>销量</th>-->
<!--                    <th>营销中</th>-->
<!--                    <th>店铺</th>-->
                </tr>
                </thead>
                <tbody>
                <?php foreach ($query->each() as $goods) {/** @var Goods $goods */?>
                <tr>
                    <td class="center">
                        <label class="pos-rel">
                            <input type="checkbox" class="ace" value="<?php echo $goods->id;?>"/>
                            <span class="lbl"><?php echo $goods->id;?></span>
                        </label>
                    </td>
                    <td><?php echo Html::a(Html::img(Util::fileUrl($goods->main_pic, false, '_32x32')) . Html::encode($goods->title), Yii::$app->params['site_host'] . '/h5/goods/view?id=' . $goods->id, ['target' => '_blank', 'data-pjax' => '0']);?></td>
                    <td><?php echo $goods->price;?></td>
                    <td><?php echo $goods->supplier_price;?></td>
                    <td><?php echo $goods->share_commission_value;?></td>
                    <td><?php echo $goods->stock;?></td>
<!--                    <td>--><?php //echo $goods->getAllStock();?><!--</td>-->
<!--                    <td>--><?php //echo $goods->getGoodsStock();?><!--</td>-->
<!--                    <td>--><?php //foreach (Coupon::find()->andWhere(['status' => Coupon::STATUS_RUNNING])->each() as $_coupon) {/** @var Coupon $_coupon */
//                            $gidList = $_coupon->getValidGidList();
//                            if (empty($gidList) || in_array($goods->id, $gidList)) {
//                                echo '优惠券[', $_coupon->id, ']：', Html::encode($_coupon->name), '<br />';
//                            }
//                        }
//                        foreach (FullCut::find()->andWhere(['status' => FullCut::STATUS_RUNNING])->each() as $_fullCut) {/** @var FullCut $_fullCut */
//                            $gidList = ArrayHelper::getColumn($_fullCut->fullCutGoodsList, 'gid');
//                            if (empty($gidList) || in_array($goods->id, $gidList)) {
//                                echo '满减[', $_fullCut->id, ']：', Html::encode($_fullCut->name), '<br />';
//                            }
//                        }?><!--</td>-->
<!--                    <td>--><?php //if (!empty($goods->sid)) {
//                            echo Html::a(Html::encode($goods->shop->name), ['/admin/merchant/shop-view', 'id' => $goods->sid], ['target' => '_blank', 'data-pjax' => '0']);
//                        } elseif (!empty($goods->hid)) {
//                            echo Html::encode($goods->house->name);
//                        }?><!--</td>-->
                </tr>
                <?php }?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 center">
            <?php echo LinkPager::widget(['pagination' => $pagination]);?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 center">
            <button type="button" class="btn btn-primary btn-sm" onclick="saveChoseGoods()">确定</button>
        </div>
    </div>
    <div class="space-10"></div>
    <?php $this->registerJs('table_check_all();');?>
    <?php Pjax::end();?>
</div>
<script>
    function page_init() {
        console.log('初始清空缓存');
        localStorage.clear();
    }
    /**
     * 选择适用商品
     */
    function chooseValidGoods() {
        layer.open({
            type: 1,
            title: '减折价商品选择',
            content: $('#choose_goods_box'),
            area: '1000px'
        });
    }
    /**
     * 选择适用商品
     */
    function checkGoods() {

        var gid_array=JSON.parse(localStorage.getItem('gid_arr'));
        if(gid_array == null)
        {
            gid_array=[];
        }
        console.log(gid_array)
        if(localStorage.getItem('is_delete') !=1)
        {
        <?php if(!empty($discount->discountGoodsList)){?>
        <?php foreach ($discount->discountGoodsList as $discountGoods) {?>
        var gid =<?php echo $discountGoods->gid?>;
        if (gid_array.indexOf(gid) === -1) {
            gid_array.push(gid);
        }
        <?php }
        }?>
        }
        console.log(gid_array)
        $("#w0").data('yiiActiveForm').validated = true;
        var i=0;

        var msg='数据填写有误，请检查';
        const  start_time=$("input[name='Discount[start_time]']").val();
        const  end_time=$("input[name='Discount[end_time]']").val();
       //判断时间是否填写正确
       if(start_time!==undefined && start_time !=='')
       {
        if(isDatetime(start_time) === false){
            msg='活动开始时间填写有误，请检查';
            $('#ts').html(msg);
            return false;
        }
       }
        if(end_time!==undefined && end_time !=='') {
            if (isDatetime(end_time) === false) {
                msg = '活动结束时间填写有误，请检查';
                $('#ts').html(msg);
                return false;
            }
        }
        gid_array.forEach((gid) => {
           // var val=$('input:radio[name="sex"]:checked').val();
            var  type_radio_val=parseInt($("input:radio[name='DiscountGoods[" + gid + "][type]']:checked").val());
            var  price_val=parseFloat($("input[name='DiscountGoods[" + gid + "][price]']").val());
            var  goods_price= parseFloat($('#data_' + gid).find('td').get(1).innerHTML);
            var  ratio_val=parseFloat($("input[name='DiscountGoods[" + gid + "][ratio]']").val());
            var  amount_val=$("input[name='DiscountGoods[" + gid + "][amount]']").val();
            var  sale_amount_val=parseInt($("input[name='DiscountGoods[" + gid + "][sale_amount]']").val());

            if (type_radio_val === undefined) {
                i++;
                msg='请选择优惠减价类型';

            }
            if (type_radio_val === 1) {

                if (price_val==undefined || price_val <=0 || isNaN(price_val) || goods_price<=price_val ) {
                    console.log(price_val);
                    i++;
                    msg='请检查已选择的减价设置值';
                }
                $("input[name='DiscountGoods[" + gid + "][ratio]']").val('');
            }

            if (type_radio_val === 2) {

                if (ratio_val === null || ratio_val === undefined ) {
                    console.log(ratio_val);
                    i++;
                    msg='请检查已选择的折扣设置值';
                }
                if (isNaN(ratio_val) || ratio_val<=0 || ratio_val>=10 ) {
                    console.log(ratio_val);
                    i++;
                    msg='请检查已选择的折扣设置值';
                }
                $("input[name='DiscountGoods[" + gid + "][price]']").val('');
            }

            if (amount_val === undefined || amount_val <=0 || (/(^[1-9]\d*$)/.test(amount_val)) === false) {
                console.log((/(^[1-9]\d*$)/.test(amount_val)))
                i++;
                msg='商品限购数量不能为空且只能是正整数';
            }

            if ( amount_val < sale_amount_val ) {
                console.log(amount_val);
                console.log(sale_amount_val);
                i++;
                msg='商品限购数量不能小于已售数量';
            }
            //执行代码
        });

        if(i>0)
        {
            $('#ts').html(msg);
            //alert('数据填写有误，请检查');
            return false;
        }else{
            $("#w0").data('yiiActiveForm').validated = true;
            localStorage.clear();
            return true;

        }

    }
    /**
     * 保存选中的商品
     */
    function saveChoseGoods() {
      //localStorage.clear();
        var gid_array=JSON.parse(localStorage.getItem('gid_arr'));

        if(gid_array == null)
        {
            gid_array=[];
        }
        layer.closeAll();
        $('#goods_list tbody').find('input[type=checkbox]:checked').each(function () {
            var gid = parseInt($(this).val());
            if(gid_array.indexOf(gid) === -1)
            {
             gid_array.push(gid);
            }
            localStorage.setItem('gid_arr',JSON.stringify(gid_array));
            console.log(gid_array);
            if ($('#data_' + gid).length > 0) {
                return true;
            }
            var goods = $(this).parents('tr').find('td').get(1).innerHTML;
            var price = $(this).parents('tr').find('td').get(2).innerHTML;
            var supplier_price = $(this).parents('tr').find('td').get(3).innerHTML;
            var commission = $(this).parents('tr').find('td').get(4).innerHTML;
            var tr = '<tr id="data_' + gid + '">';
            tr += '<td>' + goods + '</td>';
            tr += '<td>' + price + '</td>';
            tr += '<td>' + supplier_price + '</td>';
            tr += '<td>' + commission + '</td>';
            tr += '<td><label>';
            tr += '<input name="DiscountGoods[' + gid + '][type]" type="radio" class="ace" value="<?php echo DiscountGoods::TYPE_RATIO;?>" />';
            tr += '<span class="lbl"> <input type="text" name="DiscountGoods[' + gid + '][ratio]" value="" class="form-control inline" style="max-width:60px;" /></span>';
            tr += '</label></td>';
            tr += '<td><label>';
            tr += '<input name="DiscountGoods[' + gid + '][type]" type="radio" class="ace" value="<?php echo DiscountGoods::TYPE_PRICE;?>" checked />';
            tr += '<span class="lbl"> <input type="text" name="DiscountGoods[' + gid + '][price]" value="" class="form-control inline" style="max-width:60px;" /></span>';
            tr += '</label></td>';
            tr += '<td>' + price + '</td>';
            tr += '<td><span class="lbl"> <input type="text" name="DiscountGoods[' + gid + '][amount]" value="" class="form-control inline" style="max-width:60px;" /></span></td>';
            tr += '<td><span class="lbl"> <input type="text" name="DiscountGoods[' + gid + '][sale_amount]" value="0" class="form-control inline" style="max-width:60px;" readonly="readonly" /></span></td>';
            tr += '<td><button type="button" class="btn btn-xs btn-default" onclick="delete_goods(this,'+gid+')">删除</button></td>';
            tr += '</tr>';
            $('#discount_goods_box').append(tr);
        });
    }
    //验证时间是否输入正确
    function isDatetime(date){
        var regex=/^(?:19|20)[0-9][0-9]-(?:(?:0[1-9])|(?:1[0-2]))-(?:(?:[0-2][1-9])|(?:[1-3][0-1])) (?:(?:[0-2][0-3])|(?:[0-1][0-9])):[0-5][0-9]:[0-5][0-9]$/;
        if(!regex.test(date)){
           // alert("格式不正确！请输入正确的时间格式，如：2010-07-07 09:12:00");
            return false;
        }
       // alert("格式正确！");
        return true;
    }

    function  delete_goods(that,val) {
        var gid_array=JSON.parse(localStorage.getItem('gid_arr'));
        if(gid_array == null)
        {
            gid_array=[];

        <?php if(!empty($discount->discountGoodsList)){?>
        <?php foreach ($discount->discountGoodsList as $discountGoods) {?>
        var gid =<?php echo $discountGoods->gid?>;
        if (gid_array.indexOf(gid) === -1) {
            gid_array.push(gid);
        }
        <?php }
        }?>
        }
        var index = gid_array.indexOf(val);
        if (index > -1) {
            gid_array.splice(index, 1);
        }
        console.log(gid_array)
        localStorage.setItem('gid_arr',JSON.stringify(gid_array));
        localStorage.setItem('is_delete','1');
      //  console.log(gid_array)
        $(that).parents('tr').remove();
    }


</script>
