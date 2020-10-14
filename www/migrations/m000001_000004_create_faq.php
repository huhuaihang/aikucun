<?php

use yii\db\Migration;

class m000001_000004_create_faq extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%faq}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 分类编号
            'title' => $this->string(128), // 标题
            'tags' => $this->string(128), // 标签
            'content' => $this->text(), // 内容
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_faq_faq_category1_idx', '{{%faq}}', ['cid']);
        try {
            $this->addForeignKey('fk_faq_faq_category1', '{{%faq}}', ['cid'], '{{%faq_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('faq_tags_idx', '{{%faq}}', ['tags']);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['faq_status', 1, '显示'],
            ['faq_status', 9, '隐藏'],
            ['faq_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'faq_status']);
        $this->dropTable('{{%faq}}');
    }
}
