<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 聊天消息
 * Class ChatMessage
 * @package app\models
 *
 * @property integer $id PK
 * @property integer $cid 聊天编号
 * @property string $from 发送成员
 * @property string $to 接收成员
 * @property integer $type 类型
 * @property string $message 内容
 * @property integer $create_time 创建时间
 */
class ChatMessage extends ActiveRecord
{
    const TYPE_TEXT = 1; // 文本
    const TYPE_GOODS = 2; // 商品

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cid', 'from', 'type', 'message'], 'required'],
            [['from', 'to'], 'string', 'max' => 128],
            [['message'], 'safe'],
            ['cid', 'exist', 'targetClass' => Chat::className(), 'targetAttribute' => 'id', 'message' => '没有找到聊天信息。'],
            ['from', function () {
                $member = Chat::findOne($this->cid)->getMemberList()->andWhere(['member' => $this->from])->one();
                if (empty($member)) {
                    $this->addError('from', '发送人错误。');
                }
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $chat = Chat::findOne($this->cid);
            if ($chat->type = Chat::TYPE_CHAT) {
                /** @var ChatMember $member */
                $member = $chat->getMemberList()->andWhere(['<>', 'member', $this->from])->one();
                $this->to = $member->member;
            }
            $this->create_time = time();
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            ChatMember::updateAll(['last_read_msg_id' => $this->id], ['cid' => $this->cid, 'member' => $this->from]);
        }
        parent::afterSave($insert, $changedAttributes);
    }
}
