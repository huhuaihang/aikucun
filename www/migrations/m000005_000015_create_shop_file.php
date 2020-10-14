<?php

use yii\db\Migration;

class m000005_000015_create_shop_file extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_file}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'cid' => $this->integer(), // 店铺文件分类编号
            'type' => $this->integer(), // 类型
            'url' => $this->string(128), // 路径
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_shop_file_shop1_idx', '{{%shop_file}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_file_shop1', '{{%shop_file}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_file_category1_idx', '{{%shop_file}}', ['cid']);
        try {
            $this->addForeignKey('fk_shop_file_category1', '{{%shop_file}}', ['cid'], '{{%shop_file_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_file_type', 1, '图片'],
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['shop_file_status', 1, '正常'],
            ['shop_file_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'shop_file_status']);
        $this->delete('{{%key_map}}', ['t' => 'shop_file_type']);
        $this->dropTable('{{%shop_file}}');
    }
}
