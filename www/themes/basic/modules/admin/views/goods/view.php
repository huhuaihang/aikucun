<?php

use app\assets\TableAsset;
use app\models\KeyMap;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $goods \app\models\Goods
 */

TableAsset::register($this);

$this->title = '商品详情';
$this->params['breadcrumbs'][] = '商品管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">基本信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $goods->id;?></td>
    </tr>
    <tr>
        <th>类型</th>
        <td><?php echo KeyMap::getValue('goods_type', $goods->type);?></td>
    </tr>
    <tr>
        <th>店铺</th>
        <td><?php echo Html::encode($goods->shop->name);?></td>
    </tr>
    <tr>
        <th>类型</th>
        <td><?php echo Html::encode($goods->goods_type->name);?></td>
    </tr>
    <tr>
        <th>分类</th>
        <td><?php echo Html::encode($goods->goods_category->name);?></td>
    </tr>
    <?php if (!empty($goods->scid)) {?>
        <tr>
            <th>店铺分类</th>
            <td><?php echo Html::encode($goods->shopGoodsCategory->name);?></td>
        </tr>
    <?php }?>
    <?php if (!empty($goods->bid)) {?>
        <tr>
            <th>品牌</th>
            <td><?php echo Html::encode($goods->goods_brand->name);?></td>
        </tr>
    <?php }?>
    <tr>
        <th>标题</th>
        <td><?php echo Html::encode($goods->title)?></td>
    </tr>
    <tr>
        <th>关键词</th>
        <td><?php echo Html::encode($goods->keywords);?></td>
    </tr>
    <tr>
        <th>描述</th>
        <td><?php echo Html::encode($goods->desc);?></td>
    </tr>
    <tr>
        <th>价格</th>
        <td><?php echo $goods->price;?></td>
    </tr>
    <?php if (!empty($goods->share_commission_type)) {?>
        <tr>
            <th>佣金方式</th>
            <td><?php echo KeyMap::getValue('goods_share_commission_type', $goods->share_commission_type);?></td>
        </tr>
        <tr>
            <th>佣金</th>
            <td><?php echo $goods->share_commission_value;?></td>
        </tr>
    <?php }?>
    <tr>
        <th>库存</th>
        <td><?php echo $goods->stock;?></td>
    </tr>
    <tr>
        <th>主图</th>
        <td><img src="<?php echo Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->main_pic;?>_128x128"></td>
    </tr>
    <?php if (!empty($goods->banner_pic)) {?>
        <tr>
            <th>横幅图</th>
            <td><img src="<?php echo Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $goods->banner_pic;?>_128x128"></td>
        </tr>
    <?php }?>
    <tr>
        <th>详情图</th>
        <td><?php foreach ($goods->getDetailPicList() as $pic) {
                echo Html::img(Yii::$app->params['site_host'] . Yii::$app->params['upload_url'] . $pic . '_128x128');
            }?></td>
    </tr>
    <tr>
        <th>运费计费方式</th>
        <td><?php echo KeyMap::getValue('goods_deliver_fee_type', $goods->deliver_fee_type);?></td>
    </tr>
    <tr>
        <th>重量</th>
        <td><?php echo $goods->weight;?></td>
    </tr>
    <tr>
        <th>体积</th>
        <td><?php echo $goods->bulk;?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo Keymap::getValue('goods_status', $goods->status);?></td>
    </tr>
    <tr>
        <th>创建时间</th>
        <td><?php echo Yii::$app->formatter->asDatetime($goods->create_time);?></td>
    </tr>
    <tr>
        <th>上架时间</th>
        <td><?php echo empty($goods->sale_time) ? '' :Yii::$app->formatter->asDatetime($goods->sale_time);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($goods->remark);?></td>
    </tr>
    <tr>
        <th>详细</th>
        <td><div style="max-height:400px; overflow-y: auto;"><?php echo $goods->content;?></div></td>
    </tr>
</table>
