<?php

use yii\db\Migration;

class m000000_000010_create_system_error extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%system_error}}', [
            'id' => $this->primaryKey(), // PK
            'time' => $this->integer(), // 时间
            'message' => $this->text(), // 内容
            'code' => $this->string(128), // 代码
            'file' => $this->string(128), // 文件
            'line' => $this->integer(), // 行号
            'trace' => $this->text(), // 追踪信息
            'context' => $this->text(), // 环境信息
            'status' => $this->integer(), // 状态
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['system_error_status', 1, '待处理'],
            ['system_error_status', 9, '已处理'],
            ['system_error_status', 0, '已删除'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%key_map}}', ['t' => 'system_error_status']);
        $this->dropTable('{{%system_error}}');
    }
}
