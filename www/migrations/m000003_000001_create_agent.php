<?php

use yii\db\Migration;

class m000003_000001_create_agent extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%agent}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(256), // 登录邮箱账号
            'auth_key' => $this->string(32),
            'password' => $this->string(256), // HASH密码
            'mobile' => $this->string(32), // 手机号
            'contact_name' => $this->string(32), // 联系人姓名
            'avatar' => $this->string(128), // 头像
            'area' => $this->string(32), // 区域编码
            'earnest_money_fid' => $this->integer(), // 保证金财务记录编号
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_agent_finance_log1_idx', '{{%agent}}', ['earnest_money_fid']);
        try {
            $this->addForeignKey('fk_agent_finance_log1', '{{%agent}}', ['earnest_money_fid'], '{{%finance_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['agent_status', 1, '正在填写入驻申请'],
            ['agent_status', 2, '填写完成等待客服沟通'],
            ['agent_status', 3, '客服沟通完成等待财务审核加盟费'],
            ['agent_status', 4, '加盟费付款完成等待保证金付款'],
            ['agent_status', 5, '保证金付款完成等待财务审核'],
            ['agent_status', 6, '正常'],
            ['agent_status', 9, '停止'],
            ['agent_status', 0, '删除'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%key_map}}', ['t' => 'agent_status']);
        $this->dropTable('{{%agent}}');
    }
}
