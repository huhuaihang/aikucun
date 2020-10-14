<?php

use yii\db\Migration;

class m000004_000007_create_user_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_config}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 代理商编号
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_user_config_user1_idx', '{{%user_config}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_config_user1', '{{%user_config}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_config}}');
    }
}
