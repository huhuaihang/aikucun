<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * IP地址城市对应
 * Class IpCity
 * @package app\models
 *
 * @property integer $id PK
 * @property string $ip IP地址
 * @property string $area 区域编码
 * @property string $source 数据来源
 * @property integer $create_time 创建时间
 * @property string $data 原始数据
 *
 * @property City $city 关联城市
 */
class IpCity extends ActiveRecord
{
    /**
     * 关联城市
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['code' => 'area']);
    }

    /**
     * 根据IP地址获取区域
     * @param $ip string IP地址
     * @return false|IpCity
     */
    public static function findByIp($ip = null)
    {
        if ($ip == null) {
            $ip = IpCity::ipAddress();
        }
        /** @var IpCity $model */
        $model = IpCity::find()->andWhere(['ip' => $ip])->one();
        if (!empty($model)) {
            if ($model->create_time > time() - 30 * 86400) {
                return $model;
            }
            try {
                $model->delete();
            } catch (\Throwable $t) {
            }
        }
        $r = IpCity::findByIpBaidu($ip);
        if (is_string($r)) {
            return false;
        }
        return $r;
    }

    /**
     * 获取当前IP地址
     * @return string
     */
    public static function ipAddress()
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ip_address = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ip_address = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip_address = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $ip_address = getenv("HTTP_CLIENT_IP");
            } else {
                $ip_address = getenv("REMOTE_ADDR");
            }
        }
        return $ip_address;
    }

    /**
     * 使用淘宝接口获取地址信息
     * @param $ip string IP地址
     * @return IpCity|string
     */
    private static function findByIpTaobao($ip)
    {
        $r = Util::get('http://ip.taobao.com/service/getIpInfo.php?ip=' . $ip);
        // {"code":0,"data":{"ip":"210.75.225.254","country":"\u4e2d\u56fd","area":"\u534e\u5317","region":"\u5317\u4eac\u5e02","city":"\u5317\u4eac\u5e02","county":"","isp":"\u7535\u4fe1","country_id":"86","area_id":"100000","region_id":"110000","city_id":"110000","county_id":"-1","isp_id":"100017"}}
        if (empty($r)) {
            return '接口没有返回值';
        }
        $json = json_decode($r, true);
        if (empty($json)) {
            return '无法解析JSON数据：' . $r;
        }
        if (!isset($json['code']) || $json['code'] != 0) {
            return '接口调用错误：' . $r;
        }
        $model = new IpCity();
        $model->ip = $ip;
        $model->area = $json['data']['city_id'];
        $model->source = 'ip.taobao.com';
        $model->create_time = time();
        $model->data = $r;
        if (!$model->save()) {
            return '无法保存到数据库';
        }
        return $model;
    }

    /**
     * 使用百度地图接口获取地址信息
     * @param $ip string IP地址
     * @return IpCity|string
     */
    private static function findByIpBaidu($ip)
    {
        if (strpos($ip, ',') !== false) {
            $ip = preg_replace('/^([^,]*).*/', '$1', $ip);
        }
        $api = new BaiduMapApi();
        $r = $api->locationIp($ip);
        if (empty($r)) {
            return '接口没有返回值';
        }
        $json = json_decode($r, true);
        if (empty($json)) {
            return '无法解析JSON数据：' . $r;
        }
        if ($json['status'] != 0) {
            return '接口调用错误：' . $json['status'];
        }
        $location = $json['content']['point']['y'] . ',' . $json['content']['point']['x'];
        $r = $api->geocoder($location);
        if (empty($r)) {
            return '接口没有返回值';
        }
        $json = json_decode($r, true);
        if (empty($json)) {
            return '无法解析JSON数据：' . $r;
        }
        if ($json['status'] != 0) {
            Yii::warning('根据IP获取地址信息错误：' . $r);
            return '接口调用错误：' . $json['status'];
        }
        $model = new IpCity();
        $model->ip = $ip;
        $model->area = $json['result']['addressComponent']['adcode'];
        $model->source = 'api.map.baidu.com';
        $model->create_time = time();
        $model->data = $r;
        if (!$model->save()) {
            return '无法保存到数据库';
        }
        return $model;
    }
}
