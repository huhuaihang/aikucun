<?php

use yii\db\Migration;

class m000011_000003_create_user_notice extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_notice}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'nid' => $this->integer(), // 公告编号
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_notice_user1_idx', '{{%user_notice}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_notice_user1', '{{%user_notice}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_notice_notice1_idx', '{{%user_notice}}', ['nid']);
        try {
            $this->addForeignKey('fk_user_notice_notice1', '{{%user_notice}}', ['nid'], '{{%notice}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_notice}}');
    }
}
