<?php

use yii\db\Migration;

class m000010_000005_create_supplier extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), //公司名称
            'mobile' => $this->string(32), // 手机号
            'auth_key' => $this->string(32), // auth_key
            'password' => $this->string(256), // 密码
            'status' => $this->integer(), // 状态码
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['supplier_status', 1, '通过'],
            ['supplier_status', 9, '停用'],
            ['supplier_status', 0, '删除'],
        ]);
        $this->addColumn('{{%goods}}', 'supplier_id', $this->integer()->after('bid'));
        $this->createIndex('fk_goods_supplier1_idx', '{{%goods}}', ['supplier_id']);
        try {
            $this->addForeignKey('fk_goods_supplier1', '{{%goods}}', ['supplier_id'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->addColumn('{{%goods}}', 'supplier_price', $this->decimal(12, 2)->comment('供货商结算价')->after('price'));
        $this->addColumn('{{%goods_sku}}', 'supplier_price', $this->decimal(12, 2)->comment('供货商结算价')->after('price'));
        $this->addColumn('{{%order_deliver}}', 'supplier_id', $this->integer()->after('oid'));
        $this->createIndex('fk_order_deliver_supplier1_idx', '{{%order_deliver}}', ['supplier_id']);
        try {
            $this->addForeignKey('fk_order_deliver_supplier1', '{{%order_deliver}}', ['supplier_id'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->addColumn('{{%order_refund}}', 'oid', $this->integer()->after('id'));
        $this->createIndex('fk_order_refund_order1_idx', '{{%order_refund}}', ['oid']);
        try {
            $this->addForeignKey('fk_order_refund_order1', '{{%order_refund}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->addColumn('{{%order_refund}}', 'supplier_id', $this->integer()->after('oiid'));
        $this->createIndex('fk_order_refund_supplier1_idx', '{{%order_refund}}', ['supplier_id']);
        try {
            $this->addForeignKey('fk_order_refund_supplier1', '{{%order_refund}}', ['supplier_id'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_order_refund_supplier1', '{{%order_refund}}');
        $this->dropIndex('fk_order_refund_supplier1_idx', '{{%order_refund}}');
        $this->dropColumn('{{%order_refund}}', 'supplier_id');
        $this->dropForeignKey('fk_order_deliver_supplier1', '{{%order_deliver}}');
        $this->dropIndex('fk_order_deliver_supplier1_idx', '{{%order_deliver}}');
        $this->dropColumn('{{%order_deliver}}', 'supplier_id');
        $this->dropForeignKey('fk_goods_supplier1', '{{%goods}}');
        $this->dropIndex('fk_goods_supplier1_idx', '{{%goods}}');
        $this->dropColumn('{{%goods}}', 'supplier_id');
        $this->delete('{{%key_map}}', ['t' => 'supplier_status']);
        $this->dropTable('{{%supplier}}');
    }
}
