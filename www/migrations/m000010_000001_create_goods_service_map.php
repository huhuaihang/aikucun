<?php

use yii\db\Migration;

class m000010_000001_create_goods_service_map extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_service_map}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'sid' => $this->integer(), // 商品服务编号
        ]);
        $this->createIndex('fk_goods_service_map_goods1_idx', '{{%goods_service_map}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_service_map_goods1', '{{%goods_service_map}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_service_map_goods_service1_idx', '{{%goods_service_map}}', ['sid']);
        try {
            $this->addForeignKey('fk_goods_service_map_goods_service1', '{{%goods_service_map}}', ['sid'], '{{%goods_service}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_service_map}}');
    }
}
