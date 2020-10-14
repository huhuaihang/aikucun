<?php

use yii\db\Migration;

class m000004_000009_create_user_account_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_account_log}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'money' => $this->decimal(12, 2), // 现金
            'commission' => $this->decimal(12, 2), // 佣金
            'score' => $this->integer(), // 积分
            'level_money' => $this->decimal(12, 2), // 等级现金
            'prepare_level_money' => $this->decimal(12, 2), // 实际等级现金
            'time' => $this->integer(), // 时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_user_account_log_user1_idx', '{{%user_account_log}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_account_log_user1', '{{%user_account_log}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_account_log}}');
    }
}
