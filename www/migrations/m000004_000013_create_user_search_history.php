<?php

use yii\db\Migration;

class m000004_000013_create_user_search_history extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_search_history}}', [
            'id' => $this->primaryKey(), // PK/
            'uid' => $this->integer(), // 用户编号
            'keyword' => $this->string(128), // 关键字
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_search_history_user1_idx', '{{%user_search_history}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_search_history_user1', '{{%user_search_history}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_search_history}}');
    }
}
