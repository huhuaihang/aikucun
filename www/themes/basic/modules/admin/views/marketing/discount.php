<?php

use app\assets\ApiAsset;
use app\assets\LayerAsset;
use app\assets\TableAsset;
use app\models\Discount;
use app\models\DiscountGoods;
use app\models\KeyMap;
use app\widgets\LinkPager;
use app\widgets\ManagerTableOp;
use app\assets\MaskedInputAsset;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var $this \yii\web\View
 * @var $discountList \app\models\Discount[]
 * @var $pagination \yii\data\Pagination
 */

ApiAsset::register($this);
LayerAsset::register($this);
MaskedInputAsset::register($this);
TableAsset::register($this);

$this->title = '限时折扣活动列表';
$this->params['breadcrumbs'][] = '营销管理';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php echo Html::beginForm('?', 'get', ['class' => 'form-inline']);?>
<div class="form-group">
    <label for="search_name" class="sr-only">名称</label>
    <?php echo Html::textInput('search_name', Yii::$app->request->get('search_name'), ['id' => 'search_name', 'class' => 'form-control', 'placeholder' => '名称', 'style' => 'max-width:100px;']);?>
</div>
<div class="form-group">
    <button class="btn btn-primary btn-sm">搜索</button>
</div>
<br />
<div class="form-group">
    <a href="<?php echo Url::to(['/admin/marketing/discount-edit']);?>" class="btn btn-success btn-sm">添加限时折扣活动</a>
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
        <th>名称</th>
        <th>期限</th>
        <th>商品</th>
        <th>状态</th>
        <th>创建时间</th>
        <th>操作</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($discountList as $discount) {?>
        <tr id="data_<?php echo $discount->id;?>">
            <td class="center">
                <label class="pos-rel">
                    <input type="checkbox" class="ace" value="<?php echo $discount->id;?>"/>
                    <span class="lbl"><?php echo $discount->id;?></span>
                </label>
            </td>
            <td><?php echo Html::encode($discount->name);?></td>
            <td><?php echo Yii::$app->formatter->asDatetime($discount->start_time);
                echo '<br />';
                echo Yii::$app->formatter->asDatetime($discount->end_time);?></td>
            <td><button type="button" class="btn btn-xs btn-default" onclick="showDiscountGoods(<?php echo $discount->id;?>)"><?php echo $discount->getGoodsCount(), '件，点击弹出';?></button></td>
            <td><span class="label label-default arrowed-in-right arrowed"><?php echo KeyMap::getValue('discount_status', $discount->status);?></span></td>
            <td><?php echo Yii::$app->formatter->asDatetime($discount->create_time);?></td>
            <td><?php echo ManagerTableOp::widget(['items' => [
                    ['icon' => 'fa fa-info-circle', 'href' => Url::to(['/admin/marketing/discount-view', 'id' => $discount->id]), 'btn_class' => 'btn btn-xs', 'tip' => '详情'],
                    $discount->status != Discount::STATUS_EDIT ?: ['icon' => 'fa fa-check', 'onclick' => 'startDiscount(' . $discount->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '启动减折价'],
                    $discount->status != Discount::STATUS_RUNNING ?: ['icon' => 'fa fa-times', 'onclick' => 'stopDiscount(' . $discount->id . ')', 'btn_class' => 'btn btn-xs btn-warning', 'tip' => '暂停减折价'],
                    ['icon' => 'fa fa-pencil', 'href' => Url::to(['/admin/marketing/discount-edit', 'id' => $discount->id]), 'btn_class' => 'btn btn-xs btn-success', 'tip' => '编辑', 'color' => 'green'],
                ]]);?></td>
        </tr>
    <?php }?>
    </tbody>
</table>
<?php echo LinkPager::widget(['pagination' => $pagination]);?>
<div id="choose_goods_list_box" style="display:none; ">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped table-bordered" id="goods_list">
                <thead>
                <tr>
                    <th>商品名称</th>
                    <th>展示小时数</th>
                    <th>操作</th>
                    <!--                    <th>价格</th>-->
                    <!--                    <th>库存</th>-->
                    <!--                    <th>销量</th>-->
                    <!--                    <th>减折价</th>-->
                </tr>
                </thead>
                <tbody id="chose_goods_lists">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>

    /**
     * 显示减折价商品
     * @param id
     */
    function showDiscountGoods(id) {

        $.getJSON('<?php echo Url::to(['/admin/marketing/discount-goods-list']);?>', {'did': id}, function (json) {
            var goods_list_html = '';
            var discount_goods_list = json['discount_goods_list'];
            $.each(discount_goods_list, function(i, discount_goods) {
                goods_list_html += '<tr>' +
                    '<td>'+ discount_goods['goods']['title'] +'</td>' +
                    // '<td>'+ discount_goods['goods']['price'] +'</td>' +
                    '<td><input type="text"  class="form-control" value="'+discount_goods['goods']['hour']+'" name="hour'+ discount_goods['goods']['id']+'" ></td>' +
                    '<td><a onclick="setHour('+discount_goods['goods']['id']+','+id+')" class="btn btn-success btn-sm">确定</a></td>' +
                    //'<td>' + ((discount_goods['type'] == <?php //echo DiscountGoods::TYPE_PRICE;?>//) ? ('减价' + discount_goods['price'] + '元') : ('打' + discount_goods['ratio'] + '折')) + '</td>' +
                    '</tr>';
            });
            $('#chose_goods_lists').html(goods_list_html);
        });
        layer.open({
            type: 1,
            title: '选中的商品',
            content: $('#choose_goods_list_box'),
            area: '1000px,100%',
            shadeClose: true,
            scrollbar:true,
            success: function(elem){
                $(".layui-layer-page").css({"overflow":"auto","height":"100%"});
            }
        });

    }

    function setHour(gid,did) {
        var hour = $('input[name="hour' + gid + '"]').val();
        if (/(^[1-9]\d*$)/.test(hour) === false) {
            layer.msg('必须是正整数');
            return false;
        }
        $.getJSON('<?php echo Url::to(['/admin/marketing/set-discount-goods-hour']);?>', {'hour': hour,'did':did,'gid':gid}, function (json) {
            if (json['message']) {
                layer.msg(json['message']);
                return false;
            }else{
                layer.msg('设置成功');
            }
            console.log(json)
        });

    }
    /**
     * 启动减折价
     * @param id
     */
    function startDiscount(id) {
        $.getJSON('<?php echo Url::to(['/admin/marketing/discount-start']);?>', {'id': id}, function (json) {

            if(json['message'])
            {
                alert(json['message']);
            }
            window.location.reload();
        });
    }

    /**
     * 暂停减折价
     * @param id
     */
    function stopDiscount(id) {
        $.getJSON('<?php echo Url::to(['/admin/marketing/discount-stop']);?>', {'id': id}, function (json) {
            window.location.reload();
        });
    }


</script>
