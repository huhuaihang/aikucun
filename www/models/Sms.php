<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * 短信
 * Class Sms
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $u_type 用户类型
 * @property integer $uid 用户编号
 * @property integer $type 类型
 * @property string $msgid 流水号
 * @property string $mobile 手机号
 * @property string $content 内容
 * @property integer $send_time 发送时间
 * @property integer $status 状态
 * @property string $remark 备注
 */
class Sms extends ActiveRecord
{
    const U_TYPE_MANAGER = 1;
    const U_TYPE_AGENT = 2;
    const U_TYPE_MERCHANT = 3;
    const U_TYPE_USER = 4;

    const TYPE_BIND_MOBILE = 1; // 绑定手机号码
    const TYPE_FORGOT_PASSWORD = 2; // 忘记密码
    const TYPE_REGISTER = 3; // 用户注册
    const TYPE_PAYMENT_PASSWORD = 4; // 设置支付密码
    const TYPE_AGENT_JOIN = 5; // 代理商入驻申请
    const TYPE_MERCHANT_JOIN = 6; // 商户入驻申请
    const TYPE_ALI_REGISTER = 7; // 用户注册 阿里云发送
    const TYPE_NOTICE = 8; // 通知信息
    const TYPE_ACTIVE_NOTICE = 9; // 激活通知
    const TYPE_SEND_NOTICE = 10; // 发货通知

