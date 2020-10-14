<?php

use yii\db\Migration;

class m000005_000007_create_goods_type_brand extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_type_brand}}', [
            'id' => $this->primaryKey(), // PK
            'tid' => $this->integer(), // 商品类型编号
            'bid' => $this->integer(), // 商品品牌编号
        ]);
        $this->createIndex('fk_goods_type_brand_goods_type1_idx', '{{%goods_type_brand}}', ['tid']);
        try {
            $this->addForeignKey('fk_goods_type_brand_goods_type1', '{{%goods_type_brand}}', ['tid'], '{{%goods_type}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_type_brand_goods_brand1_idx', '{{%goods_type_brand}}', ['bid']);
        try {
            $this->addForeignKey('fk_goods_type_brand_goods_brand1', '{{%goods_type_brand}}', ['bid'], '{{%goods_brand}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_type_brand}}');
    }
}
