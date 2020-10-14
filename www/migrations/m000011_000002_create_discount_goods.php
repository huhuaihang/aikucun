<?php

use yii\db\Migration;

class m000011_000002_create_discount_goods extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%discount_goods}}', [
            'id' => $this->primaryKey(), // PK
            'did' => $this->integer(), // 减折价编号
            'gid' => $this->integer(), // 商品编号
            'type' => $this->integer(), // 类型：减价、折价
            'price' => $this->decimal(12, 2), // 减加：0.50表示在原价基础上减0.5元
            'ratio' => $this->integer(), // 折扣：1-99，85表示打8.5折
        ]);
        $this->createIndex('fk_discount_goods_discount1_idx', '{{%discount_goods}}', ['did']);
        try {
            $this->addForeignKey('fk_discount_goods_discount1', '{{%discount_goods}}', ['did'], '{{%discount}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_discount_goods_goods1_idx', '{{%discount_goods}}', ['gid']);
        try {
            $this->addForeignKey('fk_discount_goods_goods1', '{{%discount_goods}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['discount_goods_type', 1, '减价'],
            ['discount_goods_type', 2, '折价'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'discount_goods_type']);
        $this->dropTable('{{%discount_goods}}');
    }
}
