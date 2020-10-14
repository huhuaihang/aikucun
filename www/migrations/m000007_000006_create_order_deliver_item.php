<?php

use yii\db\Migration;

class m000007_000006_create_order_deliver_item extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_deliver_item}}', [
            'id' => $this->primaryKey(), // PK
            'did' => $this->integer(), // 订单发货单编号
            'oiid' => $this->integer(), // 订单内容编号
            'amount' => $this->integer(), // 数量
        ]);
        $this->createIndex('fk_order_deliver_item_order_deliver1_idx', '{{%order_deliver_item}}', ['did']);
        try {
            $this->addForeignKey('fk_order_deliver_item_order_deliver1', '{{%order_deliver_item}}', ['did'], '{{%order_deliver}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_deliver_item_order_item1_idx', '{{%order_deliver_item}}', ['oiid']);
        try {
            $this->addForeignKey('fk_order_deliver_item_order_item1', '{{%order_deliver_item}}', ['oiid'], '{{%order_item}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%order_deliver_item}}');
    }
}
