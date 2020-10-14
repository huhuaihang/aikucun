<?php

use yii\db\Migration;

class m000004_000008_create_user_account extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_account}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'money' => $this->decimal(12, 2), // 现金
            'commission' => $this->decimal(12, 2), // 佣金
            'score' => $this->integer(), // 积分
        ]);
        $this->createIndex('fk_user_account_user1_idx', '{{%user_account}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_account_user1', '{{%user_account}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_account}}');
    }
}
