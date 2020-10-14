<?php

use yii\db\Migration;

class m000010_000017_create_goods_coupon_gift_user extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%goods_coupon_gift_user}}', [
            'id' => $this->primaryKey(), // PK
            'cid' => $this->integer(), // 优惠券活动编号
            'gid' => $this->integer(), // 商品编号
            'uid' => $this->integer(), // 用户编号
            'create_time' => $this->integer(), // 获取时间
            'use_time' => $this->integer(), // 使用时间
            'status' => $this->integer(), // 状态
            'remark' => $this->text(), // 备注
        ]);

        $this->createIndex('fk_goods_coupon_gift_user_goods_coupon_rule1_idx', '{{%goods_coupon_gift_user}}', ['cid']);
        try {
            $this->addForeignKey('fk_goods_coupon_gift_user_goods_coupon_rule1', '{{%goods_coupon_gift_user}}', ['cid'], '{{%goods_coupon_rule}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_coupon_gift_user_goods1_idx', '{{%goods_coupon_gift_user}}', ['gid']);
        try {
            $this->addForeignKey('fk_goods_coupon_gift_user_goods1', '{{%goods_coupon_gift_user}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_goods_coupon_gift_user_user1_idx', '{{%goods_coupon_gift_user}}', ['uid']);
        try {
            $this->addForeignKey('fk_goods_coupon_gift_user_user1', '{{%goods_coupon_gift_user}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['goods_coupon_gift_user_status', 1, '待使用'],
            ['goods_coupon_gift_user_status', 2, '已锁定'],
            ['goods_coupon_gift_user_status', 3, '已使用'],
            ['goods_coupon_gift_user_status', 0, '已删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'goods_coupon_gift_user_status']);
        $this->dropTable('{{%goods_coupon_gift_user}}');
    }
}
