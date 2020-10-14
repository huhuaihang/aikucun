<?php

use yii\db\Migration;

class m000009_000007_create_user_card_level extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_card_level}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 等级名称
            'remark' => $this->string(128), // 备注
            'status' => $this->integer(), // 会员卡等级
            'create_time' => $this->integer(), // 绑定时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_card_level_status', 1, '正常'],
            ['user_card_level_status', 9, '弃用'],
            ['user_card_level_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_card_level_status']);
        $this->dropTable('{{%user_card_level}}');
    }
}
