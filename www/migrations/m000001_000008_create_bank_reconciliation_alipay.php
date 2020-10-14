<?php

use yii\db\Migration;

class m000001_000008_create_bank_reconciliation_alipay extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bank_reconciliation_alipay}}', [
            'id' => $this->primaryKey(),
            'alipay_trade_id' => $this->string(128), // 支付宝交易号
            'out_trade_no' => $this->string(128), // 商户订单号
            'biz_type' => $this->string(32), // 业务类型
            'subject' => $this->string(128), // 商品名称
            'create_time' => $this->string(32), // 创建时间
            'finish_time' => $this->string(32), // 完成时间
            'shop_no' => $this->string(32), // 门店编号
            'shop_name' => $this->string(32), // 门店名称
            'shop_user' => $this->string(32), // 操作员
            'shop_term_no' => $this->string(32), // 终端号
            'user_account' => $this->string(128), // 对方账户
            'total_amount' => $this->decimal(12, 2), // 订单金额
            'merchant_receive_amount' => $this->decimal(12, 2), // 商家实收
            'alipay_red' => $this->decimal(12, 2), // 支付宝红包（元）
            'alipay_score' => $this->decimal(12, 2), // 集分宝（元）
            'alipay_preference' => $this->decimal(12, 2), // 支付宝优惠（元）
            'merchant_preference' => $this->decimal(12, 2), // 商家优惠（元）
            'coupon_amount' => $this->decimal(12, 2), // 券核销金额（元）
            'coupon_name' => $this->string(32), // 券名称
            'merchant_red' => $this->decimal(12, 2), // 商家红包
            'card_amount' => $this->decimal(12, 2), // 卡消费金额
            'refund_trade_no' => $this->string(128), // 退款单号
            'charge' => $this->decimal(12, 2), // 服务费（元）
            'commission' => $this->decimal(12, 2), // 分润（元）
            'remark' => $this->text(), // 备注
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bank_reconciliation_alipay}}');
    }
}
