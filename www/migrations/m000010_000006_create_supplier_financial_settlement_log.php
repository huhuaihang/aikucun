<?php

use yii\db\Migration;

class m000010_000006_create_supplier_financial_settlement_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier_financial_settlement_log}}', [
            'id' => $this->primaryKey(),
            'sid' => $this->integer()->notNull()->comment('供货商编号'),
            'money' => $this->decimal(12, 2)->notNull()->comment('结算金额'),
            'bank_info' => $this->string(256)->comment('银行信息'),
            'proof_file' => $this->text()->comment('凭证文件JSON'),
            'create_time' => $this->integer()->notNull()->comment('创建时间'),
            'status' => $this->integer()->notNull()->comment('状态'),
            'remark' => $this->text()->comment('备注'),
        ]);
        $this->createIndex('fk_supplier_financial_settlement_log_supplier1_idx', '{{%supplier_financial_settlement_log}}', ['sid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_log_supplier1', '{{%supplier_financial_settlement_log}}', ['sid'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['supplier_financial_settlement_log_status', 1, '未结算'],
            ['supplier_financial_settlement_log_status', 2, '已锁定'],
            ['supplier_financial_settlement_log_status', 3, '已结算'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'supplier_financial_settlement_log_status']);
        $this->dropTable('{{%supplier_financial_settlement_log}}');
    }
}
