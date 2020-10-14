<?php

use yii\db\Migration;

class m000004_000003_create_user_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_message}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'title' => $this->string(128), // 标题
            'content' => $this->text(), // 内容
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_message_user1_idx', '{{%user_message}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_message_user1', '{{%user_message}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_message_status', 1, '未读'],
            ['user_message_status', 9, '已读'],
            ['user_message_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_message_status']);
        $this->dropTable('{{%user_message}}');
    }
}
