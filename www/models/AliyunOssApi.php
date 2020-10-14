<?php

namespace app\models;

use OSS\Core\OssException;
use OSS\OssClient;
use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 阿里云OSS存储接口
 * Class AliyunOssApi
 * @package app\models
 */
class AliyunOssApi extends Model
{
    /**
     * @var string
     */
    private $accessKeyId;
    /**
     * @var string
     */
    private $accessKeySecret;
    /**
     * @var OssClient
     */
    private $ossClient;
    /**
     * @var string Bucket
     */
    private $bucket;
    /**
     * @var string Host
     */
    private $host;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->accessKeyId = System::getConfig('aliyun_oss_access_key_id');
        $this->accessKeySecret = System::getConfig('aliyun_oss_access_key_secret');
        $endpoint = System::getConfig('aliyun_oss_endpoint');
        $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $endpoint);
        $this->bucket = System::getConfig('aliyun_oss_bucket');
        $this->host = System::getConfig('aliyun_oss_host');
        parent::init();
    }

    /**
     * 上传文件
     * @param $name string 文件名称
     * @param $path string 上传文件本地路径
     * @return string 访问地址
     * @throws Exception
     */
    public function uploadFile($name, $path)
    {
        try {
            $this->ossClient->uploadFile($this->bucket, $name, $path);
            return $this->host . '/' . $name;
        } catch (OssException $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 删除文件
     * @param $name string 文件名称（附带地址）
     * @return mixed
     */
    public function deleteFile($name)
    {
        if (strpos($name, 'http') !== false) {
            $name = preg_replace('/^https?:\/\/[^\/]*\/(.*)$/', '$1', $name);
        }
        Yii::warning('删除文件：' . $name, 'aliyunoss');
        return $this->ossClient->deleteObject($this->bucket, $name);
    }

    /**
     * 第三方授权
     * @param $path string 路径
     * @param $expire integer 超时秒
     * @return array
     */
    public function ossPolicy($path, $expire = 600)
    {
        $now = time();
        $end = $now + $expire;
        $expiration = date('c', $end);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        $expiration .= 'Z';

        $dir = $path . '/';

        //最大文件大小.用户可以自己设置
        $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
        $conditions[] = $condition;

        //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
        $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
        $conditions[] = $start;


        $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
        //echo json_encode($arr);
        //return;
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $this->accessKeySecret, true));

        $response = array();
        $response['accessid'] = $this->accessKeyId;
        $response['host'] = $this->host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        //这个参数是设置用户上传指定的前缀
        $response['dir'] = $dir;
        return $response;
    }
}
