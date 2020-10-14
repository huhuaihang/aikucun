<?php

use yii\db\Migration;

class m000001_000010_create_bank_reconciliation_allinpay extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bank_reconciliation_allinpay}}', [
            'id' => $this->primaryKey(), // PK
            'type' => $this->string(32), // 交易类型
            'date' => $this->string(32), // 结算日期
            'mch_id' => $this->string(32), // 商户号
            'trade_time' => $this->string(32), // 交易时间
            'trade_no' => $this->string(128), // 商户订单号
            'out_trade_no' => $this->string(128), // 通联流水号
            'money' => $this->decimal(12, 2), // 交易金额
            'fee' => $this->decimal(12, 2), // 手续费
            'settle_money' => $this->decimal(12, 2), // 清算金额
            'currency' => $this->string(8), // 币种
            'origin_money' => $this->integer(), // 商户原始订单金额（分）
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bank_reconciliation_allinpay}}');
    }
}
