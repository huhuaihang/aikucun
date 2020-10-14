<?php

use yii\db\Migration;

class m000005_000013_create_shop_decoration_item extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_decoration_item}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'type' => $this->integer(), // 类型
            'sort' => $this->integer(), // 排序
            'data' => $this->text(), // 数据
        ]);
        $this->createIndex('fk_shop_decoration_item_shop1_idx', '{{%shop_decoration_item}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_decoration_item_shop1', '{{%shop_decoration_item}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_decoration_item_type', 1, '热点'],
            ['shop_decoration_item_type', 2, '轮播'],
            ['shop_decoration_item_type', 3, '商品'],
            ['shop_decoration_item_type', 4, '页面'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_decoration_item_type']);
        $this->dropTable('{{%shop_decoration_item}}');
    }
}
