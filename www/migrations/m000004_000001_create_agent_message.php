<?php

use yii\db\Migration;

class m000004_000001_create_agent_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%agent_message}}', [
            'id' => $this->primaryKey(), // PK
            'aid' => $this->integer(), // 代理商编号
            'sid' => $this->integer(), // 系统消息编号
            'title' => $this->string(128), // 标题
            'content' => $this->text(), // 内容
            'time' => $this->integer(), // 添加时间
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_agent_message_agent1_idx', '{{%agent_message}}', ['aid']);
        try {
            $this->addForeignKey('fk_agent_message_agent1', '{{%agent_message}}', ['aid'], '{{%agent}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_agent_message_system_message1_idx', '{{%agent_message}}', ['sid']);
        try {
            $this->addForeignKey('fk_agent_message_system_message1', '{{%agent_message}}', ['sid'], '{{%system_message}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%agent_message}}');
    }
}
