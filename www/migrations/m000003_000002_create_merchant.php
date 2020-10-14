<?php

use yii\db\Migration;

class m000003_000002_create_merchant extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%merchant}}', [
            'id' => $this->primaryKey(), // PK
            'type' => $this->integer(), // 类型
            'aid' => $this->integer(), // 代理商编号
            'username' => $this->string(256), // 登录邮箱账号
            'auth_key' => $this->string(32),
            'password' => $this->string(256), // HASH密码
            'mobile' => $this->string(32), // 手机号
            'contact_name' => $this->string(32), // 联系人姓名
            'avatar' => $this->string(128), // 头像
            'is_person' => $this->integer(), // 是否为个人
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_merchant_agent1_idx', '{{%merchant}}', ['aid']);
        try {
            $this->addForeignKey('fk_merchant_agent1', '{{%merchant}}', ['aid'], '{{%agent}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['merchant_type', 1, '线上'],
            ['merchant_type', 2, '线下'],
            ['merchant_status', 1, '正在填写入驻申请'],
            ['merchant_status', 2, '基本资料填写完成等待数据审核'],
            ['merchant_status', 3, '客服审核通过，可登录商户后台，完善资料'],
            ['merchant_status', 4, '详细资料填写完成等待数据审核'],
            ['merchant_status', 5, '客服审核通过并确定保证金金额，等待支付'],
            ['merchant_status', 6, '完成，此时可以正常使用'],
            ['merchant_status', 9, '停止'],
            ['merchant_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'merchant_status']);
        $this->delete('{{%key_map}}', ['t' => 'merchant_type']);
        $this->dropTable('{{%merchant}}');
    }
}
