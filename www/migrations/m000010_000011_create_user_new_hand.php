<?php

use yii\db\Migration;

class m000010_000011_create_user_new_hand extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_new_hand}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'nid' => $this->integer(), // 新闻编号
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_new_hand_user1_idx', '{{%user_new_hand}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_new_hand_user1', '{{%user_new_hand}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_new_hand_hand1_idx', '{{%user_new_hand}}', ['nid']);
        try {
            $this->addForeignKey('fk_user_new_hand_hand1', '{{%user_new_hand}}', ['nid'], '{{%new_hand}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_new_hand}}');
    }
}
