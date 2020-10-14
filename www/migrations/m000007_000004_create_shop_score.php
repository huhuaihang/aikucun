<?php

use yii\db\Migration;

class m000007_000004_create_shop_score extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%shop_score}}', [
            'id' => $this->primaryKey(), // PK
            'sid' => $this->integer(), // 店铺编号
            'uid' => $this->integer(), // 用户编号
            'oid' => $this->integer(), // 订单编号
            'score' => $this->integer(), // 评分值
            'create_time' => $this->integer(), // 创建时间
        ]);
        $this->createIndex('fk_shop_score_shop1_idx', '{{%shop_score}}', ['sid']);
        try {
            $this->addForeignKey('fk_shop_score_shop1', '{{%shop_score}}', ['sid'], '{{%shop}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_score_user1_idx', '{{%shop_score}}', ['uid']);
        try {
            $this->addForeignKey('fk_shop_score_user1', '{{%shop_score}}', ['uid'], '{{%user}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
        $this->createIndex('fk_shop_score_order1_idx', '{{%shop_score}}', ['oid']);
        try {
            $this->addForeignKey('fk_shop_score_order1', '{{%shop_score}}', ['oid'], '{{%order}}', ['id']);
        } catch (Exception $e) {
            Yii::error($e->getMessage());
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%shop_score}}');
    }
}
