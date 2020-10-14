<?php

use yii\db\Migration;

class m000004_000012_create_user_recharge extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_recharge}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'fid' => $this->integer(), // 财务记录编号
            'money' => $this->decimal(12, 2), // 充值金额
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_user_recharge_user1_idx', '{{%user_recharge}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_recharge_user1', '{{%user_recharge}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_recharge_finance_log1_idx', '{{%user_recharge}}', ['fid']);
        try {
            $this->addForeignKey('fk_user_recharge_finance_log1', '{{%user_recharge}}', ['fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_recharge_status', 1, '待支付 |'],
            ['user_recharge_status', 2, '支付成功'],
            ['user_recharge_status', 9, '支付失败'],
            ['user_recharge_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_recharge_status']);
        $this->dropTable('{{%user_recharge}}');
    }
}
