<?php

use yii\db\Migration;

class m000000_000009_create_api_client extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%api_client}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 名称
            'app_id' => $this->string(32), // AppId
            'app_secret' => $this->string(32), // AppSecret,
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['api_client_status', 1, '正常'],
            ['api_client_status', 9, '停止'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'api_client_status']);
        $this->dropTable('{{%api_client}}');
    }
}
