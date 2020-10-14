<?php

use yii\db\Migration;

class m000000_000008_create_system_version extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{system_version}}', [
            'id' => $this->primaryKey(), // PK
            'api_version' => $this->string(16), // 接口版本号
            'ios_version' => $this->string(16), // IOS版本号
            'android_version' => $this->string(16), // Android版本号
            'aes_key' => $this->string(128), // AES加密key
            'aes_iv' => $this->string(128), // AES加密iv
            'android_download_source' => $this->integer(), // 安卓下载来源
            'android_download_url' => $this->string(512), // 安卓下载地址
            'update_info' => $this->text(), // 更新信息
            'is_support' => $this->integer(), // 是否支持
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['android_download_source', 1, '蒲公英'],
            ['android_download_source', 2, '应用宝'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'android_download_source']);
        $this->dropTable('{{system_version}}');
    }
}
