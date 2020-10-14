<?php

namespace app\modules\api\controllers;

use app\models\OrderDeliver;
use Yii;
use yii\web\Response;

class Kuaidi100Controller extends \app\controllers\BaseController
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
     * 快递100回调接口
     */
    public function actionNotify()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $order_deliver_id = $this->get('order_deliver_id');
        $param = $this->post('param');
        Yii::warning('快递100回调：' . $param);
        if (empty($order_deliver_id)) {
            return ['result' => false, 'returnCode' => -1, 'message' => '没有找到关联物流单号。'];
        }
        if (empty($param)) {
            return ['result' => false, 'returnCode' => -2, 'message' => '没有找到推送数据。'];
        }
        $json = json_decode($param, true);
        if (empty($json)) {
            return ['result' => false, 'returnCode' => -3, 'message' => '无法解析推送数据。'];
        }
        if (!isset($json['lastResult'])) {
            return ['result' => false, 'returnCode' => -4, 'message' => '没有找到跟踪结果。'];
        }
        $lastResult = $json['lastResult'];
        if (empty($lastResult)) {
            return ['result' => false, 'returnCode' => -4, 'message' => '没有找到跟踪结果。'];
        }
        if (!isset($lastResult['data'])) {
            return ['result' => false, 'returnCode' => -5, 'message' => '没有找到订单信息。'];
        }
        $trace_data = $lastResult['data'];
        $deliver = OrderDeliver::findOne($order_deliver_id);
        if (empty($deliver)) {
            return ['result' => false, 'returnCode' => -5, 'message' => '没有找到物流单信息。'];
        }
        $deliver->trace = json_encode($trace_data);
        $deliver->save(false);
        return [
            'result' => true,
            'returnCode' => 200,
            'message' => '成功'
        ];
    }
}
