<?php

use yii\db\Migration;

class m000004_000011_create_user_address extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_address}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'area' => $this->string(32), // 区域编码
            'address' => $this->string(128), // 详细地址
            'name' => $this->string(32), // 收货人
            'mobile' => $this->string(32), // 电话
            'is_default' => $this->integer(), // 是否默认
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_user_address_user1_idx', '{{%user_address}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_address_user1', '{{%user_address}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_address_status', 1, '正常'],
            ['user_address_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'aid');
        $this->delete('{{%key_map}}', ['t' => 'user_address_status']);
        $this->dropTable('{{%user_address}}');
    }
}
