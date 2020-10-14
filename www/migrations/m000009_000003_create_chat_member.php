<?php

use yii\db\Migration;

class m000009_000003_create_chat_member extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%chat_member}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 聊天编号
            'type' => $this->integer(), // 类型
            'member' => $this->string(128), // 成员
            'create_time' => $this->integer(), // 加入时间
            'last_read_msg_id' => $this->integer(), // 最后读取的消息编号
        ]);
        $this->createIndex('fk_chat_member_chat1_idx', '{{%chat_member}}', ['cid']);
        try {
            $this->addForeignKey('fk_chat_member_chat1', '{{%chat_member}}', ['cid'], '{{%chat}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_chat_member_chat_message1_idx', '{{%chat_member}}', ['last_read_msg_id']);
        try {
            $this->addForeignKey('fk_chat_member_chat_message1', '{{%chat_member}}', ['last_read_msg_id'], '{{%chat_message}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['chat_member_type', 1, '普通成员'],
            ['chat_member_type', 2, '群主'],
            ['chat_member_type', 4, '管理员'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'chat_member_type']);
        $this->dropTable('{{%chat_member}}');
    }
}
