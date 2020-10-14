<?php

use yii\db\Migration;

class m000008_000007_create_user_withdraw extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_withdraw}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'money' => $this->decimal(12, 2), // 提现金额
            'tax' => $this->decimal(12, 2), // 手续费
            'bank_name' => $this->string(32), // 银行名称
            'bank_address' => $this->string(128), // 开户行所在地
            'account_name' => $this->string(128), // 账户名
            'account_no' => $this->string(128), // 账号
            'create_time' => $this->integer(), // 创建时间
            'apply_time' => $this->integer(), // 通过时间
            'finish_time' => $this->integer(), // 完毕时间
            'type' => $this->integer(), // 体现类型
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_user_withdraw_user1_idx', '{{%user_withdraw}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_withdraw_user1', '{{%user_withdraw}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_withdraw_status', 1, '等待审核'],
            ['user_withdraw_status', 2, '通过'],
            ['user_withdraw_status', 9, '拒绝'],
            ['user_withdraw_status', 0, '删除'],
            ['user_withdraw_type', 1, '补贴提现'],
            ['user_withdraw_type', 2, '佣金提现'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_withdraw_status']);
        $this->delete('{{%key_map}}', ['t' => 'user_withdraw_type']);
        $this->dropTable('{{%user_withdraw}}');
    }
}
