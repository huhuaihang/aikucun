<?php

use yii\db\Migration;

class m000004_000015_create_shop_theme extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_theme}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 名称
            'code' => $this->string(32), // 查找目录代号
            'cover_image' => $this->string(128), // 封面图片
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%shop_theme}}');
    }
}
