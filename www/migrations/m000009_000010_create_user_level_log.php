<?php

use yii\db\Migration;

class m000009_000010_create_user_level_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_level_log}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'level_id' => $this->integer(), // 用户升级后登记
            'remark' => $this->string(256), // 备注
            'create_time' => $this->integer(), // 时间
        ]);
        $this->createIndex('fk_user_uid_user1_idx', '{{%user_level_log}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_uid_user1', '{{%user_level_log}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_level_log}}');
    }
}
