<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Url;

/**
 * 通联支付支付宝接口
 * Class AllInPayAliApi
 * @package app\models
 */
class AllInPayAliApi extends Model
{
    /**
     * @var string 接口地址
     */
    private $gateway;
    /**
     * @var string 商户号
     */
    private $cusid;
    /**
     * @var string APPId
     */
    private $appid;
    /**
     * @var string KEY
     */
    private $key;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->gateway = YII_ENV === 'prod' ? 'https://vsp.allinpay.com' : 'https://vsp.allinpay.com';
        $this->cusid = System::getConfig('allinpay_ali_cusid');
        $this->appid = System::getConfig('allinpay_ali_appid');
        $this->key = System::getConfig('allinpay_ali_key');
        parent::init();
    }

    /**
     * 统一支付接口
     * @param $tradeNo
     * @param $orderMoney
     * @return array
     * @throws Exception
     */
    public function unitOrder($tradeNo, $orderMoney)
    {
        $data = [
            'cusid' => $this->cusid, // 商户号
            'appid' => $this->appid, // 应用ID
            'trxamt' => $orderMoney * 100, // 交易金额，单位为分
            'reqsn' => $tradeNo, // 商户交易号
            'paytype' => 'A01', // 交易方式
            'randomstr' => Util::randomStr(32, 7), // 随机字符串
            'validtime' => '30', // 订单有效期分钟
            'notify_url' => Url::to(['/api/all-in-pay-ali/notify'], true), // 交易结果通知地址
        ];
        $data['sign'] = $this->makeSign($data);
        $response = Util::post($this->gateway . '/apiweb/unitorder/pay', $data);
        Yii::warning($this->gateway . '/apiweb/unitorder/pay', 'allinpay_ali');
        Yii::warning(print_r($data, true), 'allinpay_ali');
        Yii::warning($response, 'allinpay_ali');
        if (empty($response)) {
            throw new Exception('支付通道没有返回值。');
        }
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new Exception('支付通道返回结果无法解析。');
        }
        return $json;
    }

    /**
     * 交易退款
     * @param $tradeNo string 原交易商户订单号
     * @param $refundTradeNo string 退款商户订单号
     * @param $refundMoney float 退款金额
     * @return array
     * @throws Exception
     */
    public function refund($tradeNo, $refundTradeNo, $refundMoney)
    {
        $data = [
            'cusid' => $this->cusid, // 商户号
            'appid' => $this->appid, // 应用ID
            'trxamt' => $refundMoney * 100, // 交易金额，单位为分
            'reqsn' => $refundTradeNo, // 商户交易号
            'oldreqsn' => $tradeNo, // 原交易订单号
            'randomstr' => Util::randomStr(32, 7), // 随机字符串
        ];
        $data['sign'] = $this->makeSign($data);
        $response = Util::post($this->gateway . '/apiweb/unitorder/refund', $data);
        Yii::warning($this->gateway . '/apiweb/unitorder/refund', 'allinpay_ali');
        Yii::warning(print_r($data, true), 'allinpay_ali');
        Yii::warning($response, 'allinpay_ali');
        if (empty($response)) {
            throw new Exception('支付通道没有返回值。');
        }
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new Exception('支付通道返回结果无法解析。');
        }
        return $json;
    }

    /**
     * 交易查询
     * @param $tradeNo string 商户交易号
     * @return array
     * @throws Exception
     */
    public function query($tradeNo)
    {
        $data = [
            'cusid' => $this->cusid, // 商户号
            'appid' => $this->appid, // 应用ID
            'reqsn' => $tradeNo, // 商户交易号
            'randomstr' => Util::randomStr(32, 7), // 随机字符串
        ];
        $data['sign'] = $this->makeSign($data);
        $response = Util::post($this->gateway . '/apiweb/unitorder/query', $data);
        Yii::warning($this->gateway . '/apiweb/unitorder/pay', 'allinpay_ali');
        Yii::warning(print_r($data, true), 'allinpay_ali');
        Yii::warning($response, 'allinpay_ali');
        if (empty($response)) {
            throw new Exception('支付通道没有返回值。');
        }
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new Exception('支付通道返回结果无法解析。');
        }
        return $json;
