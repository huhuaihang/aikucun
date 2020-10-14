<?php

use yii\db\Migration;

class m000010_000009_create_supplier_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier_message}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 供货商编号
            'smid' => $this->integer(), // 系统消息编号
            'title' => $this->string(128), // 标题
            'content' => $this->text(), // 内容
            'time' => $this->integer(), // 添加时间
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_supplier_message_supplier1_idx', '{{%supplier_message}}', ['sid']);
        try {
            $this->addForeignKey('fk_supplier_message_supplier1', '{{%supplier_message}}', ['sid'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_supplier_message_system_message1_idx', '{{%supplier_message}}', ['smid']);
        try {
            $this->addForeignKey('fk_supplier_message_system_message1', '{{%supplier_message}}', ['smid'], '{{%system_message}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%supplier_message}}');
    }
}
