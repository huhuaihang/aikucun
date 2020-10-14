<?php

use yii\db\Migration;

class m000001_000011_create_bank_reconciliation_allinpay_ali extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bank_reconciliation_allinpay_ali}}', [
            'id' => $this->primaryKey(), // PK
            'mch_id' => $this->string(32), // 商户号
            'store_name' => $this->string(32), // 门店名称
            'term_no' => $this->string(32), // 终端号
            'trade_time' => $this->string(32), // 交易时间
            'trade_type' => $this->string(32), // 交易类型
            'trade_batch_no' => $this->string(32), // 交易批次号
            'proof_no' => $this->string(32), // 凭证号
            'ref_no' => $this->string(32), // 参考号
            'card_no' => $this->string(32), // 卡号
            'card_type' => $this->string(32), // 卡类别
            'card_org_no' => $this->string(32), // 发卡行机构代码
            'card_org_name' => $this->string(32), // 发卡行名称
            'trade_money' => $this->decimal(12, 2), // 交易金额
            'tax_money' => $this->decimal(12, 2), // 手续费
            'trade_date' => $this->string(32), // 交易日期
            'trade_no' => $this->string(32), // 交易单号
            'order_no' => $this->string(32), // 订单号
            'mch_remark' => $this->string(128), // 商户备注
            'app_no' => $this->string(32), // 对接应用号
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bank_reconciliation_allinpay_ali}}');
    }
}
