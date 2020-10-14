<?php

use yii\db\Migration;

class m000008_000001_create_user_cart extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_cart}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'sid' => $this->integer(), // 店铺编号
            'gid' => $this->integer(), // 商品编号
            'sku_key_name' => $this->string(256), // 商品SKU信息
            'amount' => $this->integer(), // 数量
            'price' => $this->decimal(12, 2), // 最后更新的单价
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_cart_user1_idx', '{{%user_cart}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_cart_user1', '{{%user_cart}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_cart_shop1_idx', '{{%user_cart}}', ['sid']);
        try {
            $this->addForeignKey('fk_user_cart_shop1', '{{%user_cart}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_cart_goods1_idx', '{{%user_cart}}', ['gid']);
        try {
            $this->addForeignKey('fk_user_cart_goods1', '{{%user_cart}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_cart}}');
    }
}
