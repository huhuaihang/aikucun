<?php

use yii\db\Migration;

class m000001_000006_create_finance_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%finance_log}}', [
            'id' => $this->primaryKey(), // PK
            'trade_no' => $this->string(128), // 交易号
            'type' => $this->integer(), // 类型
            'money' => $this->decimal(12, 2), // 金额
            'pay_method' => $this->integer(), // 支付方式
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'update_time' => $this->integer(), // 更新时间
            'remark' => $this->text(), // 备注
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['finance_log_type', 1, '用户充值'],
            ['finance_log_type', 2, '订单支付'],
            ['finance_log_type', 3, '商户保证金'],
            ['finance_log_type', 4, '代理商保证金'],
            ['finance_log_type', 5, '订单售后退款'],
            ['finance_log_type', 6, '订单取消退款'],
            ['finance_log_pay_method', 11, '平安银行卡'],
            ['finance_log_pay_method', 21, '微信扫码'],
            ['finance_log_pay_method', 22, '微信APP'],
            ['finance_log_pay_method', 23, '微信公众号'],
            ['finance_log_pay_method', 24, '微信H5'],
            ['finance_log_pay_method', 31, '支付宝'],
            ['finance_log_pay_method', 41, '通联银行支付'],
            ['finance_log_pay_method', 42, '通联H5支付'],
            ['finance_log_pay_method', 43, '通联支付宝支付'],
            ['finance_log_pay_method', 91, '余额'],
            ['finance_log_pay_method', 99, '货到付款'],
            ['finance_log_status', 1, '待支付'],
            ['finance_log_status', 2, '支付成功'],
            ['finance_log_status', 9, '支付失败'],
            ['finance_log_status', 0, '支付取消或关闭'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'finance_log_status']);
        $this->delete('{{%key_map}}', ['t' => 'finance_log_pay_method']);
        $this->delete('{{%key_map}}', ['t' => 'finance_log_type']);
        $this->dropTable('{{%finance_log}}');
    }
}
