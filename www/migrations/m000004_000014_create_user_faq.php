<?php

use yii\db\Migration;

class m000004_000014_create_user_faq extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_faq}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'fid' => $this->integer(), // 常见问题编号
            'result' => $this->integer(), // 结果
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_faq_user1_idx', '{{%user_faq}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_faq_user1', '{{%user_faq}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_faq_faq1_idx', '{{%user_faq}}', ['fid']);
        try {
            $this->addForeignKey('fk_user_faq_faq1', '{{%user_faq}}', ['fid'], '{{%faq}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_faq_result', 1, '已解决'],
            ['user_faq_result', 0, '未解决'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_faq_result']);
        $this->dropTable('{{%user_faq}}');
    }
}