/*
字段ID	字段名称	取值	可空	最大长度	备注
retcode	返回码	SUCCESS/FAIL	否	8	此字段是通信标识，非交易结果，交易是否成功需要查看trxstatus来判断
retmsg	返回码说明		是	100
以下信息只有当retcode为SUCCESS时有返回
cusid	商户号	平台分配的商户号	否	15
appid	应用ID	平台分配的APPID	否	8
trxid	交易单号	平台的交易流水号	否	20
chnltrxid	支付渠道交易单号	支付宝平台的交易单号	是	50
reqsn	商户订单号	商户的交易订单号	否	32
trxcode	交易类型	交易类型	否	8	见3.2
trxamt	交易金额	单位为分	否	16
trxstatus	交易状态	交易的状态	否	4	见3.1
acct	支付平台用户标识	JS支付时使用
支付宝支付-用户user_id	是	32	如果为空,则默认填000000
fintime	交易完成时间	yyyyMMddHHmmss	是	14
randomstr	随机字符串	随机生成的字符串	否	32
errmsg	错误原因	失败的原因说明	是	100
sign	签名		否	32	详见1.4
 */
    }

    /**
     * 对账文件
     * @param $date string 交易日期20180101
     * @return string 对账文件下载地址
     * @throws Exception
     */
    public function trxfile($date)
    {
        $data = [
            'cusid' => $this->cusid, // 商户号
            'appid' => $this->appid, // 应用ID
            'date' => $date, // 交易日期 Ymd
            'randomstr' => Util::randomStr(32, 7), // 随机字符串
        ];
        $data['sign'] = $this->makeSign($data);
        $response = Util::post($this->gateway . '/apiweb/trxfile/get', $data);
        Yii::warning($this->gateway . '/apiweb/trxfile/get', 'allinpay_ali');
        Yii::warning(print_r($data, true), 'allinpay_ali');
        Yii::warning($response, 'allinpay_ali');
        if (empty($response)) {
            throw new Exception('支付通道没有返回值。');
        }
        $json = json_decode($response, true);
        if (empty($json)) {
            throw new Exception('支付通道返回结果无法解析。');
        }
        if ($json['retcode'] != 'SUCCESS') {
            throw new Exception($json['retmsg']);
        }
        return $json['url'];
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

        Yii::warning('通联支付宝对账：' . $date, 'allinpay_ali');

        try {
            $api = new AllInPayAliApi();
            $url = $api->trxfile($date);
            Yii::warning($url, 'allinpay_ali');

            $filename = tempnam(sys_get_temp_dir(), '');
            file_put_contents($filename, file_get_contents($url));
            $zip = zip_open($filename);
            while ($dir_resource = zip_read($zip)) {
                if (!zip_entry_open($zip, $dir_resource)) {
                    continue;
                }
                $size = zip_entry_filesize($dir_resource);
                $data = zip_entry_read($dir_resource, $size);
                $data = mb_convert_encoding($data, 'UTF-8', 'GBK');
                Yii::warning($data, 'allinpay_ali');
                $item_list = preg_split('/\r|\n/', $data, -1, PREG_SPLIT_NO_EMPTY);
                if (empty($item_list)) {
                    continue;
                }
                for ($i = 1; $i < count($item_list); $i++) { // 去掉第一行标题
                    $item = $item_list[$i];
                    $col_list = explode(',', $item);
                    $model = new BankReconciliationAllinpayAli();
                    $model->mch_id = $col_list[0]; // 商户号
                    $model->store_name = $col_list[1]; // 门店名称
                    $model->term_no = $col_list[2]; // 终端号
                    $model->trade_time = $col_list[3]; // 交易时间
                    $model->trade_type = $col_list[4]; // 交易类型
                    $model->trade_batch_no = $col_list[5]; // 交易批次号
                    $model->proof_no = $col_list[6]; // 凭证号
                    $model->ref_no = $col_list[7]; // 参考号
                    $model->card_no = $col_list[8]; // 卡号
                    $model->card_type = $col_list[9]; // 卡类别
                    $model->card_org_no = $col_list[10]; // 发卡行机构代码
                    $model->card_org_name = $col_list[11]; // 发卡行名称
                    $model->trade_money = $col_list[12]; // 交易金额
                    $model->tax_money = $col_list[13]; // 手续费
                    $model->trade_date = $col_list[14]; // 交易日期
                    $model->order_no = $col_list[16]; // 订单号
                    $model->mch_remark = $col_list[17]; // 商户备注
                    $model->app_no = $col_list[18]; // 对接应用号
                    $model->save();
                }
                zip_entry_close($dir_resource);
            }
            zip_close($zip);
            return '对账完成：' . $date;
        } catch (\Exception $e) {
            return '对账错误：' . $date . '：' . $e->getMessage();
        }
    }

    /**
     * 签名
     * @param $data array
     * @return string
     */
    public function makeSign($data)
    {
        $data['key'] = $this->key;
        ksort($data);
        array_walk($data, function (&$v, $k) {
            $v = $k . '=' . $v;
        });
        return strtoupper(md5(implode('&', $data)));
    }
}
