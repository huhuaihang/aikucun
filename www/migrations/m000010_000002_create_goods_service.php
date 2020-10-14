<?php

use yii\db\Migration;

class m000010_000002_create_goods_service extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_service}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 名称
            'desc' => $this->text(), // 描述
        ]);

    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_service}}');
    }
}
