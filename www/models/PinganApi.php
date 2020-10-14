<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 平安银行接口
 * Class PinganApi
 * @package app\models
 * 
 * 接口调用顺序：
 * UnionAPI_Open接口->UnionAPI_Opened接口->UnionAPI_SSMS接口->UnionAPI_Submit接口->KH0003接口
 * 可以理解为：先发起开通UnionAPI_Open开通银行卡，
 * 然后调用UnionAPI_Opened接口查询出商户下的会员开通的所有银行卡信息（如果想显示银行和LOGO可以根据返回的PlantBankId来判断），
 * 商户将所有开通的银行卡展现给商户的会员选择，商户根据会员选择的支付银行，
 * 组装相关数据调用UnionAPI_SSMS接口获取支付短信验证码，
 * 然后商户组装好短信验证码等支付数据调用UnionAPI_Submit接口来支付，
 * 最后T+1日调用KH0003接口来进行对账
 */
class PinganApi extends Model
{
    private $masterId; // 商户号
    private $merchantCertFile; // 私钥文件
    private $trustPayCertFile; // 公钥文件
    private $ssl_password; // SSL密码
    private $notify_url; // 回调地址
    private $return_url; // 返回地址

    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::info('初始化平安银行支付接口', 'pingan');
        $this->masterId = System::getConfig('pingan_master_id');
        $this->merchantCertFile = System::getConfig('pingan_merchant_cert_file');
        $this->trustPayCertFile = System::getConfig('pingan_trust_pay_cert_file');
        $this->ssl_password = System::getConfig('pingan_ssl_password');
        $this->notify_url = System::getConfig('pingan_notify_url');
        $this->return_url = System::getConfig('pingan_return_url');
        parent::init();
    }

    /**
     * 订单列表信息查询接口
     * 是订单支付列表信息查询接口，
     * 只能返回成功支付（状态为01）的订单列表信息，
     * 支付未成功或者失败不会返回；
     * 不会返回退款订单信息
     * @param string $beginDate 查询开始日期（支付完成日期） 20171001000000
     * @param string $endDate 查询结束日期（支付完成日期） 20171031235959
     * @return false|array
     */
    public function KH0002($beginDate, $endDate)
    {
        $gateway = 'https://ebank.sdb.com.cn/corporbank/KH0002.pay';

        $data = [
            'masterId' => $this->masterId,
            'beginDate' => $beginDate,
            'endDate' => $endDate,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 对账接口，
     * 例如今天是T日，
     * 只能查询并且返回T-1日的对账信息，因为平安银行是T+1日做对账（一定要开发，否则无法投产）
     * @param string $date 对账日期，格式：YYYYMMDD
     * @return false|array
     */
    public function KH0003($date)
    {
        $gateway = 'https://ebank.sdb.com.cn/corporbank/KH0003.pay';

        $data = [
            'masterId' => $this->masterId,
            'date' => $date,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 单笔退款接口，
     * B2B企业支付不能退款，
     * B2C个人支付可以退款，可以多次退款，总退款金额小于等于支付金额，
     * 也可以一次性全额退款
     * @param string $refundNo 退款单号，严格遵守格式：商户号+8位日期+8位流水号
     * @param string $orderId 原订单号
     * @param string $currency 币种，目前只支持RMB
     * @param float $refundAmt 退款金额，12整数，2小数，必须等于原订单金额
     * @param string $objectName 退款原因（商户自定）
     * @param string $remark 备注字段（商户自定）
     * @return false|array
     * @throws Exception
     */
    public function KH0005($refundNo, $orderId, $currency, $refundAmt, $objectName, $remark)
    {
        $gateway = 'https://ebank.sdb.com.cn/corporbank/KH0005.pay';
        $notifyUrl = $this->notify_url . '?type=refund';

        $data = [
            'masterId' => $this->masterId,
            'refundNo' => $refundNo,
            'orderId' => $orderId,
            'currency' => $currency,
            'refundAmt' => $refundAmt,
            'objectName' => $objectName,
            'remark' => $remark,
            'NOTIFYURL' => $notifyUrl,
        ];


        $xml_data = $this->array_to_xml($data);
        $xml_data = mb_convert_encoding($xml_data, 'GBK', 'UTF-8');

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign(Yii::$app->params['upload_path'] . $this->merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 退款订单查询接口，
     * 返回退款订单的信息
     * @param string $beginDate 查询开始时间（退款请求接收时间）YYYYMMDDHHMMSS
     * @param string $endDate 查询结束时间
     * @return false|array
     */
    public function KH0006($beginDate, $endDate)
    {

        $gateway = 'https://ebank.sdb.com.cn/corporbank/KH0006.pay';

        $data = [
            'masterId' => $this->masterId,
            'beginDate' => $beginDate,
            'endDate' => $endDate,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 银行卡开通接口，
     * 调用该接口来开通快捷支付，
     * 每一次只能开通一张银行卡，不能批量开通
     * @param string $customer_id 用户编号
     * @param string $return_url 绑定后返回地址
     * @return string HTML内容
     */
    public function UnionAPI_Open($customer_id, $return_url)
    {
        $gateway = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_Open.do';
        $notifyUrl = $this->notify_url . '?type=open';

        // 组装订单数据
        $data = [
            'masterId' => $this->masterId,
            'customerId' => $customer_id,
            'orderId' => $this->masterId . date('Ymd') . rand(10000000, 99999999),
            'dateTime' => date('YmdHis'),
        ];

        $data = $this->array_to_xml($data);

        // 获取orig和sign
        list($orig, $sign) = $this->_getOrigAndSing(Yii::$app->params['upload_path'] . $this->merchantCertFile, $data);

        $parameter = [
            'orig' => $orig,
            'sign' => $sign,
            'returnurl' => $this->return_url . '?return_url=' . urlencode($return_url),
            'NOTIFYURL' => $notifyUrl,
        ];

        return $this->showHtml($gateway, $parameter);
    }

    /**
     * 银行卡开通查询接口，
     * 调用该接口查询某银行卡号是否开通了快捷支付
     * @param string $customerId 商户会员号
     * @param string $accNo 银行卡号
     * @return false|array
     */
    public function UnionAPI_QueryOPN($customerId, $accNo)
    {
        $gateway = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_QueryOPN.do';

        $data = [
            'masterId' => $this->masterId,
            'customerId' => $customerId,
            'accNo' => $accNo,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 已开通银行卡列表查询接口，
     * 调用该接口可以查询某商户下的某个会员名下开通的所有银行卡信息
     * @param $customer_id string 用户编号
     * @return false|array
     */
    public function UnionAPI_Opened($customer_id)
    {
        $gateway_url = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_Opened.do';
        $data = [
            'masterId' => $this->masterId,
            'customerId' => $customer_id,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway_url, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        if (!empty($orig_data['body'])) {
            return $orig_data['body'];
        } else {
            return [];
        }
    }

    /**
     * 已开通银行卡关闭接口，
     * 调用该接口可以关闭开通快捷支付的银行卡，
     * 只能每一次关闭一张银行卡
     * @param string $customerId 商户会员号
     * @param string $OpenId 银行开通ID
     * @return false|array
     */
    public function UnionAPI_OPNCL($customerId, $OpenId)
    {
        $gateway = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_OPNCL.do';

        $data = [
            'masterId' => $this->masterId,
            'customerId' => $customerId,
            'OpenId' => $OpenId,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 短信发送验证码接口，
     * 获取支付短信验证码
     * @param string $order_id 订单编号
     * @param float $money 订单金额，单位元（两位小数）
     * @param string $customer_id 用户编号
     * @param string $open_id 银行卡开通id
     * @return false|array
     * @throws Exception
     */
    public function UnionAPI_SSMS($order_id, $money, $customer_id, $open_id)
    {
        $send_url = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_SSMS.do';

        $data = [
            'masterId' => $this->masterId,
            'orderId' => $order_id,
            'currency' => 'RMB',
            'amount' => $money,
            'paydate' => date('YmdHis'),
            'customerId' => $customer_id,
            'OpenId' => $open_id,
        ];

        $xml_data = $this->array_to_xml($data);

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign(Yii::$app->params['upload_path'] . $this->merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($send_url, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }

        // 输出结果
        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        /*
         * <kColl id="output" append="false">
         *     <field id="errorCode" value="UKHPAY43" required="false"/>
         *     <field id="errorMsg" value="单笔支付不能小于0.1元" required="false"/>
         *     <field id="masterId" value="2000808425" required="false"/>
         *     <field id="orderId" value="20008084252017070464828167" required="false"/>
         *     <field id="currency" value="RMB" required="false"/>
         *     <field id="amount" value="0.02" required="false"/>
         *     <field id="status" value="02" required="false"/>
         *     <field id="paydate" value="20170704100137" required="false"/>
         *     <field id="customerId" value="256823" required="false"/>
         * </kColl>
         */
        return $this->xml_to_array($formOrig);
    }

    /**
     * 后台发起支付交易接口
     * @param string $order_id 订单编号
     * @param float $money 订单金额，单位元（两位小数）
     * @param string $name 订单名称
     * @param string $pay_date 支付时间，需要和发送短信返回的paydate一致
     * @param string $remark 备注信息
     * @param string $customer_id 用户编号
     * @param string $open_id 银行卡开通id
     * @param string $verify_code 短信验证码
     * @return array
     * @throws Exception
     */
    public function UnionAPI_Submit($order_id, $money, $name, $pay_date, $remark, $customer_id, $open_id, $verify_code)
    {
        $send_url = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_Submit.do';
        $notifyUrl = $this->notify_url . '?type=submit';

        $data = [
            'masterId' => $this->masterId,
            'orderId' => $order_id,
            'currency' => 'RMB',
            'amount' => $money,
            'objectName' => $name,
            'paydate' => $pay_date,
            'validtime' => '0',
            'remark' => $remark,
            'customerId' => $customer_id,
            'OpenId' => $open_id,
            'NOTIFYURL' => $notifyUrl,
            'verifyCode' => $verify_code
        ];

        $xml_data = $this->array_to_xml($data);
        $xml_data = mb_convert_encoding($xml_data, 'GBK', 'UTF-8');

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign(Yii::$app->params['upload_path'] . $this->merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($send_url, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 单笔支付订单查询接口，
     * 如果3分钟后，
     * 没有接收到后台通知既可以发起查询接口，
     * 支付完成后，
     * 如果比较着急，
     * 可以间隔2、4、8、16、32、60秒发查询
     * @param string $orderId 订单号
     * @param string $customerId 商户会员号
     * @return false|array
     */
    public function UnionAPI_OrderQuery($orderId, $customerId)
    {
        $gateway = 'https://ebank.sdb.com.cn/khpayment/UnionAPI_OrderQuery.do';

        $data = [
            'masterId' => $this->masterId,
            'orderId' => $orderId,
            'customerId' => $customerId,
        ];

        $xml_data = $this->array_to_xml($data);
        $merchantCertFile = Yii::$app->params['upload_path'] . $this->merchantCertFile;

        // 获取签名后的orig和sign
        $orig = $this->getOrig($xml_data);
        $sign = $this->getSign($merchantCertFile, $xml_data);

        // 通过curl请求接口
        $parms = 'orig=' . $orig . '&sign=' . $sign;
        $rsponse = $this->curl($gateway, $parms);

        $rsponse = preg_split('/\r|\n/', $rsponse, -1, PREG_SPLIT_NO_EMPTY);
        $rsponseSign = substr($rsponse[0], 5);
        $rsponseOrig = substr($rsponse[1], 5);

        // 解码
        $formSign = $this->_base64_url_decode($rsponseSign);
        $formOrig = $this->_base64_url_decode($rsponseOrig);
        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            return false;
        }

        $formOrig = iconv("GBK", "UTF-8", $formOrig);
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 生成订单号
     * @param string $id 自定义流水号，暂定8位字符
     * @return string
     */
    public function generateOrderNo($id)
    {
        return $this->masterId . date('Ymd') . $id;
    }

    /**
     * 银行卡开通回调
     * @param string $raw 回调请求原始信息
     * @return array
     * @throws Exception
     */
    public function openNotify($raw)
    {
        Yii::warning('平安银行回调：' . $raw, 'pingan');
        parse_str($raw, $post);
        $orig = $post['orig'];
        $sign = $post['sign'];

        // 解码
        $formSign = base64_decode($sign);
        $formOrig = base64_decode($orig);

        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }
        $formOrig = mb_convert_encoding($formOrig, 'UTF-8', 'GBK');
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 支付回调处理
     * @param string $raw 回调请求原始信息
     * @param string $orig 回调请求Orig信息
     * @param string $sign 回调请求Sign信息
     * @return array
     * @throws Exception
     */
    public function submitNotify($raw = '', $orig = '', $sign = '')
    {
        if (empty($orig)) {
            Yii::warning('平安银行回调：' . $raw, 'pingan');
            parse_str($raw, $post);
            $orig = $post['orig'];
            $sign = $post['sign'];
        } else {
            Yii::warning('平安银行回调：' . $orig, 'pingan');
            Yii::warning('平安银行回调：' . $sign, 'pingan');
        }

        // 解码
        $formSign = base64_decode($sign);
        $formOrig = base64_decode($orig);

        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }
        $formOrig = mb_convert_encoding($formOrig, 'UTF-8', 'GBK');
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 退款回调
     * @param string $raw 回调请求原始信息
     * @return array
     * @throws Exception
     */
    public function refundNotify($raw)
    {
        Yii::warning('平安银行回调：' . $raw, 'pingan');
        parse_str($raw, $post);
        $orig = $post['orig'];
        $sign = $post['sign'];

        // 解码
        $formSign = base64_decode($sign);
        $formOrig = base64_decode($orig);

        // 验证签名是否正确
        $result = $this->verify($formOrig, $formSign, Yii::$app->params['upload_path'] . $this->trustPayCertFile);
        if (!$result) {
            throw new Exception('签名验证失败。');
        }
        $formOrig = mb_convert_encoding($formOrig, 'UTF-8', 'GBK');
        $orig_data = $this->xml_to_array($formOrig);

        return $orig_data;
    }

    /**
     * 定时任务获取对账信息
     * @param string $date = null 对账单日期YYYYMMDD
     * @return string
     */
    public static function task_bank_reconciliation($date = null)
    {
        if (empty($date)) {
            $date = date('Ymd', time() - 86400);
        }

        Yii::warning('平安银行对账：' . $date, 'pingan');

        $pinganApi = new PinganApi();
        $result = $pinganApi->KH0003($date);

        Yii::warning(print_r($result, true), 'pingan');

        if (isset($result['errorCode']) && !empty($result['errorCode'])
            || isset($result['errorMsg']) && !empty($result['errorMsg'])
        ) {
            return $result['errorCode'] . ':' . $result['errorMsg'];
        }
        $trans = Yii::$app->db->beginTransaction();
        try {
            foreach ($result['body'] as $item) {
                $model = new BankReconciliationPingan();
                $model->setAttributes($item, false);
                if (!$model->save()) {
                    throw new Exception($model->errors);
                }
            }
            $trans->commit();
        } catch (Exception $e) {
            try {
                $trans->rollBack();
            } catch (Exception $e) {
            }
        }

        return '对账完成：' . $date;
    }

    /**
     * 组装HTML
     * @param string $gateway
     * @param array $parameter
     * @return string
     */
    private function showHtml($gateway, $parameter)
    {
        $html = '<form method="post" name="P_FORM" id="P_FORM" action="' . $gateway . '">';
        foreach ($parameter as $key => $val) {
            $html .= "<input type='hidden' name='$key' value='$val' />";
        }
        $html .= '</form>';
        return $html;
    }

    /**
     * 组装XML
     * @param array $data
     * @return string
     */
    private function array_to_xml($data)
    {
        $xml_data = '<kColl id="input" append="false">';
        foreach ($data as $key => $value) {
            $xml_data .= '<field id="' . $key . '" value="' . $value . '"/>';
        }
        $xml_data .= '</kColl>';

        return $xml_data;
    }

    /**
     * XML解析成数组
     * @param string $orig_xml
     * @return array
     */
    private function xml_to_array($orig_xml)
    {
        $xml = simplexml_load_string($orig_xml);
        $arr = json_decode(json_encode($xml), TRUE);

        $res = [];

        foreach ($arr ['field'] as $key => $row) {
            $res [$row ['@attributes'] ['id']] = $row ['@attributes'] ['value'];
            //array_push($res, $row['@attributes']['id']);
        }

        if (array_key_exists('iColl', $arr)) {
            $resBody = [];
            if (array_key_exists('kColl', $arr ['iColl'])) {
                // 如果多个对象，是在一个数组
                if (!array_key_exists('field', $arr ['iColl'] ['kColl'])) {
                    foreach ($arr ['iColl'] ['kColl'] as $key => $row) {
                        $coll = [];
                        foreach ($row ['field'] as $_key => $_row) {
                            $coll [$_row ['@attributes'] ['id']] = $_row ['@attributes'] ['value'];
                        }
                        array_push($resBody, $coll);
                    }
                } //如果单个对象，仅返回对象
                else {
                    $coll = [];
                    foreach ($arr ['iColl'] ['kColl'] ['field'] as $_key => $_row) {
                        $coll [$_row ['@attributes'] ['id']] = $_row ['@attributes'] ['value'];
                    }
                    array_push($resBody, $coll);
                }
                $res ['body'] = $resBody;
            } else { // 没有循环体，只有unionInfo，false
                foreach ($arr ['iColl'] as $key => $row) {
                    $res [$row ['id']] = $row ['append'];
                    // array_push($res, $row['@attributes']['id']);
                }
            }
        }
        return $res;
    }

    /**
     * 获取签名过后的原始数据orig和签名数据sign
     * @param string $merchantCertFile
     * @param string $data
     * @return array
     */
    private function _getOrigAndSing($merchantCertFile, $data)
    {
        $orig = $this->_base64_url_encode($data);
        $sign = $this->getSign($merchantCertFile, $data);

        return [$orig, $sign];
    }

    /**
     * @param string $orig
     * @param bool $sign
     * @param $tTrustPayCertFile
     * @return int
     */
    private function verify($orig, $sign, $tTrustPayCertFile)
    {
        $tSign = $this->hex2bin(trim($sign));
        $pem = "-----BEGIN CERTIFICATE-----\n" . chunk_split(base64_encode(file_get_contents($tTrustPayCertFile)), 64, "\n") . "-----END CERTIFICATE-----\n";
        try {
            $iTrustpayCertificate = openssl_x509_read($pem);
            $key = openssl_pkey_get_public($iTrustpayCertificate);
        } catch (\Exception $e) {
            $key = openssl_pkey_get_public($pem);
        }
        $r = openssl_verify(trim($orig), $tSign, $key, OPENSSL_ALGO_MD5);
        openssl_free_key($key);
        return $r;
    }

    private function hex2bin($hexdata)
    {
        $bindata = '';
        $length = strlen($hexdata);
        for ($i = 0; $i < $length; $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    /**
     * to base64 and url
     *
     * @param string $data
     * @return string
     */
    private function _base64_url_encode($data)
    {
        $data_base64 = base64_encode($data); // base64
        $data_gbk = iconv("UTF-8", "GBK", $data_base64); // utf-8 to gbk
        $data_url = urlencode($data_gbk); // url
        return $data_url;
    }

    private function _base64_url_decode($data)
    {
        $data = urldecode($data); // url
        $data_base64 = base64_decode($data); // base64
        return $data_base64;
    }

    private function getOrig($data)
    {
        $orig = $this->_base64_url_encode($data);
        return $orig;
    }

    /**
     * get sign
     *
     * @param string $merchantCertFile
     * @param string $data
     * @return string
     */
    private function getSign($merchantCertFile, $data)
    {
        $sign = $this->_getSign($merchantCertFile, $data);
        $sign = $this->_base64_url_encode($sign);
        return $sign;
    }

    /**
     * sign by open_ssl
     *
     * @param string $merchantCertFile
     * @param string $data
     * @return string
     */
    private function _getSign($merchantCertFile, $data)
    {
        $tCertificate = [];
        $pkey = '';
        if (openssl_pkcs12_read(file_get_contents($merchantCertFile), $tCertificate, $this->ssl_password)) {
            $pkey = openssl_pkey_get_private($tCertificate ['pkey']);
        }

        $signature = '';

        if (!openssl_sign($data, $signature, $pkey, OPENSSL_ALGO_MD5)) {
            exit ("Have a error!Please check!");
        }
        $sign = bin2hex($signature);
        return $sign;
    }

    private function curl($url, $parms)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $parms);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
