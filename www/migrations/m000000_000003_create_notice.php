<?php

use yii\db\Migration;

class m000000_000003_create_notice extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%notice}}', [
            'id' => $this->primaryKey(), // PK
            'title' => $this->string(128), // 标题
            'main_pic' => $this->string(128), // 主图
            'content' => $this->text(), // 内容
            'status' => $this->integer(), // 状态
            'time' => $this->integer(), // 时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['notice_status', 1, '显示'],
            ['notice_status', 9, '隐藏'],
            ['notice_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'notice_status']);
        $this->dropTable('{{%notice}}');
    }
}
