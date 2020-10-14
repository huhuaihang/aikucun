<?php

use yii\db\Migration;

class m000000_000005_create_express extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%express}}', [
            'id' => $this->primaryKey(), // PK
            'code' => $this->string(32), // 代码
            'name' => $this->string(32), // 名称
            'sort' => $this->integer(), // 排序
            'status' => $this->integer(), // 状态
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['express_status', 1, '正常'],
            ['express_status', 9, '暂停'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'express_status']);
        $this->dropTable('{{%express}}');
    }
}
