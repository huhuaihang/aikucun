<?php

use yii\db\Migration;

class m000001_000005_create_violation_type extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%violation_type}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 名称
            'remark' => $this->text(), // 备注
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%violation_type}}');
    }
}
