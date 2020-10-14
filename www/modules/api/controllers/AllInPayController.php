<?php

namespace app\modules\api\controllers;

use app\models\AllInPayApi;
use app\models\FinanceLog;
use app\models\Order;
use Yii;

/**
 * 通联支付
 * Class AllInPayController
 * @package app\modules\api\controllers
 */
class AllInPayController extends \app\controllers\BaseController
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
     * @throws \yii\base\Exception
     */
    public function actionNotify()
    {
        $raw = file_get_contents('php://input');

        Yii::warning($raw, 'allinpay');

        parse_str($raw, $post);
        $signMsg = $post['signMsg'];
        unset($post['signMsg']);
        $data = [
            'merchantId' => $post['merchantId'],
            'version' => $post['version'],
            'language' => $post['language'],
            'signType' => $post['signType'],
            'payType' => $post['payType'],
            'issuerId' => $post['issuerId'],
            'paymentOrderId' => $post['paymentOrderId'],
            'orderNo' => $post['orderNo'],
            'orderDatetime' => $post['orderDatetime'],
            'orderAmount' => $post['orderAmount'],
            'payDatetime' => $post['payDatetime'],
            'payAmount' => $post['payAmount'],
            'ext1' => $post['ext1'],
            'ext2' => $post['ext2'],
            'payResult' => $post['payResult'],
            'errorCode' => $post['errorCode'],
            'returnDatetime' => $post['returnDatetime'],
        ];
        $api = new AllInPayApi();
        $_sign = $api->makeSign($data);
        if ($signMsg != $_sign) {
            Yii::error('通联支付异步通知签名验证失败：' . $raw);
            return '签名错误';
        }

        if ($post['payResult'] != 1) {
            return '支付失败';
        }
        return FinanceLog::payNotify($post['orderNo'], round($post['payAmount'] / 100, 2),  FinanceLog::STATUS_SUCCESS, $raw);
    }

    /**
     * 返回地址
     * @return \yii\web\Response|string
     */
    public function actionReturn()
    {
        /** @var FinanceLog $financeLog */
        $financeLog = FinanceLog::find()->andWhere(['trade_no' => $this->get('trade_no')])->one();
        switch ($financeLog->type) {
            case FinanceLog::TYPE_ORDER_PAY:
                /** @var Order $order */
                $order = Order::find()->where(['fid' => $financeLog->id])->one();
                $type = $this->get('type', 'web');
                if ($type == 'web') { // 页面调用，转到订单详情页面
                    return $this->redirect(['/h5/order/view', 'order_no' => $order->no, 'check_pay' => 1]);
                } elseif ($type == 'app') { // APP调用，脚本通知APP结果
                    return '<!-- APP定时检查订单支付结果，显示订单详情界面 -->';
                }
        }
        return $this->redirect(['/']);
    }
}
