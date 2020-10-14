<?php

use yii\db\Migration;

class m000010_000007_create_supplier_financial_settlement extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%supplier_financial_settlement}}', [
            'id' => $this->primaryKey(),
            'sid' => $this->integer()->notNull()->comment('供货商编号'),
            'oid' => $this->integer()->notNull()->comment('订单编号'),
            'oiid' => $this->integer()->notNull()->comment('订单内容编号'),
            'gid' => $this->integer()->notNull()->comment('商品编号'),
            'price' => $this->decimal(12, 2)->notNull()->comment('结算价格'),
            'amount' => $this->integer()->notNull()->comment('数量'),
            'money' => $this->decimal(12, 2)->notNull()->comment('结算金额'),
            'status' => $this->integer()->notNull()->comment('状态'),
            'create_time' => $this->integer()->notNull()->comment('创建时间'),
            'lid' => $this->integer()->comment('结算记录编号'),
            'settle_time' => $this->integer()->comment('结算时间'),
            'remark' => $this->text()->comment('备注'),
        ]);
        $this->createIndex('fk_supplier_financial_settlement_supplier1_idx', '{{%supplier_financial_settlement}}', ['sid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_supplier1', '{{%supplier_financial_settlement}}', ['sid'], '{{%supplier}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_supplier_financial_settlement_order1_idx', '{{%supplier_financial_settlement}}', ['oid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_order1', '{{%supplier_financial_settlement}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_supplier_financial_settlement_order_item1_idx', '{{%supplier_financial_settlement}}', ['oiid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_order_item1', '{{%supplier_financial_settlement}}', ['oiid'], '{{%order_item}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_supplier_financial_settlement_goods1_idx', '{{%supplier_financial_settlement}}', ['gid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_goods1', '{{%supplier_financial_settlement}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_supplier_financial_settlement_financial_settlement_log1_idx', '{{%supplier_financial_settlement}}', ['lid']);
        try {
            $this->addForeignKey('fk_supplier_financial_settlement_financial_settlement_log1', '{{%supplier_financial_settlement}}', ['lid'], '{{%supplier_financial_settlement_log}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['supplier_financial_settlement_status', 1, '未到结算期'],
            ['supplier_financial_settlement_status', 2, '金额确定'],
            ['supplier_financial_settlement_status', 3, '等待结算'],
            ['supplier_financial_settlement_status', 4, '已结算'],
            ['supplier_financial_settlement_status', 5, '已经售后退款不再结算'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'supplier_financial_settlement_status']);
        $this->dropTable('{{%supplier_financial_settlement}}');
    }
}
