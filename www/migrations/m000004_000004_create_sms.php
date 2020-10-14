<?php

use yii\db\Migration;

class m000004_000004_create_sms extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%sms}}', [
            'id' => $this->primaryKey(), // PK
            'u_type' => $this->integer(), // 用户类型
            'uid' => $this->integer(), // 用户编号，根据u_type确定对应表
            'type' => $this->integer(), // 短信类型
            'msgid' => $this->string(64), // 流水号
            'mobile' => $this->string(32), // 手机号
            'content' => $this->text(), // 内容
            'send_time' => $this->integer(), // 发送时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_sms_u_idx', '{{%sms}}', ['uid']);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['sms_u_type', 1, '管理后台用户'],
            ['sms_u_type', 2, '代理商'],
            ['sms_u_type', 3, '商户'],
            ['sms_u_type', 4, '前台用户'],
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['sms_type', 1, '绑定手机'],
            ['sms_type', 2, '忘记密码'],
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['sms_status', 1, '待发送'],
            ['sms_status', 2, '暂停发送'],
            ['sms_status', 3, '提交成功'],
            ['sms_status', 4, '提交失败'],
            ['sms_status', 5, '发送成功'],
            ['sms_status', 6, '发送失败'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'sms_status']);
        $this->delete('{{%key_map}}', ['t' => 'sms_type']);
        $this->delete('{{%key_map}}', ['t' => 'sms_u_type']);
        $this->dropTable('{{%sms}}');
    }
}
