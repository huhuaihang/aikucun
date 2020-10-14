<?php

use yii\db\Migration;

class m000009_000002_create_chat_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%chat_message}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 聊天编号
            'from' => $this->string(128), // 发送成员
            'to' => $this->string(128), // 接收成员
            'type' => $this->integer(), // 消息类型
            'message' => $this->text(), // 内容
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_chat_message_chat1_idx', '{{%chat_message}}', ['cid']);
        try {
            $this->addForeignKey('fk_chat_message_chat1', '{{%chat_message}}', ['cid'], '{{%chat}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['chat_message_type', 1, '文本'],
            ['chat_message_type', 2, '商品'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'chat_message_type']);
        $this->dropTable('{{%chat_message}}');
    }
}
