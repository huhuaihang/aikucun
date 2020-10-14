<?php

use yii\db\Migration;

class m000005_000002_create_shop_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_config}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_shop_config_shop1_idx', '{{%shop_config}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_config_shop1', '{{%shop_config}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%shop_config}}');
    }
}
