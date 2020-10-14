<?php

namespace app\models;

use Yii;
use yii\base\Exception;

/**
 * 微信公众号接口
 * Class WeixinOaApi
 * @package app\models
 */
class WeixinOaApi extends WeixinApi
{
    /**
     * @var string 微信AppSecret
     */
    private $app_secret = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->app_id = System::getConfig('weixin_oa_app_id');
        $this->app_secret = System::getConfig('weixin_oa_app_secret');
        $this->mch_id = System::getConfig('weixin_oa_mch_id');
        $this->api_key = System::getConfig('weixin_oa_api_key');
        $this->cert_file = System::getConfig('weixin_oa_cert_file');
        $this->key_file = System::getConfig('weixin_oa_key_file');
        parent::init();
    }

    /**
     * 获取微信AppId
     * @return string
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * 获取微信AccessToken
     * @return string
     * @throws Exception
     */
    public function getAccessToken()
    {
        $cache = Yii::$app->cache->get('weixin_oa_access_token');
        if (!empty($cache)) {
            $json = json_decode($cache, true);
            return $json['access_token'];
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->app_id . '&secret=' . $this->app_secret;
        $response = Util::get($url);
        Yii::warning($url);
        Yii::warning($response);
        $json = json_decode($response, true);
        if (!empty($json['errcode'])) {
            throw new Exception($json['errmsg']);
        }
        Yii::$app->cache->set('weixin_oa_access_token', $response, 7200);
        return $json['access_token'];
    }

    /**
     * 获取微信jsapi_ticket
     * @return string
     * @throws Exception
     */
    public function getJsApiTicket()
    {
        $cache = Yii::$app->cache->get('weixin_oa_jsapi_ticket');
        if (!empty($cache)) {
            $json = json_decode($cache, true);
            return $json['ticket'];
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $this->getAccessToken() . '&type=jsapi';
        $response = Util::get($url);
        Yii::warning($url);
        Yii::warning($response);
        $json = json_decode($response, true);
        if (!empty($json['errcode'])) {
            throw new Exception($json['errmsg']);
        }
        Yii::$app->cache->set('weixin_oa_jsapi_ticket', $response, 7200);
        return $json['ticket'];
    }

    /**
     * 获取微信code跳转地址
     * @param $redirect_url string 回调地址
     * @param $scope string 授权方式 snsapi_base 静默授权并且自动跳转到回调页面，只能获取openid snsapi_userinfo 需要用户同意，可以获取用户基本信息
     * @return string
     */
    public function codeUrl($redirect_url, $scope = 'snsapi_base')
    {
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $this->app_id . '&redirect_uri=' . urlencode($redirect_url) . '&response_type=code&scope=' . $scope . '&state=#wechat_redirect';
        return $url;
    }

    /**
     * 根据code获取用户openid
     * @param $code string 微信oauth code
     * @return string
     * @throws Exception
     */
    public function code2Openid($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->app_id . '&secret=' . $this->app_secret . '&code=' . $code . '&grant_type=authorization_code';
        $response = Util::get($url);
        Yii::warning($url);
        Yii::warning($response);
        $json = json_decode($response, true);
        if (!empty($json['errcode'])) {
            throw new Exception($json['errmsg']);
        }
        return $json['openid'];
    }

    /**
     * 获取网页配置
     * @param string $url 需要传入参与签名的页面url
     * @return array
     * @throws Exception
     */
    public function jsWxConfig($url)
    {
        $url = preg_replace('/#.*/', '', $url);
        $config = [
            'nonceStr' => Util::randomStr(32),
            'timestamp' => strval(time()),
        ];
        $config['signature'] = $this->makeJsSign(array_merge($config, [
            'url' => $url,
            'jsapi_ticket' => $this->getJsApiTicket(),
        ]));
        $config['appId'] = $this->app_id;
        return $config;
    }

    /**
     * 统一下单
     * @param string $name 订单名称
     * @param string $no 订单编号
     * @param float $money 订单金额
     * @param string $trade_type 交易方式
     * @param string $openid 用户微信OpenId，当$trade_type为JSAPI时必传
     * @return array prepay_id：预支付会话标识，有效期2小时；code_url：二维码内容（只有NATIVE支付方式才有）
     * @throws Exception
     */
    public function unifiedOrder($name, $no, $money, $trade_type = 'JSAPI', $openid = '')
    {
        $url = $this->host . '/pay/unifiedorder';
        $post = [];
        $post['appid'] = $this->app_id;
        $post['mch_id'] = $this->mch_id;
        $post['device_info'] = 'WEB';
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['body'] = $name;
        $post['out_trade_no'] = $no;
        $post['total_fee'] = round($money * 100);
        $post['spbill_create_ip'] = Yii::$app->request->userIP;
        $post['notify_url'] = Yii::$app->params['site_host'] . '/api/weixin/oa-notify';
        $post['trade_type'] = $trade_type;
        if (!empty($openid)) {
            $post['openid'] = $openid;
        }
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml);
        $res = $this->postXmlCurl($xml, $url);
        Yii::warning($res);
        $xml = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        $xml = (array)$xml;
        if ($xml['return_code'] != 'SUCCESS') {
            throw new Exception($xml['return_msg']);
        }
        if ($xml['result_code'] != 'SUCCESS') {
            throw new Exception('code:' . $xml['err_code'] . ';msg:' . $xml['err_code_des']);
        }
        return [
            $xml['prepay_id'],
            $trade_type == 'NATIVE' ? $xml['code_url'] : '',
        ];
    }

    /**
     * 查询订单
     * @param string $out_trade_no 商户订单号
     * @return array
     * @throws Exception
     */
    public function orderQuery($out_trade_no)
    {
        $url = $this->host . '/pay/orderquery';
        $post = [];
        $post['appid'] = $this->app_id;
        $post['mch_id'] = $this->mch_id;
        $post['out_trade_no'] = $out_trade_no;
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml, 'weixin'); // 记录XML数据
        $res = $this->postXmlCurl($xml, $url);
        Yii::warning($res, 'weixin'); // 记录返回数据
        $json = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($json['return_code'] != 'SUCCESS') {
            throw new Exception($json['return_msg']);
        }
        if ($json['result_code'] != 'SUCCESS') {
            throw new Exception('code:' . $json['err_code'] . ';msg:' . $json['err_code_des']);
        }
        return $json;
    }

    /**
     * 关闭订单
     * @param string $out_trade_no 商户订单号
     * @throws Exception
     * @return array result_code：业务结果；result_msg：业务结果描述
     */
    public function closeOrder($out_trade_no)
    {
        $url = $this->host . '/pay/closeorder';
        $post = [];
        $post['appid'] = $this->app_id;
        $post['mch_id'] = $this->mch_id;
        $post['out_trade_no'] = $out_trade_no;
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml); // 记录XML数据
        try {
            $res = $this->postXmlCurl($xml, $url);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $xml = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml['return_code'] != 'SUCCESS') {
            throw new Exception($xml['return_msg']);
        }
        if ($xml['result_code'] != 'SUCCESS') {
            throw new Exception('code:' . $xml['err_code'] . ';msg:' . $xml['err_code_des']);
        }
        Yii::warning($xml); // 记录返回XML数据
        return [
            $xml['result_code'],
            $xml['result_msg'],
        ];
    }

    /**
     * 申请退款
     * @param string $out_trade_no 商户订单号
     * @param string $out_refund_no 商户退款单号
     * @param string $total_money 订单金额
     * @param string $refund_money 退款金额
     * @param string $refund_desc 退款原因
     * @throws Exception
     * @return array
     */
    public function refund($out_trade_no, $out_refund_no, $total_money, $refund_money, $refund_desc = ''){
        $url = $this->host . '/secapi/pay/refund';
        $post = [];
        $post['appid'] = $this->app_id;
        $post['mch_id'] = $this->mch_id;
        $post['out_trade_no'] = $out_trade_no;
        $post['out_refund_no'] = $out_refund_no;
        $post['total_fee'] = round($total_money * 100);
        $post['refund_fee'] = round($refund_money * 100);
        if (!empty($refund_desc)) {
            $post['refund_desc'] = $refund_desc;
        }
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml); // 记录XML数据
        try {
            $res = $this->postXmlCurl($xml, $url, true);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $xml = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml['return_code'] != 'SUCCESS') {
            throw new Exception($xml['return_msg']);
        }
        if ($xml['result_code'] != 'SUCCESS') {
            throw new Exception('code:' . $xml['err_code'] . ';msg:' . $xml['err_code_des']);
        }
        Yii::warning($xml); // 记录返回XML数据
        return $xml;
    }

    /**
     * 查询退款
     * @param string $out_trade_no 商户订单号
     * @throws Exception
     * @return array
     */
    public function refundQuery($out_trade_no)
    {
        $url = $this->host . '/pay/refundquery';
        $post = [];
        $post['appid'] = $this->app_id;
        $post['mch_id'] = $this->mch_id;
        $post['out_trade_no'] = $out_trade_no;
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml); // 记录XML数据
        try {
            $res = $this->postXmlCurl($xml, $url);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        $xml = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml['return_code'] != 'SUCCESS') {
            throw new Exception($xml['return_msg']);
        }
        if ($xml['result_code'] != 'SUCCESS') {
            throw new Exception('code:' . $xml['err_code'] . ';msg:' . $xml['err_code_des']);
        }
        Yii::warning($xml); // 记录返回XML数据
        return $xml;
    }

    /**
     * 下载对账单
     * @param string $bill_date 日期Ymd
     * @return string
     * @throws Exception
     */
    public function downloadBill($bill_date = null)
    {
        Yii::warning('微信对账：' . $bill_date, 'wechat');
        $url = $this->host . '/pay/downloadbill';
        $post = [];
        if (empty($bill_date)) {
            $bill_date = date('Ymd', time() - 3600 *24);
        }
        $post['appid'] = $this->app_id;
        $post['bill_date'] = $bill_date;
        $post['bill_type'] = 'ALL';
        $post['mch_id'] = $this->mch_id;
        $post['nonce_str'] = Util::randomStr(32, 7);
        $post['sign'] = $this->makeSign($post);
        $xml = '<xml>';
        foreach ($post as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $xml .= '</xml>';
        Yii::warning($xml, 'weixin'); // 记录XML数据
        try {
            $res = $this->postXmlCurl($xml, $url);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }
        Yii::warning($res, 'weixin');
        return $res;
    }

    /**
     * 定时任务获取对账单
     * @param string $bill_date 下载时间
     * @throws Exception
     * @return bool
     */
    public static function task_bank_reconciliation($bill_date = null)
    {
        $api = new WeixinOaApi();
        $res = $api->downloadBill($bill_date);
        $xml_parser = xml_parser_create();
        if (xml_parse($xml_parser, $res)) {
            $xml = (array)simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
            Yii::warning(print_r($xml['return_code'].':'.$xml['return_msg'], true), 'weixin');
            return $xml['return_code'].':'.$xml['return_msg'];
        } else {
            $res = str_replace('`', "", $res);
            $res = explode("\r", $res);
            unset($res[0]);
            foreach($res as $k => $val) {
                $res[$k] = explode(',', $val);
            }
            $count = count($res);
            unset($res[$count]);
            unset($res[$count -1]);
            unset($res[$count -2]);
            foreach ($res as $key => $val) {
                $res[$key][0] = strtotime($val[0]);
            }
            $key = ['trade_time', 'app_id', 'mch_id', 'sub_mch_id', 'client_no', 'weixin_trade_id', 'out_trade_no', 'user_open_id', 'trade_type', 'trade_status', 'pay_bank', 'currency', 'order_amount', 'merchant_red', 'refund_trade_id', 'refund_out_trade_no', 'refund_amount', 'refund_merchant_red', 'refund_type', 'refund_status', 'subject', 'merchant_data', 'charge', 'charge_ratio'];
            Yii::$app->db->createCommand()->batchInsert(BankReconciliationWeixin::tableName(), $key, $res)->execute();
        }
        return '对账完成：' . $bill_date;
    }

    /**
     * 生成网页需要的签名
     * @param $data array 参数列表
     * @return string
     */
    public function makeJsSign($data)
    {
        ksort($data);
        $stringA = '';
        foreach ($data as $k => $v) {
            if (empty($v) && $v !== '0') {
                continue;
            }
            $stringA .= '&' . strtolower($k) . '=' . $v;
        }
        $stringA = substr($stringA, 1);
        $key = sha1($stringA);
        return $key;
    }
}
