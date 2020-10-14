<?php

use yii\db\Migration;

class m000005_000011_create_shop_goods_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_goods_category}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'name' => $this->string(32), // 名称
            'sort' => $this->integer(), // 排序
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_sgc_shop1_idx', '{{%shop_goods_category}}', ['sid']);
        try {
            $this->addForeignKey('fk_sgc_shop1', '{{%shop_goods_category}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_goods_category_status', 1, '显示'],
            ['shop_goods_category_status', 9, '隐藏'],
            ['shop_goods_category_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_goods_category_status']);
        $this->dropTable('{{%shop_goods_category}}');
    }
}
