<?php

use yii\db\Migration;

class m000000_000006_create_ip_city extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%ip_city}}', [
            'id' => $this->primaryKey(), // PK
            'ip' => $this->string(32), // IP地址
            'area' => $this->string(32), // 区域编码
            'source' => $this->string(32), // 数据来源
            'create_time' => $this->integer(), // 创建时间
            'data' => $this->text(), // 原始数据
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%ip_city}}');
    }
}
