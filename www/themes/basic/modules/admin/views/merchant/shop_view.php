<?php

use app\models\City;
use app\models\KeyMap;
use app\models\ShopConfig;
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $shop \app\models\Shop
 */

$this->title = '店铺详情';
$this->params['breadcrumbs'][] = '店铺管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<table class="table table-striped table-bordered table-hover">
    <tr>
        <th colspan="2">店铺信息</th>
    </tr>
    <tr>
        <th>编号</th>
        <td><?php echo $shop->id;?></td>
    </tr>
    <tr>
        <th>名称</th>
        <td><?php echo Html::encode($shop->name);?></td>
    </tr>
    <tr>
        <th>LOGO</th>
        <td><?php $logo = ShopConfig::getConfig($shop->id, 'logo');
            if (!empty($logo)) {
                echo Html::img(Yii::$app->params['upload_url'] . $logo, ['width' => 200]);
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>店铺故事轮播图</th>
        <td><?php $banners = ShopConfig::getConfig($shop->id, 'banners');
            $banners = json_decode($banners, true);
            if (!empty($banners) && is_array($banners)) {
                foreach ($banners as $banner) {
                    echo Html::img(Yii::$app->params['upload_url'] . $banner, ['width' => 200, 'height' => 300]);
                }
            } else {
                echo '<i>没有上传</i>';
            }?>
        </td>
    </tr>
    <tr>
        <th>区域</th>
        <td><?php if (!empty($shop->name) && !empty($shop->area)) {
                $city = City::findByCode($shop->area);
                echo implode(' ', $city->address());
            } else {
                echo '<i>没有设置</i>';
            }?></td>
    </tr>
    <tr>
        <th>店铺地址</th>
        <td><?php echo Html::encode(ShopConfig::getConfig($shop->id, 'address'));?></td>
    </tr>
    <tr>
        <th>店铺地址</th>
        <td>
            <div id="containers"></div>
        </td>
    </tr>
    <tr>
        <th>店铺客服电话</th>
        <td><?php echo Html::encode(ShopConfig::getConfig($shop->id, 'service_tel'));?></td>
    </tr>
    <tr>
        <th>状态</th>
        <td><?php echo KeyMap::getValue('shop_status', $shop->status);?></td>
    </tr>
    <tr>
        <th>备注</th>
        <td><?php echo Html::encode($shop->remark);?></td>
    </tr>
</table>
<style>
    #containers {
        height: 400px;
    }
</style>
<script type="text/javascript"  src="https://webapi.amap.com/maps?v=1.4.2&key=c2e204c095440f0b6c465ddac326cd87&plugin=AMap.Autocomplete,AMap.DistrictSearch"></script>
<script>
    var  marker, map = new AMap.Map("containers", {
        resizeEnable: true,
        center: [<?php echo empty(ShopConfig::getConfig($shop->id, 'longitude')) ? 118.408133 : ShopConfig::getConfig($shop->id, 'longitude');?>,<?php echo empty(ShopConfig::getConfig($shop->id, 'latitude')) ? 35.022768 : ShopConfig::getConfig($shop->id, 'latitude');?>]
    });
    marker = new AMap.Marker({
        icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
        position: [<?php echo empty(ShopConfig::getConfig($shop->id, 'longitude')) ? 118.408133 : ShopConfig::getConfig($shop->id, 'longitude');?>,<?php echo empty(ShopConfig::getConfig($shop->id, 'latitude')) ? 35.022768 : ShopConfig::getConfig($shop->id, 'latitude');?>]
    });
    marker.setMap(map);
    map.setZoom(12);
    //为地图注册click事件获取鼠标点击出的经纬度坐标
    var clickEventListener = map.on('click', function(e) {
        if (marker) {
            marker.setMap(null);
            marker = null;
        }
        marker = new AMap.Marker({
            icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
            position: [e.lnglat.getLng() , e.lnglat.getLat()]
        });
        marker.setMap(map);
        $(".longitude").val(e.lnglat.getLng());
        $(".latitude").val(e.lnglat.getLat());
    });
</script>