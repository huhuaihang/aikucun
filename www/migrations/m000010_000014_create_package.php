<?php

use yii\db\Migration;

class m000010_000014_create_package extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%package}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(128), // 套餐名称
            'count' => $this->integer(), // 套餐含礼包数量
            'price' => $this->decimal(12, 2), // 原价
            'package_price' => $this->decimal(12, 2), // 活动价格
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['package_status', 1, '正常'],
            ['package_status', 9, '隐藏'],
            ['package_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'package_status']);
        $this->dropTable('{{%package}}');
    }
}
