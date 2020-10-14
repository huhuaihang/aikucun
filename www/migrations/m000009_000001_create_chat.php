<?php

use yii\db\Migration;

class m000009_000001_create_chat extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%chat}}', [
            'id' => $this->primaryKey(), // PK
            'type' => $this->integer(), // 类型
            'create_member' => $this->string(128), // 创建人
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
           ['chat_type', 1, '单聊'],
           ['chat_type', 2, '群聊'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'chat_type']);
        $this->dropTable('{{%chat}}');
    }
}
