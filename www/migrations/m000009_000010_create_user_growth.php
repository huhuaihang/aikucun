<?php

use yii\db\Migration;

class m000009_000010_create_user_growth extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_growth}}', [
            'id' => $this->primaryKey(), // PK
            'to_uid' => $this->integer(), // 该拿成长值用户编号
            'from_uid' => $this->integer(), // 购买礼包用户编号编号
            'money' => $this->decimal(12,2), // 成长值
            'type' => $this->integer(), // 成长值等级
            'create_time' => $this->integer(), // 时间
        ]);
        $this->createIndex('fk_user_from_uid_user1_idx', '{{%user_growth}}', ['from_uid']);
        try {
            $this->addForeignKey('fk_user_from_uid_user1', '{{%user_growth}}', ['from_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_uid_user1_idx', '{{%user_growth}}', ['to_uid']);
        try {
            $this->addForeignKey('fk_user_to_uid_user1', '{{%user_growth}}', ['to_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_growth_type', 1, '直接邀请'],
            ['user_growth_type', 2, '直属会员新增邀请'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_growth_type']);
        $this->dropTable('{{%user_growth}}');
    }
}
