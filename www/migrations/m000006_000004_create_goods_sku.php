<?php

use yii\db\Migration;

class m000006_000004_create_goods_sku extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_sku}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'key' => $this->string(128), // 属性值
            'key_name' => $this->string(256), // 属性值中文
            'market_price' => $this->decimal(12, 2), // 市场价
            'price' => $this->decimal(12, 2), // 单价
            'stock' => $this->integer(), // 库存
        ]);
        $this->createIndex('fk_goods_sku_goods1_idx', '{{%goods_sku}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_sku_goods1', '{{%goods_sku}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_sku}}');
    }
}
