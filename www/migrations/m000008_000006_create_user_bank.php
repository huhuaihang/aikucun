<?php

use yii\db\Migration;

class m000008_000006_create_user_bank extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_bank}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'bank_name' => $this->string(32), // 银行名称
            'bank_address' => $this->string(128), // 开户行所在地
            'account_name' => $this->string(128), // 账户名
            'account_no' => $this->string(128), // 账号
        ]);
        $this->createIndex('fk_user_bank_user1_idx', '{{%user_bank}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_bank_user1', '{{%user_bank}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_bank}}');
    }
}
