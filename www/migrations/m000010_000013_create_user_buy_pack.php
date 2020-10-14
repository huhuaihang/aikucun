<?php

use yii\db\Migration;

class m000010_000013_create_user_buy_pack extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_buy_pack}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'type' => $this->integer(), // 类型 购买升级卡  购买套餐
            'pack_name' => $this->string(128), // 如果类型是套餐卡 保存套餐名称
            'fid' => $this->integer(), // 财务记录编号
            'no' => $this->string(64), // 订单号
            'amount' => $this->integer(), // 数量
            'money' => $this->decimal(12, 2), // 金额
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_user_buy_pack_user1_idx', '{{%user_buy_pack}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_buy_pack_user1', '{{%user_buy_pack}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_buy_pack_finance_log1_idx', '{{%user_buy_pack}}', ['fid']);
        try {
            $this->addForeignKey('fk_user_buy_pack_finance_log1', '{{%user_buy_pack}}', ['fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_buy_pack_status', 1, '待支付'],
            ['user_buy_pack_status', 2, '支付成功'],
            ['user_buy_pack_status', 3, '支付失败'],
            ['user_buy_pack_status', 9, '取消'],
            ['user_buy_pack_status', 0, '删除'],
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_buy_pack_type', 1, '购买升级卡'],
            ['user_buy_pack_type', 2, '购买套餐礼包'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_buy_pack_status']);
        $this->dropTable('{{%user_buy_pack}}');
    }
}
