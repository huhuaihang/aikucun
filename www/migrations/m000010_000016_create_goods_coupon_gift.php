<?php

use yii\db\Migration;

class m000010_000016_create_goods_coupon_gift extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_coupon_gift}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 优惠券活动编号
            'gid' => $this->integer(), // 商品编号
            'name' => $this->string(128), // 附赠品名称
            'pic' => $this->string(256), // 附赠品图片
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_coupon_gift_status', 1, '正常'],
            ['goods_coupon_gift_status', 9, '隐藏'],
            ['goods_coupon_gift_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_coupon_gift_status']);
        $this->dropTable('{{%goods_coupon_gift}}');
    }
}
