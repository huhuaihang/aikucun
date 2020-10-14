<?php

use yii\db\Migration;

class m000011_000006_create_master_user_account_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%master_user_account_log}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'money' => $this->decimal(12, 2), // 现金
            'commission' => $this->decimal(12, 2), // 佣金
            'score' => $this->integer(), // 积分
            'level_money' => $this->decimal(12, 2), // 等级现金
            'prepare_level_money' => $this->decimal(12, 2), // 实际等级现金
            'time' => $this->integer(), // 时间
            'remark' => $this->text(), // 备注
            'create_time' => $this->integer(), // 创建时间
            'jan' => $this->decimal(12,2),
            'feb' => $this->decimal(12,2),
            'mar' => $this->decimal(12,2),
            'apr' => $this->decimal(12,2),
            'may' => $this->decimal(12,2),
            'jun' => $this->decimal(12,2),
            'jul' => $this->decimal(12,2),
            'aug' => $this->decimal(12,2),
            'sep' => $this->decimal(12,2),
            'oct' => $this->decimal(12,2),
            'nov' => $this->decimal(12,2),
            'dec' => $this->decimal(12,2),
            'year' => $this->integer(),

        ]);
        $this->createIndex('fk_master_user_account_log_user1_idx', '{{%master_user_account_log}}', ['uid']);
        try {
            $this->addForeignKey('fk_master_user_account_log_user1', '{{%master_user_account_log}}', ['uid'], '{{%master_user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%master_user_account_log}}');
    }
}
