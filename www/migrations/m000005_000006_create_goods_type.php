<?php

use yii\db\Migration;

class m000005_000006_create_goods_type extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_type}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 名称
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_type}}');
    }
}
