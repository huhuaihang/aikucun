<?php

use yii\db\Migration;

class m000000_000003_create_system_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%system_message}}', [
            'id' => $this->primaryKey(), // PK
            'title' => $this->string(128), // 标题
            'content' => $this->text(), // 内容
            'time' => $this->integer(), // 时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['message_status', 1, '未读'],
            ['message_status', 9, '已读'],
            ['message_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'message_status']);
        $this->dropTable('{{%system_message}}');
    }
}
