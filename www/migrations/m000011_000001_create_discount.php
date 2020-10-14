<?php

use yii\db\Migration;

class m000011_000001_create_discount extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%discount}}', [
            'id' => $this->primaryKey(), // PK
            'name' => $this->string(32), // 名称
            'start_time' => $this->integer(), // 开始时间
            'end_time' => $this->integer(), // 结束时间
            'goods_flag_txt' => $this->string(32), // 商品标志文字
            'goods_flag_img' => $this->string(128), // 商品标志图标
            'buy_limit' => $this->integer(), // 每人限购数量
            'amount' => $this->integer(), // 总限购数量
            'status' => $this->integer(), // 状态
            'create_time' => $this->integer(), // 创建时间
            'remark' => $this->text(), // 备注
        ]);
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['discount_status', 1, '修改中'],
            ['discount_status', 2, '进行中'],
            ['discount_status', 9, '已结束'],
            ['discount_status', 0, '已删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'discount_status']);
        $this->dropTable('{{%discount}}');
    }
}
