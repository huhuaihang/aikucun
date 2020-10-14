<?php

use yii\db\Migration;

class m000009_000011_create_user_sale_log extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_sale_log}}', [
            'id' => $this->primaryKey(), // PK
            'uid' => $this->integer(), // 用户编号
            'to_uid' => $this->integer(), // 卖给用户编号
            'oid' => $this->integer(), // 订单编号
            'gid' => $this->integer(), // 礼包产品编号
            'remark' => $this->string(256), // 备注
            'create_time' => $this->integer(), // 时间
        ]);
        $this->createIndex('fk_user_uid_user1_idx', '{{%user_sale_log}}', ['uid']);
        try {
            $this->addForeignKey('fk_user_uid_user2', '{{%user_sale_log}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_to_uid_user1_idx', '{{%user_sale_log}}', ['to_uid']);
        try {
            $this->addForeignKey('fk_user_to_uid_user2', '{{%user_sale_log}}', ['to_uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_oid_order1_idx', '{{%user_sale_log}}', ['oid']);
        try {
            $this->addForeignKey('fk_user_oid_order1', '{{%user_sale_log}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_user_gid_goods1_idx', '{{%user_sale_log}}', ['gid']);
        try {
            $this->addForeignKey('fk_user_gid_goods1', '{{%user_sale_log}}', ['gid'], '{{%goods}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_sale_log}}');
    }
}
