<?php

use yii\db\Migration;

class m000005_000009_create_goods_attr extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_attr}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'tid' => $this->integer(), // 商品类型编号
            'name' => $this->string(32), // 名称
            'values' => $this->text(), // 可选值JSON
            'is_sku' => $this->integer(), // 是否SKU属性
        ]);
        $this->createIndex('fk_goods_attr_shop1_idx', '{{%goods_attr}}', ['sid']);
        try {
            $this->addForeignKey('fk_goods_attr_shop1', '{{%goods_attr}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_attr_goods_type1_idx', '{{%goods_attr}}', ['tid']);
        try {
            $this->addForeignKey('fk_goods_attr_goods_type1', '{{%goods_attr}}', ['tid'], '{{%goods_type}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_attr}}');
    }
}
