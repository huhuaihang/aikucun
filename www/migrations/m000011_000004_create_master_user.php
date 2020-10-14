<?php

use yii\db\Migration;

class m000011_000004_create_master_user extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%master_user}}', [
            'id' => $this->primaryKey(), // PK
            'pid' => $this->integer(), // 上级编号 推荐关系
            'team_pid' => $this->integer(), // 团队上级编号
            'invite_code' => $this->string(8), // 邀请码
            'mobile' => $this->string(32), // 手机号
            'auth_key' => $this->string(32), //
            'password' => $this->string(256), // HASH密码
            'payment_password' => $this->string(256), // 支付密码
            'real_name' => $this->string(32), // 真实姓名
            'nickname' => $this->string(32), // 昵称
            'gender' => $this->integer(), // 性别
            'birthday' => $this->integer(), // 出生日期
            'avatar' => $this->string(128), // 头像
            'prepare_count' => $this->integer(), //预购数据量
            'level_id' => $this->integer(), // 等级编号
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_master_user_level1_idx', '{{%master_user}}', ['level_id']);
        try {
            $this->addForeignKey('fk_master_user_level1', '{{%master_user}}', ['level_id'], '{{%user_level}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('user_invite_code', '{{%master_user}}', ['invite_code'], true);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['gender', 1, '男'],
            ['gender', 2, '女'],
            ['gender', 0, '未知'],
            ['gender', 9, '保密'],
            ['master_user_status', 1, '正常'],
            ['master_user_status', 2, '待激活'],
            ['master_user_status', 9, '暂停'],
            ['master_user_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'master_user_status']);
        $this->delete('{{%key_map}}', ['t' => 'gender']);
        $this->dropTable('{{%master_user}}');
    }
}
