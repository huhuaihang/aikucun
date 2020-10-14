<?php

namespace app\modules\api\controllers;

use app\models\FinanceLog;
use app\models\Order;
use Yii;
use yii\base\Exception;
use yii\web\Response;

/**
 * 通联H5支付
 * Class AllInPayH5Controller
 * @package app\modules\api\controllers
 */
class AllInPayH5Controller extends \app\controllers\BaseController
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

        $status = ($post['payResult'] == '1') ? FinanceLog::STATUS_SUCCESS : FinanceLog::STATUS_FAIL;
        try {
            $r = FinanceLog::payNotify($post['orderNo'], round($post['payAmount'] / 100, 2),  $status, $raw);
            if ($r) {
                return 'success';
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return 'server_error';
    }

    /**
     * 回跳地址
     * @return Response
     */
    public function actionReturn()
    {
        $raw = file_get_contents('php://input');
        parse_str($raw, $post);
        $trade_no = $post['orderNo'];
        /** @var FinanceLog $finance_log */
        $finance_log = FinanceLog::find()->where(['trade_no' => $trade_no])->one();
        if ($finance_log->type == FinanceLog::TYPE_ORDER_PAY) {
            /** @var Order $order */
            $order = Order::find()->andWhere(['fid' => $finance_log->id])->one();
            return $this->redirect(['/h5/order/view', 'order_no' => $order->no]);
        }
        return $this->redirect(['/h5/user']);
    }
}
