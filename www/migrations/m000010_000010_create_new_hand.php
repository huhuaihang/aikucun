<?php

use yii\db\Migration;

class m000010_000010_create_new_hand extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%new_hand}}', [
            'id' => $this->primaryKey(), // PK
            'title' => $this->string(32), // 标题
            'content' => $this->text(), // 内容
            'main_pic' => $this->string(256), // 缩略图
            'read_count' => $this->integer(11), // 阅读人数
            'share_count' => $this->integer(11), // 分享次数
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['new_hand_status', 1, '正常'],
            ['new_hand_status', 9, '隐藏'],
            ['new_hand_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'new_hand_status']);
        $this->dropTable('{{%new_hand}}');
    }
}
