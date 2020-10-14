<?php

use yii\db\Migration;

class m000005_000014_create_shop_file_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_file_category}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'name' => $this->string(32), // 名称
        ]);
        $this->createIndex('fk_shop_file_category_shop1_idx', '{{%shop_file_category}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_file_category_shop1_idx', '{{%shop_file_category}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%shop_file_category}}');
    }
}
