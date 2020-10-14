<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 微信接口基类
 * Class WeixinApi
 * @package app\models
 */
class WeixinApi extends Model
{
    /**
     * @var string 接口域名
     */
    protected $host = 'https://api.mch.weixin.qq.com';
    /**
     * @var string 微信AppId
     */
    protected $app_id = '';
    /**
     * @var string 微信商户Id
     */
    protected $mch_id = '';
    /**
     * @var string 秘钥
     */
    protected $api_key = '';
    /**
     * @var string 公钥证书文件地址
     */
    protected $cert_file = '';
    /**
     * @var string 私钥证书文件地址
     */
    protected $key_file = '';

    /**
     * 生成签名
     * @param array $data 参数列表
     * @param boolean $withAppId 加密时是否需要将AppId放入
     * @return string
     */
    public function makeSign($data, $withAppId = false)
    {
        if ($withAppId) {
            $data['appId'] = $this->app_id;
        }
        ksort($data);
        $stringA = '';
        foreach ($data as $k => $v) {
            if (empty($v) && $v !== '0') {
                continue;
            }
            $stringA .= $k . '=' . $v . '&';
        }
        $stringA .= 'key=' . $this->api_key;
        $key = md5($stringA);
        $key = strtoupper($key);
        return $key;
    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws Exception
     * @return string
     */
    protected function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        // 设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 如果有配置代理这里就设置代理
        if (false) {
            curl_setopt($ch,CURLOPT_PROXY, '0.0.0.0');
            curl_setopt($ch,CURLOPT_PROXYPORT, 0);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, Yii::$app->params['upload_path'] . $this->cert_file);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, Yii::$app->params['upload_path'] . $this->key_file);
        }
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new Exception("curl出错，错误码:$error");
        }
    }
}
