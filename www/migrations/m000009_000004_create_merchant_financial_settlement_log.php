<?php

use yii\db\Migration;

class m000009_000004_create_merchant_financial_settlement_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant_financial_settlement_log}}', [
            'id' => $this->primaryKey(), // PK
            'mid' => $this->integer(), // 商户编号
            'money' => $this->decimal(12, 2), // 金额
            'bank_info' => $this->string(256), // 银行信息
            'proof_file' => $this->string(128), // 凭证文件
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_mfsl_merchant1_idx', '{{%merchant_financial_settlement_log}}', ['mid']);
        try {
            $this->addForeignKey('fk_mfsl_merchant1', '{{%merchant_financial_settlement_log}}', ['mid'], '{{%merchant}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['merchant_financial_settlement_log_status', 1, '未结算'],
            ['merchant_financial_settlement_log_status', 2, '已结算'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'merchant_financial_settlement_log']);
        $this->dropTable('{{%merchant_financial_settlement_log}}');
    }
}
