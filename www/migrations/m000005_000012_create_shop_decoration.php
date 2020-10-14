<?php

use yii\db\Migration;

class m000005_000012_create_shop_decoration extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_decoration}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'header_background_image' => $this->string(128), // 头部背景图
        ]);
        $this->createIndex('fk_shop_decoration_shop1_idx', '{{%shop_decoration}}', ['sid']);
        try {
            $this->addForeignKey('fk_goods_decoration_shop1', '{{%shop_decoration}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%shop_decoration}}');
    }
}
