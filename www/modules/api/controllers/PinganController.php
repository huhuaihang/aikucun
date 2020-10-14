<?php

namespace app\modules\api\controllers;

use app\models\FinanceLog;
use app\models\PinganApi;
use app\modules\api\models\ErrorCode;
use Yii;
use yii\base\Exception;

/**
 * 平安银行
 * Class PinganController
 * @package app\modules\api\controllers
 */
class PinganController extends \app\controllers\BaseController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * 发送短信AJAX接口
     * @return array
     */
    public function actionSendSms()
    {
        $order_id = $this->get('order_id');
        $money = $this->get('money');
        $customer_id = $this->get('customer_id');
        $open_id = $this->get('open_id');

        $pingan_api = new PinganApi();
        try {
            $result = $pingan_api->UnionAPI_SSMS($order_id, $money, $customer_id, $open_id);
            return [
                'orig' => $result
            ];
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 后台支付AJAX接口
     * @return array
     */
    public function actionPay()
    {
        $order_id = $this->get('order_id');
        $money = $this->get('money');
        $name = $this->get('name');
        $pay_date = $this->get('pay_date');
        $remark = $this->get('remark');
        $customer_id = $this->get('customer_id');
        $open_id = $this->get('open_id');
        $verify_code = $this->get('verify_code');

        $pingan_api = new PinganApi();
        try {
            $orig = $pingan_api->UnionAPI_Submit($order_id, $money, $name, $pay_date, $remark, $customer_id, $open_id, $verify_code);
            return [
                'orig' => $orig
            ];
        } catch (Exception $e) {
            return [
                'error_code' => ErrorCode::SERVER,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 回调地址
     * @throws Exception
     */
    public function actionNotify()
    {
        $raw = file_get_contents('php://input');
        Yii::error($raw, '/api/pingan/notify');

        $pingan_api = new PinganApi();
        switch ($this->get('type')) {
            case 'open':
                $pingan_api->openNotify($raw);
                // 前台通过跳转继续业务逻辑，此处不再处理
                break;
            case 'submit':
                $orig = $pingan_api->submitNotify($raw);
                $order_no = $orig['orderId'];
                FinanceLog::payNotify($order_no, $orig['amount'], $orig['status'] == '01' ? FinanceLog::STATUS_SUCCESS : FinanceLog::STATUS_FAIL, $raw);
                break;
            case 'refund':
                try {
                    $orig = $pingan_api->refundNotify($raw);
                    if (empty($orig)) {
                        break;
                    }
                    if (!empty($orig['errorCode'])) {
                        break;
                    }
                    if ($orig['status'] != '01') {
                        break;
                    }
                    FinanceLog::payNotify($orig['refundNo'], $orig['refundAmt'], FinanceLog::STATUS_SUCCESS, $raw);
                } catch (Exception $e) {
                }
                break;
            default:
        }
    }

    /**
     * 返回地址
     */
    public function actionReturn()
    {
        return $this->redirect($this->get('return_url'));
    }
}
