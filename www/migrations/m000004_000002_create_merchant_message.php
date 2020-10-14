<?php

use yii\db\Migration;

class m000004_000002_create_merchant_message extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_message}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'sid' => $this->integer(), // 系统消息编号
            'title' => $this->string(128), // 标题
            'content' => $this->text(), // 内容
            'time' => $this->integer(), // 添加时间
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_merchant_message_merchant1_idx', '{{%merchant_message}}', ['mid']);
        try {
            $this->addForeignKey('fk_merchant_message_merchant1', '{{%merchant_message}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_merchant_message_system_message1_idx', '{{%merchant_message}}', ['sid']);
        try {
            $this->addForeignKey('fk_merchant_message_system_message1', '{{%merchant_message}}', ['sid'], '{{%system_message}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%merchant_message}}');
    }
}
