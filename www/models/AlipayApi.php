<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\Model;

/**
 * 支付宝接口
 * Class AlipayApi
 * @package app\models
 */
class AlipayApi extends Model
{
    /**
     * @var \AopClient 支付宝SDK
     */
    private $aop;

    /**
     * @inheritdoc
     */
    public function init()
    {
        include_once Yii::getAlias('@app/components/alipay/AopSdk.php');
        $this->aop = new \AopClient();
        $this->aop->appId = System::getConfig('alipay_app_id');
        $this->aop->rsaPrivateKey = System::getConfig('alipay_private_key');
        $this->aop->alipayrsaPublicKey = System::getConfig('alipay_public_key');
        parent::init();
    }

    /**
     * 手机网站支付
     * @param $subject string 订单标题
     * @param $out_trade_no string 商户订单号
     * @param $total_amount float 金额
     * @param $return_url string 支付完成返回页面
     * @return string
     * @throws Exception
     */
    public function AlipayTradeWapPay($subject, $out_trade_no, $total_amount, $return_url)
    {
        $request = new \AlipayTradeWapPayRequest();
        $request->setBizContent(json_encode([
            'body' => $subject,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '1d',
            'total_amount' => $total_amount,
            'product_code' => 'QUICK_WAP_WAY',
        ]));
        $request->setNotifyUrl(System::getConfig('alipay_notify_url'));
        $request->setReturnUrl($return_url);
        try {
            $form = $this->aop->pageExecute($request);
            return $form;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 手机App支付
     * @param $subject string 订单标题
     * @param $body string 商品内容
     * @param $out_trade_no string 商户订单号
     * @param $total_amount float 金额
     * @return string
     */
    public function AlipayTradeAppPay($subject, $body, $out_trade_no, $total_amount)
    {
        $request = new \AlipayTradeAppPayRequest();
        $request->setBizContent(json_encode([
            'body' => $body,
            'subject' => $subject,
            'out_trade_no' => $out_trade_no,
            'timeout_express' => '1d',
            'total_amount' => $total_amount,
            'product_code' => 'QUICK_MSECURITY_PAY',
        ]));
        $request->setNotifyUrl(System::getConfig('alipay_notify_url'));
        return $this->aop->sdkExecute($request);
    }

    /**
     * 交易查询接口
     * @param string $out_trade_no 支付时传入的商户订单号，与trade_no必填一个
     * @param string $trade_no 支付时返回的支付宝交易号，与out_trade_no必填一个
     * @return \stdClass
     * @throws \Exception
     */
    public function AlipayTradeQuery($out_trade_no, $trade_no = '')
    {
        $request = new \AlipayTradeQueryRequest();
        $request->setBizContent(json_encode([
            'out_trade_no' => $out_trade_no,
            'trade_no' => $trade_no,
        ]));
        /** @var \stdClass $response */
        $response = $this->aop->execute($request);
        Yii::warning(json_encode($response), 'alipay');
        return $response->alipay_trade_query_response;
    }

    /**
     * 交易退款接口
     * @param string $out_trade_no 支付时传入的商户订单号，与trade_no必填一个
     * @param string $trade_no 支付时返回的支付宝交易号，与out_trade_no必填一个
     * @param string $out_request_no 本次退款请求流水号，部分退款时必传
     * @param float $refund_amount 本次退款金额
     * @return \stdClass
     * (
     *     [code] => 10000
     *     [msg] => Success
     *     [buyer_logon_id] => a76***@gmail.com
     *     [buyer_user_id] => 2088002636792212
     *     [fund_change] => Y
     *     [gmt_refund_pay] => 2017-10-07 13:56:24
     *     [out_trade_no] => 201710071349051
     *     [refund_fee] => 1.00
     *     [send_back_fee] => 0.00
     *     [trade_no] => 2017100721001004210537543005
     * )
     * @throws \Exception
     */
    public function AlipayTradeRefund($out_trade_no, $trade_no, $out_request_no, $refund_amount)
    {
        $request = new \AlipayTradeRefundRequest();
        $request->setBizContent(json_encode([
            'out_trade_no' => $out_trade_no,
            'trade_no' => $trade_no,
            'out_request_no' => $out_request_no,
            'refund_amount' => $refund_amount,
        ]));
        /** @var \stdClass $response */
        $response = $this->aop->execute($request);
        return $response->alipay_trade_refund_response;
    }

    /**
     * 查询对账单下载地址接口
     * @param string $bill_type 固定传入trade
     * @param string $bill_date 需要下载的账单日期，最晚是当前日期的前一天
     * @return \stdClass
     * @throws \Exception
     */
    public function AlipayDataServiceBillDownloadUrlQuery($bill_type = 'trade', $bill_date = '')
    {
        if (empty($bill_date)) {
            $bill_date = date('Y-m-d', time() - 86400);
        }
        $request = new \AlipayDataDataserviceBillDownloadurlQueryRequest();
        $request->setBizContent(json_encode([
            'bill_type' => $bill_type,
            'bill_date' => $bill_date,
        ]));
        return $this->aop->execute($request);
    }

    /**
     * 定时任务获取对账信息
     * @param string $date = null 对账单日期YYYYMMDD
     * @return string
     * @throws \Exception
     */
    public static function task_bank_reconciliation($date = null)
    {
        if (empty($date)) {
            $date = date('Y-m-d', time() - 86400);
        }

        Yii::warning('支付宝对账：' . $date, 'alipay');

        $alipayApi = new AlipayApi();
        $r = $alipayApi->AlipayDataServiceBillDownloadUrlQuery('trade', $date);
        Yii::warning(print_r($r, true), 'alipay');
        $responseNode = "alipay_data_dataservice_bill_downloadurl_query_response";
        $resultCode = $r->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            $down_url = $r->$responseNode->bill_download_url;
            $url = parse_url($down_url);
            $url = Util::convertUrlQuery($url['query']);
            $file_path = tempnam('','csv');
            $file = Util::download($down_url, $file_path.$url['downloadFileName']);
            $result = $alipayApi->get_zip_originalsize($file, $file_path);
            unlink($file);
            unlink($file_path);
            if ($result['result'] == 'error') {
                Yii::warning(print_r($result['message'], true), 'alipay');
                return '对账失败:' . $date;
            }else{
                return '对账完成：' . $date;
            }
        } else {
            Yii::warning(print_r($r->$responseNode->code.':'.$r->$responseNode->msg, true), 'alipay');
            return $r->$responseNode->code.':'.$r->$responseNode->msg;
        }
    }

    /**
     * 获取 zip压缩包
     * @param $filename string 压缩包名
     * @param $path string 路径
     * @return array
     */
    public static function get_zip_originalsize($filename, $path)
    {
        try {
            //先判断待解压的文件是否存在
            if (!file_exists($filename)) {
                Yii::warning("文件 $filename 不存在！", 'alipay');
                throw new Exception("文件 $filename 不存在！");
            }
            //打开压缩包
            $resource = zip_open($filename);
            $i = 0;
            //遍历读取压缩包里面的一个个文件
            while ($dir_resource = zip_read($resource)) {
                //如果能打开则继续
                if (!zip_entry_open($resource, $dir_resource)) {
                    $i++;
                    continue;
                }
                zip_entry_open($resource, $dir_resource);
                //获取当前项目的名称,即压缩包里面当前对应的文件名
                $file_name = $path . zip_entry_name($dir_resource);
                //以最后一个“/”分割,再用字符串截取出路径部分
                $file_path = substr($file_name, 0, strrpos($file_name, "/"));
                //如果路径不存在，则创建一个目录，true表示可以创建多级目录
                if (!is_dir($file_path)) {
                    mkdir($file_path, 0777, true);
                }
                //如果不是目录，则写入文件
                if (!is_dir($file_name)) {
                    //读取这个文件
                    $file_size = zip_entry_filesize($dir_resource);
                    $file_content = zip_entry_read($dir_resource, $file_size);
                    file_put_contents($i . '.csv', $file_content);
                    $handle = @fopen($i . '.csv', "r");
                    while (!feof($handle)) {
                        $buffer = fgets($handle, 4096);
                        Yii::warning(print_r($buffer, true), 'alipay');
                        if (!empty($buffer) && preg_match("/^\d*$/", $buffer[0])) {
                            $row = explode(',', $buffer);
                            $alipay = new BankReconciliationAlipay();
                            $index = 0;
                            foreach ($alipay->attributes as $key => $val) {
                                if ($key != 'id') {
                                    $alipay[$key] = iconv('GBK', 'UTF-8', $row[$index]);
                                    $index++;
                                }
                            }
                            $alipay->save();
                            if (!$alipay->save()) {
                                Yii::warning(print_r($alipay->errors, true), 'alipay');
                            }
                        }
                    }
                    fclose($handle);
                    unlink($i . '.csv');
                }
                $i++;
                //关闭当前
                zip_entry_close($dir_resource);
            }
            //关闭压缩包
            zip_close($resource);
            return ['result' => 'success'];
        } catch (Exception $e) {
            Yii::warning(print_r($e, true), 'alipay');
            return ['result' => 'error' , 'message' => $e];
        }
    }
}
