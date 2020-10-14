<?php

namespace app\modules\api\controllers;

use app\models\AllInPayAliApi;
use app\models\FinanceLog;
use Yii;
use yii\base\Exception;

/**
 * 通联支付宝支付
 * Class AllInPayAliController
 * @package app\modules\api\controllers
 */
class AllInPayAliController extends \app\controllers\BaseController
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
     * 回调地址
     * @return string
     */
    public function actionNotify()
    {
        $raw = file_get_contents('php://input');

        Yii::warning($raw, 'allinpay_ali');

        parse_str($raw, $post);
        $signMsg = $post['sign'];
        unset($post['sign']);
        $api = new AllInPayAliApi();
        $_sign = $api->makeSign($post);
        if ($signMsg != $_sign) {
            Yii::error('通联支付宝支付异步通知签名验证失败：' . $raw);
            return '签名错误';
        }

        $status = $post['trxstatus'] == '0000' ? FinanceLog::STATUS_SUCCESS : FinanceLog::STATUS_FAIL;
        try {
            $r = FinanceLog::payNotify($post['cusorderid'], round($post['trxamt'] / 100, 2),  $status, $raw);
            if ($r) {
                return 'success';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return 'server_error';
    }
}
