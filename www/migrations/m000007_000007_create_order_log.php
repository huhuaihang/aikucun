<?php

use yii\db\Migration;

class m000007_000007_create_order_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_log}}', [
            'id' => $this->primaryKey(), // PK
            'oid' => $this->integer(), // 订单编号
            'u_type' => $this->integer(), // 用户类型
            'uid' => $this->integer(), // 用户编号
            'content' => $this->string(512), // 操作内容
            'data' => $this->text(), // 附加数据
            'time' => $this->integer(), // 时间
        ]);
        $this->createIndex('fk_order_log_order1_idx', '{{%order_log}}', ['oid']);
        try {
            $this->addForeignKey('fk_order_log_order1', '{{%order_log}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_log_u1_idx', '{{%order_log}}', ['uid']);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['order_log_u_type', 1, '管理后台用户'],
            ['order_log_u_type', 2, '代理商'],
            ['order_log_u_type', 3, '商户'],
            ['order_log_u_type', 4, '前台用户'],
            ['order_log_u_type', 9, '系统'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'order_log_u_type']);
        $this->dropTable('{{%order_log}}');
    }
}
