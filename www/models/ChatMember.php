<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 聊天成员
 * Class ChatMember
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 聊天编号
 * @property integer $type 类型
 * @property string $member 成员
 * @property integer $create_time 加入时间
 * @property integer $last_read_msg_id 最后读取的消息编号
 *
 * @property Chat $chat 关联聊天
 */
class ChatMember extends ActiveRecord
{
    const TYPE_MEMBER = 1; // 普通成员
    const TYPE_OWNER = 2; // 群主
    const TYPE_ADMIN = 4; // 管理员

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'type', 'member', 'create_time'], 'required'],
            [['member'], 'string', 'max' => 128],
        ];
    }

    /**
     * 关联聊天
     * @return \yii\db\ActiveQuery
     */
    public function getChat()
    {
        return $this->hasOne(Chat::className(), ['id' => 'cid']);
    }
}
