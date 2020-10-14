<?php

use yii\db\Migration;

class m000005_000004_create_goods_brand extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_brand}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 名称
            'owner' => $this->string(128), // 持有人
            'logo' => $this->string(128), // LOGO
            'tm_r' => $this->string(4), // TM或R
            'sort' => $this->integer(), // 排序数字
            'create_time' => $this->integer(), // 创建时间
            'valid_time' => $this->string(32), // 有效期至
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%goods_brand}}');
    }
}
