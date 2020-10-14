<?php

use yii\db\Migration;

class m000008_000008_create_user_level extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_level}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 等级名称
            'money' => $this->decimal(12, 2), // 等级金额会员费
            'description' => $this->string(128), //权益说明
            'commission_ratio_1' => $this->string(32), // 一级佣金比率
            'commission_ratio_2' => $this->string(32), // 二级佣金比率
            'commission_ratio_3' => $this->string(32), // 三级佣金比率
            'money_1' => $this->string(32), // 一级佣金比率
            'money_2' => $this->string(32), // 二级佣金比率
            'money_3' => $this->string(32), // 三级佣金比率
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_level_status', 1, '正常'],
            ['user_level_status', 9, '弃用'],
            ['user_level_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_level_status']);
        $this->dropTable('{{%user_level}}');
    }
}
