<?php

use yii\db\Migration;

class m000010_000008_create_supplier_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier_config}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 供货商编号
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_supplier_config_supplier1_idx', '{{%supplier_config}}', ['sid']);
        try {
            $this->addForeignKey('fk_supplier_config_supplier1', '{{%supplier_config}}', ['sid'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%supplier_config}}');
    }
}
