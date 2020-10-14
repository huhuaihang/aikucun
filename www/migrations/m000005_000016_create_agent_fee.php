<?php

use yii\db\Migration;

class m000005_000016_create_agent_fee extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%agent_fee}}', [
            'id' => $this->primaryKey(), // PK
            'area' => $this->string(32), // 区域编码
            'initial_fee' => $this->decimal(12, 2), // 加盟费
            'earnest_money' => $this->decimal(12, 2), // 保证金
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%agent_fee}}');
    }
}
