<?php

use yii\db\Migration;

class m000010_000020_create_user_score_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_score_log}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), //用户编号
            'code' => $this->char(10),//事件类型
            'score' => $this->integer(), // 触发事件奖励积分
            'from_uid' => $this->integer(), // 来源用户编号
            'create_time' => $this->integer(), // 时间
            'remark' => $this->string(256), // 积分来源说明

        ]);
        $this->createIndex('fk_user_score_log_user1_idx', '{{%user_score_log}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_score_log_user1', '{{%user_score_log}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

    }

    public function safeDown()
    {
        $this->dropTable('{{%user_score_log}}');
    }
}
