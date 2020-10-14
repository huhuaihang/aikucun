<?php

use yii\db\Migration;

class m000001_000007_create_bank_reconciliation_pingan extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bank_reconciliation_pingan}}', [
            'id' => $this->primaryKey(), // PK
            'status' => $this->char(2), // 01标识成功，只返回成功
            'date' => $this->string(14), // 支付完成日期（结算日期）
            'charge' => $this->decimal(12, 2), // 订单手续费金额，12位整数，2位小数
            'masterId' => $this->char(10), // 商户号
            'orderId' => $this->string(26), // ID+YYYYMMDD+8位流水
            'currency' => $this->char(3), // 目前只支持RMB
            'amount' => $this->decimal(12, 2), // 订单金额，最大12位整数，2位小数
            'objectName' => $this->string(200), // 款项描述
            'paydate' => $this->string(14), // YYYYMMDDHHMMSS
            'validtime' => $this->integer(), // 订单有效期（毫秒），0不生效
            'remark' => $this->string(500), // 备注字段（不可含分割符号）
            'settleflg' => $this->char(1), // 订单本金清算 1已清算，0待清算
            'settletime' => $this->string(14), // 本金清算时间YYYYMMDDHHMMSS
            'chargeflg' => $this->char(1), // 手续费清算标志 1已清算，0待清算
            'chargetime' => $this->string(14), // 手续费清算时间YYYYMMDDHHMMSS
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bank_reconciliation_pingan}}');
    }
}
