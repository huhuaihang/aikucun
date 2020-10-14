<?php

namespace app\models;

use app\modules\api\models\ApiException;
use Yii;
use yii\db\ActiveRecord;

/**
 * 接口客户端
 * Class ApiClient
 * @package app\models
 *
 * @property integer $id PK
 * @property string $name 名称
 * @property string $app_id AppId
 * @property string $app_secret AppSecret
 * @property integer $status 状态
 * @property integer $create_time 创建时间
 */
class ApiClient extends ActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_STOP = 9;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'app_id', 'app_secret', 'status'], 'required'],
            [['name', 'app_id', 'app_secret'], 'string', 'max' => 32],
            ['status', 'integer'],
        ];
    }

    /**
     * 根据AppId获取客户端信息
     * @param $appId string AppId
     * @return ApiClient
     */
    public static function findByAppId($appId)
    {
        /** @var ApiClient $client */
        $client = ApiClient::find()->andWhere(['app_id' => $appId])->one();
        return $client;
    }

    /**
     * 验证签名
     * @param $params array 提交参数
     * @return bool | array
     * @throws ApiException
     */
    public function checkSign($params)
    {
        if (empty($params['nonce'])) {
            throw new ApiException('PARAM', '没有找到随机字符串参数。');
        }
        if (empty($params['timestamp'])) {
            throw new ApiException('PARAM', '没有找到时间戳参数。');
        }
        if (empty($params['sign'])) {
            throw new ApiException('PARAM', '没有找到签名参数。');
        }
        // 超时验证
        $_timestamp = time();
        if (Yii::$app->params['api_timeout'] > 0) {
            if (
                ($params['timestamp'] > $_timestamp && $params['timestamp'] > $_timestamp + Yii::$app->params['api_timeout'])
                ||
                ($params['timestamp'] < $_timestamp && $params['timestamp'] < $_timestamp - Yii::$app->params['api_timeout'])
            ) {
                throw new ApiException('TIME', '时间戳超时，请检查客户端时间。');
            }
        }
        $sign = $params['sign'];
        unset($params['sign']);
        ksort($params);
        $_tmp_str = '';
        foreach ($params as $v) {
            if (is_array($v)) {
                $v = implode('', $v);
            }
            if ($v === '') {
                continue;
            }
            $_tmp_str .= $v;
        }
        $_tmp_str .= $this->app_secret;
        $_sign = md5($_tmp_str);

        if ($_sign !== strtolower($sign)) {
            throw new ApiException('SIGN', '签名错误。');
        }
        // 调用重复验证
        if (!empty(Yii::$app->cache->get('api_sign_' . $sign))) {
            Yii::$app->cache->set('api_sign_' . $sign, time(), Yii::$app->params['api_sign_repeat_timeout'] * 10);
            throw new ApiException('SIGN_REPEAT', '签名已经使用过，请重新生成新的签名。');
        }
        Yii::$app->cache->set('api_sign_' . $sign, $_timestamp, Yii::$app->params['api_sign_repeat_timeout']);
        return true;
    }
}
