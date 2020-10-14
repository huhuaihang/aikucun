<?php

namespace app\models;

use JPush\Client;
use Yii;
use yii\base\Model;

/**
 * 极光推送接口
 * Class JPushApi
 * @package app\models
 */
class JPushApi extends Model
{
    /**
     * @var string AppKey
     */
    private $appKey;
    /**
     * @var string MasterSecret
     */
    private $masterSecret;
    /**
     * @var string $logFile
     */
    private $logFile;
    /**
     * @var Client
     */
    private $client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appKey = System::getConfig('jpush_app_key');
        $this->masterSecret = System::getConfig('jpush_master_secret');
        $this->logFile = Yii::getAlias('@runtime/logs/jpush.log');
        $this->client = new Client($this->appKey, $this->masterSecret, $this->logFile);
        parent::init();
    }

    /**
     * 返回极光推送客户端实例
     * @return Client
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * 推送自定义消息
     * @param $jpush_id string 极光推送用户编号
     * @param $type string 类型
     * @param $title string 标题
     * @param $extra array 附加数据
     * @return array
     */
    public function pushMessage($jpush_id, $type, $title, $extra)
    {
        return $this->client()
            ->push()
            ->setPlatform('all')
            ->setNotificationAlert('云淘帮通知')
//            ->setNotificationAlert($type)
            ->addRegistrationId($jpush_id)
            ->message($type, ['title' => $title, 'content_type' => null, 'extras' => $extra])
            ->send();
    }
}
