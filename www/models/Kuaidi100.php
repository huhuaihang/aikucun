<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * 快递100接口
 * Class Kuaidi100
 * @package app\models
 */
class Kuaidi100 extends Model
{
    /**
     * 订阅推送
     * @param $order_deliver_id integer 订单物流编号
     * @param $express_code string 快递代号
     * @param $express_no string 快递单号
     * @return boolean
     */
    public static function poll($order_deliver_id, $express_code, $express_no)
    {
        $url = 'http://poll.kuaidi100.com/poll';
        $param = [
            'company' => $express_code,
            'number' => $express_no,
            'key' => System::getConfig('kuaidi100_key'),
            'parameters' => [
                'callbackurl' => System::getConfig('kuaidi100_notify_url') . '?order_deliver_id=' . $order_deliver_id,
            ],
        ];
        $post_data = 'schema=json&param=' . json_encode($param);
        $res = Util::post($url, $post_data);
        Yii::warning('快递100订阅：' . $post_data);
        Yii::warning('快递100订阅：' . $res);
        if (empty($res)) {
            return false;
        }
        $json = json_decode($res, true);
        if (empty($json)) {
            return false;
        }
        return $json['result'];
    }
}
