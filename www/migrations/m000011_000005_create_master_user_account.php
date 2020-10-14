<?php

use yii\db\Migration;

class m000011_000005_create_master_user_account extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%master_user_account}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'money' => $this->decimal(12, 2), // 现金
            'commission' => $this->decimal(12, 2), // 佣金
            'score' => $this->integer(), // 积分
        ]);
        $this->createIndex('fk_master_user_account_user1_idx', '{{%master_user_account}}', ['uid']);
        try {
            $this->addForeignKey('fk_master_user_account_user1', '{{%master_user_account}}', ['uid'], '{{%master_user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%master_user_account}}');
    }
}
