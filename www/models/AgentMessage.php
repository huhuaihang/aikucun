<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 代理商消息
 * Class AgentMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $aid 代理商编号
 * @property integer $sid 系统消息编号
 * @property string $title 标题
 * @property string $content 内容
 * @property integer $time 添加时间
 * @property integer $status 状态
 */
class AgentMessage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'title', 'content', 'time', 'status'], 'required'],
            ['title', 'string', 'max' => 128],
            ['content', 'safe'],
        ];
    }

    /**
     * 检查是否有新的消息
     * @param $aid integer 代理商编号
     * @return bool
     */
    public static function checkNewMessage($aid)
    {
        $sid = SystemMessage::find()->max('id');
        if ($sid == 0) { // 没有新的系统消息
            return false;
        }
        $max_sid = AgentMessage::find()->where(['aid' => $aid])->max('sid');
        $max_sid = intval($max_sid);
        if ($max_sid >= $sid) { // 系统消息已经全部接收到了
            return false;
        }
        foreach (SystemMessage::find()->where(['>', 'id', $max_sid])->each() as $system_message) {
            /** @var SystemMessage $system_message */
            $agent_message = new AgentMessage();
            $agent_message->aid = $aid;
            $agent_message->sid = $system_message->id;
            $agent_message->title = $system_message->title;
            $agent_message->content = $system_message->content;
            $agent_message->time = $system_message->time;
            $agent_message->status = SystemMessage::STATUS_UNREAD;
            $agent_message->save();
        }
        return true;
    }
}
