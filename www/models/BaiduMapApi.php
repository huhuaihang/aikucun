<?php

namespace app\models;

use yii\base\Model;

/**
 * 百度地图接口
 * Class BaiduMapApi
 * @package app\models
 */
class BaiduMapApi extends Model
{
    private $ak;
    private $sk;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->ak = System::getConfig('baidu_map_ak');
        $this->sk = System::getConfig('baidu_map_sk');
        parent::init();
    }

    /**
     * 根据IP地址获取
     * @param $ip string IP地址
     * @return string
     */
    public function locationIp($ip)
    {
        $base = 'http://api.map.baidu.com';
        $uri = '/location/ip';
        $params = [
            'ip' => $ip,
            'ak' => $this->ak,
            'coor' => 'bd09ll',
        ];
        $sn = $this->caculateAKSN($this->ak, $this->sk, $uri, $params);
        $url = $base . $uri . '?'. http_build_query($params) . '&sn=' . $sn;
        $response = Util::get($url);
        return $response;
    }

    /**
     * 逆地理编码
     * @param $location string 纬度,经度
     * @return string
     */
    public function geocoder($location)
    {
        $base = 'http://api.map.baidu.com';
        $uri = '/geocoder/v2/';
        $params = [
            'location' => $location,
            'ak' => $this->ak,
            'output' => 'json',
        ];
        $sn = $this->caculateAKSN($this->ak, $this->sk, $uri, $params);
        $url = $base . $uri . '?'. http_build_query($params) . '&sn=' . $sn;
        $response = Util::get($url);
        return $response;
    }

    /**
     * 生成sn
     * @param $ak string ak
     * @param $sk string sk
     * @param $uri string 请求路径
     * @param $params array 请求参数
     * @param string $method 请求方式
     * @return string
     */
    private function caculateAKSN($ak, $sk, $uri, $params, $method = 'GET')
    {
        if ($method === 'POST'){
            ksort($params);
        }
        $querystring = http_build_query($params);
        return md5(urlencode($uri . '?' . $querystring . $sk));
    }
}
