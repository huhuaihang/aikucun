<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 商户消息
 * Class MerchantMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $mid 商户编号
 * @property integer $sid 系统消息编号
 * @property string $title 标题
 * @property string $content 内容
 * @property integer $time 添加时间
 * @property integer $status 状态
 */
class MerchantMessage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'title', 'content', 'time', 'status'], 'required'],
            ['title', 'string', 'max' => 128],
            ['content', 'safe'],
        ];
    }

    /**
     * 检查是否有新的消息
     * @param $mid integer 商户编号
     * @return bool
     */
    public static function checkNewMessage($mid)
    {
        $sid = SystemMessage::find()->max('id');
        if ($sid == 0) { // 没有新的系统消息
            return false;
        }
        $max_sid = MerchantMessage::find()->where(['mid' => $mid])->max('sid');
        $max_sid = intval($max_sid);
        if ($max_sid >= $sid) { // 系统消息已经全部接收到了
            return false;
        }
        foreach (SystemMessage::find()->where(['>', 'id', $max_sid])->each() as $system_message) {
            /** @var SystemMessage $system_message */
            $merchant_message = new MerchantMessage();
            $merchant_message->mid = $mid;
            $merchant_message->sid = $system_message->id;
            $merchant_message->title = $system_message->title;
            $merchant_message->content = $system_message->content;
            $merchant_message->time = $system_message->time;
            $merchant_message->status = SystemMessage::STATUS_UNREAD;
            $merchant_message->save();
        }
        return true;
    }
}
