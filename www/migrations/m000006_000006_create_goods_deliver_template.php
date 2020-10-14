<?php

use yii\db\Migration;

class m000006_000006_create_goods_deliver_template extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_deliver_template}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'did' => $this->integer(), // 运费模板编号
        ]);
        $this->createIndex('fk_goods_deliver_template_goods1_idx', '{{%goods_deliver_template}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_deliver_template_goods1', '{{%goods_deliver_template}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_deliver_template_deliver_template1_idx', '{{%goods_deliver_template}}', ['did']);
        try {
            $this->addForeignKey('fk_goods_deliver_template_deliver_template1', '{{%goods_deliver_template}}', ['did'], '{{%deliver_template}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_deliver_template}}');
    }
}
