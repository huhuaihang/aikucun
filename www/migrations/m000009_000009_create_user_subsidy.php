<?php

use yii\db\Migration;

class m000009_000009_create_user_subsidy extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_subsidy}}', [
            'id' => $this->primaryKey(), // PK
            'to_uid' => $this->integer(), // 该拿补贴用户编号
            'to_1_uid' => $this->integer(), // 第一级上级用户编号
            'to_2_uid' => $this->integer(), // 第二级上级用户编号
            'to_3_uid' => $this->integer(), // 第三级上级用户编号
            'from_uid' => $this->integer(), // 购买礼包用户编号编号
            'money' => $this->decimal(12,2), // 补贴
            'to_user_level' => $this->string(128), // 该拿补贴的人当时的用户等级
            'type' => $this->integer(), // 补贴类型
            'no' => $this->string(128), // 订单编号
            'create_time' => $this->integer(), // 时间
        ]);
        $this->createIndex('fk_user_from_uid_user1_idx', '{{%user_subsidy}}', ['from_uid']);
        try {
            $this->addForeignKey('fk_user_from_uid_user1', '{{%user_subsidy}}', ['from_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_uid_user1_idx', '{{%user_subsidy}}', ['to_uid']);
        try {
            $this->addForeignKey('fk_user_to_uid_user1', '{{%user_subsidy}}', ['to_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_1_uid_user1_idx', '{{%user_subsidy}}', ['to_1_uid']);
        try {
            $this->addForeignKey('fk_user_to_1_uid_user1', '{{%user_subsidy}}', ['to_1_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_2_uid_user1_idx', '{{%user_subsidy}}', ['to_2_uid']);
        try {
            $this->addForeignKey('fk_user_to_2_uid_user1', '{{%user_subsidy}}', ['to_2_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_3_uid_user1_idx', '{{%user_subsidy}}', ['to_3_uid']);
        try {
            $this->addForeignKey('fk_user_to_3_uid_user1', '{{%user_subsidy}}', ['to_3_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_subsidy_type', 1, '直接邀请'],
            ['user_subsidy_type', 2, '直属会员新增邀请'],
            ['user_subsidy_type', 3, '直属会员邀请会员再邀请会员'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_subsidy_type']);
        $this->dropTable('{{%user_subsidy}}');
    }
}
