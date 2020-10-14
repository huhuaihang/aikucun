<?php

use yii\db\Migration;

class m000005_000008_create_goods_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_category}}', [
            'id' => $this->primaryKey(), // PK
            'pid' => $this->integer(), // 上级编号
            'name' => $this->string(32), // 名称
            'url' => $this->string(128), // 链接地址
            'image' => $this->string(128), // 图片
            'sort' => $this->integer(), // 排序
            'status' => $this->integer(), // 状态
            'is_choicest' => $this->integer(), // 是否精选
        ]);
        $this->createIndex('fk_goods_category_goods_category1_idx', '{{%goods_category}}', ['pid']);
        try {
            $this->addForeignKey('fk_goods_category_goods_category1', '{{%goods_category}}', ['pid'], '{{%goods_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_category_status', 1, '显示'],
            ['goods_category_status', 9, '隐藏'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_category_status']);
        $this->dropTable('{{%goods_category}}');
    }
}
