<?php

use yii\db\Migration;

class m000010_000019_create_goods_barrage_rules extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_barrage_rules}}', [
            'id' => $this->primaryKey(), // PK
            'title' => $this->string(32), // 订单记录编号
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_barrage_rules_status', 1, '正常'],
            ['goods_barrage_rules_status', 9, '隐藏'],
            ['goods_barrage_rules_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_barrage_rules_status']);
        $this->dropTable('{{%goods_barrage_rules}}');
    }
}
