<?php

use yii\db\Migration;

class m000004_000010_create_feedback extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%feedback}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'client' => $this->string(512), // 客户端信息
            'version' => $this->string(32), // 客户端版本号
            'content' => $this->text(), // 内容
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_feedback_user1_idx', '{{%feedback}}', ['uid']);
        try {
            $this->addForeignKey('fk_feedback_user1', '{{%feedback}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['feedback_status', 1, '待处理'],
            ['feedback_status', 9, '已处理'],
            ['feedback_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'feedback_status']);
        $this->dropTable('{{%feedback}}');
    }
}
