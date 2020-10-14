<?php

use yii\db\Migration;

class m000000_000002_create_key_map extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%key_map}}', [
            'id'=>$this->primaryKey(), // PK
            't'=>$this->string(256), // 名称
            'k'=>$this->integer(), // 键
            'v'=>$this->text(), // 值
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['yes_no', 1, '是'],
            ['yes_no', 0, '否'],
        ]);
    }

    public function down()
    {
        $this->delete('{{%key_map}}', ['t'=>'yes_no']);
        $this->dropTable('{{%key_map}}');
    }
}
