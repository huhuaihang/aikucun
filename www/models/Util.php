<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 助手类
 * Class Util
 * @package app\models
 */
class Util extends Model
{
    /**
     * 根据数据库类型返回日期分组SQL
     * @param string $driver_name sqlite|mysql
     * @param string $split day|week|month|season|year
     * @param string $column 数据库字段名
     * @return string
     */
    public static function getDateGroup($driver_name, $split, $column)
    {
        switch ($driver_name) {
            case 'mysql':
                switch ($split) {
                    case 'year':
                        $sql = "from_unixtime({$column}, '%Y')";
                        break;
                    case 'season':
                        $sql = "concat(from_unixtime({$column}, '%Y.'), quarter(from_unixtime({$column})))";
                        break;
                    case 'month':
                        $sql = "from_unixtime({$column}, '%Y-%m')";
                        break;
                    case 'week':
                        $sql = "from_unixtime({$column}, '%U')";
                        break;
                    case 'day':
                    default:
                        $sql = "from_unixtime({$column}, '%Y-%m-%d')";
                }
                break;
            case 'sqlite':
            default:
                switch ($split) {
                    case 'year':
                        $sql = "strftime('%Y', {$column}, 'unixepoch', 'localtime')";
                        break;
                    case 'season':
                        $sql = "strftime('%Y.', {$column}, 'unixepoch', 'localtime') || cast(round(strftime('%m', {$column}, 'unixepoch', 'localtime') / 3.0 + 0.495) as int)";
                        break;
                    case 'month':
                        $sql = "strftime('%Y-%m', {$column}, 'unixepoch', 'localtime')";
                        break;
                    case 'week':
                        $sql = "strftime('%W', {$column}, 'unixepoch', 'localtime')";
                        break;
                    case 'day':
                    default:
                        $sql = "strftime('%Y-%m-%d', {$column}, 'unixepoch', 'localtime')";
                }
        }
        return $sql;
    }

    /**
     * 返回随机字符串
     * @param integer $len 随机字符串长度
     * @param integer $seed_type 字符内容类型，可叠加 1 数字 2 大写字母 4 小写字母
     * @return string
     */
    public static function randomStr($len = 32, $seed_type = 1)
    {
        $seed = '';
        if (($seed_type & 1) > 0) {
            $seed = '1234567890';
        }
        if (($seed_type & 2) > 0) {
            $seed .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if (($seed_type & 4) > 0) {
            $seed .= 'abcdefghijklmnopqrstuvwxyz';
        }
        $str = '';
        $seed_l = strlen($seed);
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($seed, rand(0, $seed_l - 1), 1);
        }
        return $str;
    }

    /**
     * GET请求数据
     * @param string $url
     * @return string
     */
    public static function get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        // curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回


        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**
     * POST请求数据
     * @param string $url
     * @param string|array $data
     * @return string
     */
    public static function post($url, $data)
    {
        if (is_array($data)) {
            array_walk($data, function (&$v, $k) {
                $v = $k . '=' . urlencode($v);
            });
            $data = implode('&', $data);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        // curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        // curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 31); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            \Yii::warning(curl_errno($curl));
        }
        if (curl_error($curl)) {
            \Yii::warning(curl_error($curl));
        }
        curl_close($curl);
        return $output;
    }

    /**
     * 判断文件是否为图片
     * @param $file
     * @return bool
     */
    public static function isImage($file)
    {
        $info = @getimagesize($file);
        if (!$info) {
            return false;
        }
        if ($info[0] <= 0 || $info[1] <= 0) {
            return false;
        }
        if (strpos($info['mime'], 'image') !== 0) {
            return false;
        }
        return true;
    }

