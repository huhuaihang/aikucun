<?php

use yii\db\Migration;

class m000001_000009_create_bank_reconciliation_weixin extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bank_reconciliation_weixin}}', [
            'id' => $this->primaryKey(), // PK
            'trade_time' => $this->string(32), // 交易时间
            'app_id' => $this->string(128), // 公众账号ID
            'mch_id' => $this->string(128), // 商户号
            'sub_mch_id' => $this->string(128), // 子商户号
            'client_no' => $this->string(128), // 设备号
            'weixin_trade_id' => $this->string(128), // 微信订单号
            'out_trade_no' => $this->string(128), // 商户订单号
            'user_open_id' => $this->string(128), // 用户标识
            'trade_type' => $this->string(32), // 交易类型
            'trade_status' => $this->string(32), // 交易状态
            'pay_bank' => $this->string(32), // 付款银行
            'currency' => $this->string(32), // 货币种类
            'order_amount' => $this->decimal(12, 2), // 总金额
            'merchant_red' => $this->decimal(12, 2), // 企业红包金额
            'subject' => $this->string(128), // 商品名称
            'merchant_data' => $this->string(256), // 商户数据包
            'charge' => $this->decimal(12, 5), // 手续费
            'charge_ratio' => $this->string(32), // 费率
            'refund_trade_id' => $this->string(128), // 微信退款单号
            'refund_out_trade_no' => $this->string(128), // 商户退款单号
            'refund_amount' => $this->decimal(12, 2), // 退款金额
            'refund_merchant_red' => $this->decimal(12, 2), // 企业红包退款金额
            'refund_type' => $this->string(32), // 退款类型
            'refund_status' => $this->string(32), // 退款状态
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bank_reconciliation_weixin}}');
    }
}
