<?php

use yii\db\Migration;

class m000004_000006_create_merchant_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_config}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_merchant_config_user1_idx', '{{%merchant_config}}', ['mid']);
        try {
            $this->addForeignKey('fk_merchant_config_user1', '{{%merchant_config}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%merchant_config}}');
    }
}
