<?php

use yii\db\Migration;

class m000006_000003_create_goods_attr_value extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_attr_value}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'aid' => $this->integer(), // 属性编号
            'value' => $this->text(), // 值
            'image' => $this->string(128), // 属性图片
        ]);
        $this->createIndex('fk_gav_goods1_idx', '{{%goods_attr_value}}', ['gid']);
        try {
            $this->addForeignKey('fk_gav_goods1', '{{%goods_attr_value}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_gav_attr1_idx', '{{%goods_attr_value}}', ['aid']);
        try {
            $this->addForeignKey('fk_gav_attr1', '{{%goods_attr_value}}', ['aid'], '{{%goods_attr}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_attr_value}}');
    }
}
