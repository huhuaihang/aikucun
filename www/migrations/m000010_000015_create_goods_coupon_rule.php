<?php

use yii\db\Migration;

class m000010_000015_create_goods_coupon_rule extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_coupon_rule}}', [
            'id' => $this->primaryKey(), // PK
            'gid' => $this->integer(), // 商品编号
            'name' => $this->string(128), // 优惠券活动名称
            'count' => $this->integer(), // 优惠券数量
            'price' => $this->decimal(12, 2), // 优惠券价格
            'create_time' => $this->integer(), // 创建时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);
        $this->createIndex('fk_goods_coupon_rule_goods1_idx', '{{%goods_coupon_rule}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_coupon_rule_goods1', '{{%goods_coupon_rule}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_coupon_rule_status', 1, '正常'],
            ['goods_coupon_rule_status', 9, '隐藏'],
            ['goods_coupon_rule_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_coupon_rule_status']);
        $this->dropTable('{{%goods_coupon_rule}}');
    }
}
