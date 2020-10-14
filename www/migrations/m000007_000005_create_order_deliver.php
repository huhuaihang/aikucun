<?php

use yii\db\Migration;

class m000007_000005_create_order_deliver extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_deliver}}', [
            'id' => $this->primaryKey(), // PK
            'oid' => $this->integer(), // 订单编号
            'eid' => $this->integer(), // 物流编号
            'no' => $this->string(32), // 快递单号
            'create_time' => $this->integer(), // 创建时间
            'send_time' => $this->integer(), // 发货时间
            'status' => $this->integer(), // 状态
            'trace' => $this->text(), // 物流跟踪信息
        ]);
        $this->createIndex('fk_order_deliver_order1_idx', '{{%order_deliver}}', ['oid']);
        try {
            $this->addForeignKey('fk_order_deliver_order1', '{{%order_deliver}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_deliver_express1_idx', '{{%order_deliver}}', ['eid']);
        try {
            $this->addForeignKey('fk_order_deliver_express1', '{{%order_deliver}}', ['eid'], '{{%express}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['order_deliver_status', 1, '等待发货'],
            ['order_deliver_status', 2, '已发货'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'order_deliver_status']);
        $this->dropTable('{{%order_deliver}}');
    }
}
