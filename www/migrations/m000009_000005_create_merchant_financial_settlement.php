<?php

use yii\db\Migration;

class m000009_000005_create_merchant_financial_settlement extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_financial_settlement}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'oid' => $this->integer(), // 订单编号
            'order_money' => $this->decimal(12, 2), // 订单金额
            'refund_money' => $this->decimal(12, 2), // 退款金额
            'merchant_receive_money' => $this->decimal(12, 2), // 商户实收金额
            'charge' => $this->decimal(12, 2), // 服务费
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'lid' => $this->integer(), // 结算记录编号
            'settle_time' => $this->integer(), // 结算时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_mfs_merchant1_idx', '{{%merchant_financial_settlement}}', ['mid']);
        try {
            $this->addForeignKey('fk_mfs_merchant1', '{{%merchant_financial_settlement}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_mfs_order1_idx', '{{%merchant_financial_settlement}}', ['oid']);
        try {
            $this->addForeignKey('fk_mfs_order1', '{{%merchant_financial_settlement}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_mfs_mfsl1_idx', '{{%merchant_financial_settlement}}', ['lid']);
        try {
            $this->addForeignKey('fk_mfs_mfsl1_idx', '{{%merchant_financial_settlement}}', ['lid'], '{{%merchant_financial_settlement_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['merchant_financial_settlement_status', 1, '未结算'],
            ['merchant_financial_settlement_status', 2, '已结算'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'merchant_financial_settlement_status']);
        $this->dropTable('{{%merchant_financial_settlement}}');
    }
}
