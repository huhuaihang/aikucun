<?php

use yii\db\Migration;

class m000005_000005_create_shop_brand extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_brand}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'bid' => $this->integer(), // 商品品牌编号
            'type' => $this->integer(), // 类型
            'status' => $this->integer(), // 状态
            'file_list' => $this->text(), // 资料列表JSON
        ]);
        $this->createIndex('fk_shop_brand_shop1_idx', '{{%shop_brand}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_brand_shop1', '{{%shop_brand}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_brand_brand1_idx', '{{%shop_brand}}', ['bid']);
        try {
            $this->addForeignKey('fk_shop_brand_brand1', '{{%shop_brand}}', ['bid'], '{{%goods_brand}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_brand_type', 1, '自有'],
            ['shop_brand_type', 2, '代理'],
            ['shop_brand_status', 1, '等待审核'],
            ['shop_brand_status', 2, '审核通过'],
            ['shop_brand_status', 9, '审核拒绝'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_brand_status']);
        $this->delete('{{%key_map}}', ['t' => 'shop_brand_type']);
        $this->dropTable('{{%shop_brand}}');
    }
}
