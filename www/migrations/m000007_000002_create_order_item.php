<?php

use yii\db\Migration;

class m000007_000002_create_order_item extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order_item}}', [
            'id' => $this->primaryKey(), // PK
            'oid' => $this->integer(), // 订单编号
            'gid' => $this->integer(), // 商品编号
            'title' => $this->string(128), // 商品标题
            'sku_key_name' => $this->string(256), // 商品SKU信息
            'amount' => $this->integer(), // 数量
            'price' => $this->decimal(12, 2), // 单价
        ]);
        $this->createIndex('fk_order_item_order1_idx', '{{%order_item}}', ['oid']);
        try {
            $this->addForeignKey('fk_order_item_order1', '{{%order_item}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_order_item_goods1_idx', '{{%order_item}}', ['gid']);
        try {
            $this->addForeignKey('fk_order_item_goods1', '{{%order_item}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%order_item}}');
    }
}
