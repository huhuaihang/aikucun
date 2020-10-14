<?php

use yii\db\Migration;

class m000009_000008_create_user_card extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_card}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'card_no' => $this->string(32), // 卡号
            'c_lid' => $this->integer(), // 会员卡等级编号
            'bind_time' => $this->integer(), // 绑定时间
            'unset_bind_time' => $this->integer(), // 解绑时间
            'status' => $this->integer(), // 绑定时间
            'create_time' => $this->integer(), // 初次绑定时间
        ]);
        $this->createIndex('fk_user_card_user1_idx', '{{%user_card}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_card_user1', '{{%user_card}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_card_user_card_level1_idx', '{{%user_card}}', ['c_lid']);
        try {
            $this->addForeignKey('fk_user_card_user_card_level1', '{{%user_card}}', ['c_lid'], '{{%user_card_level}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_card}}');
    }
}
