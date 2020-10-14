<?php

use yii\db\Migration;

class m000000_000007_create_withdraw_bank extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%withdraw_bank}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 银行名称
            'code' => $this->string(32), // 代号
            'logo' => $this->string(128), // LOGO
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%withdraw_bank}}');
    }
}
