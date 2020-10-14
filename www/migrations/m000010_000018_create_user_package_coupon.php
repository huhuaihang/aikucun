<?php

use yii\db\Migration;

class m000010_000018_create_user_package_coupon extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_package_coupon}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'oid' => $this->integer(), // 订单记录编号
            'create_time' => $this->integer(), // 创建时间
            'over_time' => $this->integer(), // 过期时间
            'status' => $this->integer(), // 状态
        ]);

        $this->createIndex('fk_user_package_coupon_user1_idx', '{{%user_package_coupon}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_package_coupon_user1', '{{%user_package_coupon}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

        $this->createIndex('fk_user_package_coupon_order1_idx', '{{%user_package_coupon}}', ['oid']);
        try {
            $this->addForeignKey('fk_user_package_coupon_order1', '{{%user_package_coupon}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }

        $this->batchInsert('{{%key_map}}', ['t', 'k', 'v'], [
            ['user_package_coupon_status', 1, '正常'],
            ['user_package_coupon_status', 2, '已使用'],
            ['user_package_coupon_status', 9, '过期'],
            ['user_package_coupon_status', 0, '删除'],
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%key_map}}', ['t' => 'user_package_coupon_status']);
        $this->dropTable('{{%user_package_coupon}}');
    }
}