    const STATUS_WAIT = 1;
    const STATUS_PAUSED = 2;
    const STATUS_SEND = 3;
    const STATUS_SEND_FAIL = 4;
    const STATUS_SENT = 5;
    const STATUS_SENT_ERROR = 6;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['u_type', 'uid', 'type', 'send_time', 'status'], 'integer'],
            [['type', 'mobile', 'content'], 'required'],
            [['msgid', 'mobile'], 'string', 'max' => 32],
            [['content', 'remark'], 'safe'],
        ];
    }

    /**
     * 发送短信验证码
     * @param integer $u_type 用户类型
     * @param integer $uid 用户编号
     * @param string $mobile 手机号码
     * @param integer $type app\models\Sms::TYPE_REG 验证码类型
     * @param string $code ='' 验证码内容，传入空值时自动生成
     * @return true|string 返回字符串时表示失败原因
     */
    public static function sendCode($u_type, $uid, $mobile, $type, $code = '')
    {
        $cache = Yii::$app->cache->get('sms_code_' . $mobile);
        if (!empty($cache)) {
            $cache = json_decode($cache, true);
            if ($type == $cache['type'] && $cache['time'] > time() - 60) {
                return '发送太频繁，请稍等再试。';
            }
        }
        if (empty($code)) {
            //$code = YII_ENV === 'prod' ? Util::randomStr(4) : '1234';
            $code = Util::randomStr(4);
        }
        switch ($type) {
            case Sms::TYPE_BIND_MOBILE:
            case Sms::TYPE_FORGOT_PASSWORD:
            case Sms::TYPE_REGISTER:
            case Sms::TYPE_ALI_REGISTER:
            $content =  $code;
            default:
                //$content = '您的验证码是' . $code . '。请在页面中提交验证码完成验证。';
                $content = $code;
        }
        $result = Sms::send($u_type, $uid, $type, $mobile, $content);
        if ($result !== true) {
            return $result;
        }
        Yii::$app->cache->set('sms_code_' . $mobile, json_encode(['type' => $type, 'code' => $code, 'time' => time()]), 600);
        return true;
    }

    /**
     * 验证短信验证码
     * @param string $mobile 手机号码
     * @param integer $type 短信类型
     * @param string $code 验证码
     * @return boolean
     */
    public static function checkCode($mobile, $type, $code)
    {
        $_code = Yii::$app->cache->get('sms_code_' . $mobile);
        if (empty($_code)) {
            // 没有找到此手机号码发送的验证码
            return false;
        }
        $_code = json_decode($_code, true);
        if ($type != $_code['type'] // 类型不符
            || strtoupper($code) != strtoupper($_code['code']) // 验证码不符
            || time() - 10 * 60 > $_code['time'] // 超时
        ) {
            return false;
        }
        Yii::$app->cache->delete('sms_code_' . $mobile);
        return true;
    }

    /**
     * 发送短信
     * @param integer $u_type 用户类型
     * @param integer $uid 用户编号
     * @param integer $type 短信类型
     * @param string $mobile 手机号码
     * @param string $content 短信内容
     * @return true|string 返回字符串时表示失败原因
     */
    public static function send($u_type, $uid, $type, $mobile, $content)
    {
        $sms = new Sms();
        $sms->u_type = $u_type;
        $sms->uid = $uid;
        $sms->type = $type;
        $sms->mobile = $mobile;
        $sms->content = $content;
        $sms->send_time = time();
        $sms->status = Sms::STATUS_WAIT;
        $sms->remark = '';
        if (!$sms->save()) {
            foreach ($sms->errors as $errors) {
                return $errors[0];
            }
        }
//        if (YII_ENV !== 'prod') {
//            $sms->status = Sms::STATUS_SENT;
//            $sms->remark = 'DEBUG';
//            $sms->save();
//            return true;
//        }
        return Sms::sendAliSms($sms);
        //return Sms::send_ali_sms_Impl($sms);
        //return Sms::send_Impl($sms);
    }

    /**
     * 实际短信发送程序
     * http://www.ihuyi.com
     * @param \app\models\Sms $sms
     * @return true|string 返回字符串时表示失败原因
     */
    private static function send_Impl_bak($sms)
    {
        $gateway = 'http://dxcxyy.ykqxt.com:8080/v2sms.aspx';
        $username = 'yunshangzhijia';
        $password = '123456';
        $time = time();
        $sign = md5($username.$password.$time);
        $sign1 = '【惠民超市】';
        $content = $sign1 . $sms->content;

        $post_data = [
            'action' => 'send',
            'userid' => '117',
            'timestamp' => $time,//$sms->mobile,
            'sign' => $sign,
            'mobile' => $sms->mobile,
            'content' => $content,
            'sendTime' => '',
            'extno' => '',
        ];
        $result = Util::post($gateway, $post_data);
        Yii::warning($gateway, 'sms');
        Yii::warning($sign, 'sms');
        Yii::warning(print_r($post_data, true), 'sms');
        Yii::warning($result, 'sms');
        try {
            $result = (array) simplexml_load_string($result);
            if ($result['returnstatus'] == 'Success') {
                $sms->status = Sms::STATUS_SEND;
                return $sms->save();
            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        $sms->status = Sms::STATUS_SEND_FAIL;
        $sms->remark = $result;
        $sms->save();
        return $result;
    }

    private static function send_Impl($sms)
    {
        $gateway = System::getConfig('sms_gateway_url');
        $username = System::getConfig('sms_appid');
        $password = System::getConfig('sms_apikey');
        $sign = '【惠民超市】';
        $content = $sms->content . urldecode($sign);
        $istimer = 'false'; // 是否为定时发送
        $timerset = date('Y-m-d H:i:s');
        $pipeid = null; // 通道ID
        $identifyNum = $sms->msgid; // 唯一流水码，用于后续查询短信发送情况
        $identifyNum = time(); // 唯一流水码，用于后续查询短信发送情况

        $post_data = [
            'username' => $username,
            'password' => $password,
            'phone' => $sms->mobile,
            'content' => $content,
            'pipeid' => $pipeid,
            'istimer' => $istimer,
            'timerset' => $timerset,
            'identifyNum' => $identifyNum,
        ];
        $result = Util::post($gateway, $post_data);
        Yii::warning($gateway, 'sms');
        Yii::warning(print_r($post_data, true), 'sms');
        Yii::warning($result, 'sms');
        try {
            $result = (array) simplexml_load_string($result);
            if ($result['rstCode'] > 0) {
                $sms->status = Sms::STATUS_SEND;
                return $sms->save();
            }
            if ($result['rstCode'] == -10) {
                $sms->status = Sms::STATUS_SEND_FAIL;
                $sms->remark = '服务器内部错误发送失败';
                $sms->save();
                return '发送失败请稍后再试';

            }
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }
        $sms->status = Sms::STATUS_SEND_FAIL;
        $sms->remark = $result;
        $sms->save();
        return $result;
    }

    /**
     * 阿里轻量级大鱼短信
     * @param $sms
     * @return bool
     */
    public static function  sendAliSms($sms){

        $params = array ();
        //阿里云的AccessKey
        $accessKeyId = System::getConfig('ali_sms_app_key');

        //阿里云的Access Key Secret
        $accessKeySecret = System::getConfig('ali_sms_app_secret');

        //要发送的手机号
        $params["PhoneNumbers"] = $sms->mobile;

        //签名，第三步申请得到
        $params["SignName"] = '云淘帮';

        $tem_id = [
            Sms::TYPE_BIND_MOBILE => 'SMS_155330018',
            Sms::TYPE_FORGOT_PASSWORD => 'SMS_155330018',
            Sms::TYPE_REGISTER => 'SMS_155330018',
            Sms::TYPE_PAYMENT_PASSWORD => 'SMS_152760370',
            Sms::TYPE_ALI_REGISTER => 'SMS_155330018',
            Sms::TYPE_ACTIVE_NOTICE => 'SMS_173405355',
//            Sms::TYPE_SEND_NOTICE => 'SMS_173425201',
            Sms::TYPE_SEND_NOTICE => 'SMS_175241424',
        ][$sms->type];
        //模板code，第三步申请得到
        $params["TemplateCode"] = $tem_id;//'SMS_155330018';//$tem_id;

        //模板的参数，注意code这个键需要和模板的占位符一致
        $params['TemplateParam'] = Array (
            "code" => $sms->content
        );
        if ($sms->type == Sms::TYPE_ACTIVE_NOTICE) {
            $params['TemplateParam'] = Array (
                "name" => $sms->content
            );
        }
        if ($sms->type == Sms::TYPE_SEND_NOTICE) {
            $params['TemplateParam'] = Array (
                "title" => $sms->content
            );
        }


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        try{
            // 此处可能会抛出异常，注意catch
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            // fixme 选填: 启用https
            // ,true
            );
            $res=array('errCode'=>0,'msg'=>'ok');
            if($content->Message!='OK'){
                $res['errCode']=1;
                $res['msg']= $content->Message;

            }
            $sms->status = Sms::STATUS_SEND;
            $sms->save();
            return true;
            //echo json_encode($res);
        }catch(\Exception $e){
            $sms->status = Sms::STATUS_SEND_FAIL;
            $sms->remark = $e->getMessage();
            $sms->save();
            return false;
            //echo '短信接口请求错误';exit;
        }

    }


    /**
     * 阿里大鱼短信发送
     * @param \app\models\Sms $sms
     * @return array|true
     */
    private static function send_ali_sms_Impl($sms)
    {
        $ali_sms_app_key = System::getConfig('ali_sms_app_key');
        $ali_sms_app_secret = System::getConfig('ali_sms_app_secret');
        $smsObj = new \saviorlv\aliyun\AliSms();
        $smsObj->accessKeyId = $ali_sms_app_key;
        $smsObj->accessKeySecret = $ali_sms_app_secret;
        $tem_id = [
            Sms::TYPE_BIND_MOBILE => 'SMS_152760370',
            Sms::TYPE_FORGOT_PASSWORD => 'SMS_152760370',
            Sms::TYPE_REGISTER => 'SMS_155330018',
            Sms::TYPE_PAYMENT_PASSWORD => 'SMS_152760370',
            Sms::TYPE_ALI_REGISTER => 'SMS_155330018',
        ][$sms->type];
        $send = $smsObj->sendSms("云淘帮", // 短信签名
            $tem_id, // 短信模板编号
            "$sms->mobile", // 短信接收者
            [  // 短信模板中字段的值
                "code" => $sms->content,
                // "product" => "律宝盒短信"
            ],
            "");
        $send = json_decode($send);
        if ($send->code == '200') {
            $sms->status = Sms::STATUS_SEND;
            $sms->save();
            return true;
        } else {
            $sms->status = Sms::STATUS_SEND_FAIL;
            $sms->remark = $send;
            $sms->save();
            return false;
        }
    }

    /**
     * 验证MOB短信验证码
     * @param $mobile
     * @param $code
     * @return string
     */
    public static function checkMobCode($mobile, $code)
    {
        // 配置项
        $api = 'https://webapi.sms.mob.com';
        $app_key = '241314f99db55';
        $post_data = [
            'appkey' => $app_key,
            'phone' => $mobile,
            'zone' => '86',
            'code' => $code,
        ];
        // 发送验证码
        $result = self::postRequest( $api . '/sms/verify', $post_data);
        Yii::warning(print_r($post_data, true), 'sms');
        Yii::warning($result, 'sms');
        $result = json_decode($result, true);
        if ($result['status'] == 200) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 发起一个post请求到指定接口
     *
     * @param string $api 请求的接口
     * @param array $params post参数
     * @param int $timeout 超时时间
     * @return string 请求结果
     */
    private static function postRequest( $api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) );
        // 发送数据
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }
}
