<?php

use yii\db\Migration;

class m000006_000002_create_goods_config extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_config}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号 FK goods.id
            'k' => $this->string(128), // 键
            'v' => $this->text(), // 值
        ]);
        $this->createIndex('fk_goods_config_goods1_idx', '{{%goods_config}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_config_goods1', '{{%goods_config}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_config}}');
    }
}
