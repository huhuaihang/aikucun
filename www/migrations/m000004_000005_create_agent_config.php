<?php

use yii\db\Migration;

class m000004_000005_create_agent_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%agent_config}}', [
            'id' => $this->primaryKey(), // PK
            'aid' => $this->integer(), // 代理商编号
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_agent_config_agent1_idx', '{{%agent_config}}', ['aid']);
        try {
            $this->addForeignKey('fk_agent_config_agent1', '{{%agent_config}}', ['aid'], '{{%agent}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%agent_config}}');
    }
}