    /**
     * URL 参数解析成 数组
     * https://www.baidu.com/index.php?m=content&c=index&a=lists&catid=6&area=0&author=0&h=0
     *
     * @param $query
     * @return array [m' => string 'content' (length=7)
     *               'c' => string 'index' (length=5)
     *               'a' => string 'lists' (length=5)
     *               'catid' => string '6' (length=1)
     *               'area' => string '0' (length=1)
     *               'author' => string '0' (length=1)
     *               'h' => string '0' (length=1)
     *               'region' => string '0' (length=1)
     *               's' => string '1' (length=1)
     *               'page' => string '1' (length=1)]
     */
    public static function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    /**
     * 下载文件
     * @param $url  string 下载路径
     * @param string $file  文件名
     * @param int $timeout  超时时间
     * @return bool|mixed|string
     */
    public static function download($url, $file="", $timeout=60) {
        $file = empty($file) ? pathinfo($url,PATHINFO_BASENAME) : $file;
        $dir = pathinfo($file,PATHINFO_DIRNAME);
        !is_dir($dir) && @mkdir($dir,0755,true);
        $url = str_replace(" ","%20",$url);

        if(function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            $temp = curl_exec($ch);
            if(@file_put_contents($file, $temp) && !curl_error($ch)) {
                return $file;
            } else {
                return false;
            }
        } else {
            $opts = array(
                "http"=>array(
                    "method"=>"GET",
                    "header"=>"",
                    "timeout"=>$timeout)
            );
            $context = stream_context_create($opts);
            if(@copy($url, $file, $context)) {
                //$http_response_header
                return $file;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取客户端真实IP地址
     * @return mixed
     */
    public static function realIp()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 字符串相似度
     * Levenshtein Distance
     * @param string $str1
     * @param string $str2
     * @return float
     */
    public static function strEditDistance($str1, $str2)
    {
        $len1 = mb_strlen($str1);
        $len2 = mb_strlen($str2);
        // 建立数组，比字符长度大一个空间
        $dif = []; // 二维数组 [$len1 + 1][$len2 + 1]
        // 赋初值
        for ($a = 0; $a <= $len1; $a++) {
            $dif[$a][0] = $a;
        }
        for ($a = 0; $a <= $len2; $a++) {
            $dif[0][$a] = $a;
        }
        // 计算两个字符是否一样，计算左上的值
        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $tmp = mb_substr($str1, $i - 1, 1) == mb_substr($str2, $j - 1, 1) ? 0 : 1;
                $dif[$i][$j] = min($dif[$i - 1][$j - 1] + $tmp, $dif[$i][$j - 1] + 1, $dif[$i - 1][$j] + 1);
            }
        }
        return 1 - $dif[$len1][$len2] / max($len1, $len2);
    }


    /**
     * 数组元素个数取偶数
     * @param  $array array
     * @return  array
    */
    public static function array_even($array)
    {

        if (count($array) % 2 != 0) {
            array_splice($array,0,1);
        }
        return $array;
    }

    /**
     * 返回金额（两位小数）
     * @param float $money 金额
     * @param integer $accuracy
     * @return string
     */
    public static function money($money,$accuracy=3)
    {
        $str_ret = 0;
        if (empty($money) === false) {
            $str_ret = sprintf("%.".$accuracy."f", substr(sprintf("%.".($accuracy+1)."f", floatval($money)), 0, -1));
        }

        return floatval($str_ret);

    }

    /**
     * 任意精度比较
     * @param $a float
     * @param $b float
     * @param $s integer scale
     * @return int
     */
    public static function comp($a, $b, $s)
    {
        return bccomp(strval(round($a, $s)), strval(round($b, $s)), $s);
    }

    /**
     * 返回文件地址
     * @param $uri string 文件保存相对路径
     * @param bool $schema 是否返回完整路径
     * @param string $suffix 后缀
     * @return string
     */
    public static function fileUrl($uri, $schema = true, $suffix = '')
    {
        if (empty($uri)) {
            return '';
        }
        if (preg_match('/^http/', $uri)) {
            $url = $uri;
        } else {
            $url = Yii::$app->params['upload_url'] . $uri;
            if ($schema) {
                $url = Yii::$app->params['site_host'] . $url;
            }
        }
        if (!empty($suffix)) {
            $url .= $suffix;
        }
        return $url;
    }

    /**
     * 生成JWT
     * @param $data array 数据
     * @param $key string 秘钥
     * @return string JWT Token
     */
    public static function makeJWT($data, $key)
    {
        $header = base64_encode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256',
        ]));
        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha256', $header . '.' . $payload, md5($key));
        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * 解析JWT Token
     * @param $token string JWT Token
     * @param $key string 秘钥
     * @return array
     * @throws Exception
     */
    public static function checkJWT($token, $key = null)
    {
        try {
            list($header, $payload, $signature) = explode('.', $token);
        } catch (\Exception $e) {
            throw new Exception('Token无法解析。');
        }
        $payloadJson = json_decode(base64_decode($payload), true);
        if (empty($payloadJson)) {
            throw new Exception('Token无法解析。');
        }
        if (empty($key)) {
            if (!isset($payloadJson['app'])) {
                throw new Exception('Token格式错误。');
            }
            $apiClient = ApiClient::findByAppId($payloadJson['app']);
            if (empty($apiClient) || $apiClient->status != ApiClient::STATUS_OK) {
                throw new Exception('Token已失效。');
            }
            $key = $apiClient->app_secret;
        }
        if (hash_hmac('sha256', $header . '.' . $payload, md5($key)) !== $signature) {
            throw new Exception('Token签名错误。');
        }
        return $payloadJson;
    }

    /**
     * 金额格式化  过滤.00  转成 字符串
     * @param $price float
     * @return string
     */
    public static function convertPrice($price)
    {
        return (string)floatval(sprintf("%.2f",$price));
    }

    /**
     * 判断是否为json
     * @param string $data
     * @param bool $assoc
     * @return array|bool|mixed|string
     */
    function is_json($data = '', $assoc = false) {
        $data = json_decode($data, $assoc);
        if ($data && (is_object($data)) || (is_array($data) && !empty(current($data)))) {
            return $data;
        }
        return false;
    }
}
