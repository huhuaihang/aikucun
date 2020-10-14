<?php

use yii\db\Migration;

class m000001_000003_create_faq_category extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%faq_category}}', [
            'id' => $this->primaryKey(), // PK
            'pid' => $this->integer(), // 上级编号
            'name' => $this->string(128), // 名称
            'status' => $this->integer(), // 状态
        ]);
        $this->createIndex('fk_faq_category_faq_category1_idx', '{{%faq_category}}', ['pid']);
        try {
            $this->addForeignKey('fk_faq_category_faq_category1', '{{%faq_category}}', ['pid'], '{{%faq_category}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['faq_category_status', 1, '显示'],
            ['faq_category_status', 9, '隐藏'],
            ['faq_category_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'faq_category_status']);
        $this->dropTable('{{%faq_category}}');
    }
}
